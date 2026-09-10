<?php

namespace Database\Seeders;

use App\Enums\PengeluaranJenis;
use App\Models\Expense;
use App\Models\NotaDinas;
use App\Models\NotaDinasDetail;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductVendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class NotaDinasDetailSeeder extends Seeder
{
    public function run(): void
    {
        $notaDinas = NotaDinas::query()->orderBy('id')->get();
        if ($notaDinas->isEmpty()) {
            $this->command->warn('Nota dinas belum ada. Menjalankan NotaDinasSeeder...');
            $this->call(NotaDinasSeeder::class);
            $notaDinas = NotaDinas::query()->orderBy('id')->get();
        }

        $orders = Order::query()
            ->with(['items.product'])
            ->orderBy('id')
            ->get();

        if ($notaDinas->isEmpty() || $orders->isEmpty()) {
            $this->command->error('Nota dinas atau order belum ada. Jalankan OrderSeeder dan NotaDinasSeeder terlebih dahulu.');

            return;
        }

        $paymentMethod = PaymentMethod::query()->where('is_cash', false)->orderBy('id')->first()
            ?? PaymentMethod::query()->orderBy('id')->first();
        $invoiceImage = $this->seedImage();
        $keepIds = [];
        $created = 0;

        foreach ($orders->values() as $index => $order) {
            $nd = $notaDinas->get($index) ?? $notaDinas->first();
            $orderProduct = $order->items->first();
            if (! $orderProduct) {
                $this->command->warn("Order '{$order->name}' belum punya produk, dilewati.");

                continue;
            }

            $productVendors = ProductVendor::query()
                ->with('vendor')
                ->where('product_id', $orderProduct->product_id)
                ->whereNull('simulasi_produk_id')
                ->whereHas('vendor')
                ->orderBy('id')
                ->take(2)
                ->get();

            if ($productVendors->isEmpty()) {
                $this->command->warn("Produk order '{$order->name}' belum punya vendor, dilewati.");

                continue;
            }

            $stages = ['DP', 'Payment 1'];

            foreach ($productVendors->values() as $itemIndex => $productVendor) {
                $vendor = $productVendor->vendor;
                $invoiceNumber = 'INV-W-'.str_pad((string) (($index * 10) + $itemIndex + 1), 3, '0', STR_PAD_LEFT);
                $amount = max(500000, (int) ($productVendor->harga_vendor ?: $vendor->harga_vendor ?: 1000000));
                $accountHolder = $vendor->account_holder ?: $vendor->pic_name ?: $vendor->name;
                $bankName = $vendor->bank_name ?: 'BCA';
                $bankAccount = $vendor->bank_account ?: '1234567890';
                $keperluan = 'Pembayaran ke '.$vendor->name.' untuk '.$order->name;

                $existing = NotaDinasDetail::withTrashed()
                    ->where('invoice_number', $invoiceNumber)
                    ->first();

                if ($existing?->trashed()) {
                    $existing->restore();
                }

                $payload = [
                    'nota_dinas_id' => $nd->id,
                    'vendor_id' => $vendor->id,
                    'keperluan' => $keperluan,
                    'event' => $order->name,
                    'jumlah_transfer' => $amount,
                    'invoice_number' => $invoiceNumber,
                    'invoice_file' => $invoiceImage,
                    'bank_name' => $bankName,
                    'bank_account' => $bankAccount,
                    'account_holder' => $accountHolder,
                    'status_invoice' => $itemIndex === 0 ? 'sudah_dibayar' : 'menunggu',
                    'jenis_pengeluaran' => PengeluaranJenis::WEDDING->value,
                    'payment_stage' => $stages[$itemIndex] ?? 'Additional',
                    'order_id' => $order->id,
                    'order_product_id' => $orderProduct->id,
                    'product_vendor_id' => $productVendor->id,
                ];

                if ($existing) {
                    $existing->forceFill($payload)->save();
                    $detail = $existing;
                } else {
                    $detail = NotaDinasDetail::query()->create($payload);
                }

                $this->syncExpense($order, $nd, $detail, $paymentMethod?->id, $invoiceImage);
                $keepIds[] = $detail->id;
                $created++;
            }
        }

        if ($keepIds !== []) {
            $extraIds = NotaDinasDetail::query()->whereNotIn('id', $keepIds)->pluck('id');
            if ($extraIds->isNotEmpty()) {
                Expense::query()->whereIn('nota_dinas_detail_id', $extraIds)->delete();
                $removed = NotaDinasDetail::query()->whereIn('id', $extraIds)->delete();
                $this->command->warn("Detail nota dinas di luar data seed di-soft-delete: {$removed}.");
            }
        }

        $orderIds = $orders->pluck('id');
        $orphans = Expense::query()
            ->whereIn('order_id', $orderIds)
            ->whereNull('nota_dinas_id')
            ->delete();
        if ($orphans > 0) {
            $this->command->warn("Pengeluaran tanpa nota dinas di-soft-delete: {$orphans}.");
        }

        $this->command->info("✅ NotaDinasDetailSeeder: {$created} detail wedding terhubung ke order.");
    }

    private function syncExpense(
        Order $order,
        NotaDinas $notaDinas,
        NotaDinasDetail $detail,
        ?int $paymentMethodId,
        ?string $invoiceImage,
    ): void {
        if (! $paymentMethodId) {
            return;
        }

        $existing = Expense::withTrashed()
            ->where('nota_dinas_detail_id', $detail->id)
            ->first();

        if (! $existing) {
            $existing = Expense::withTrashed()
                ->where('order_id', $order->id)
                ->where('vendor_id', $detail->vendor_id)
                ->whereNull('nota_dinas_detail_id')
                ->first();
        }

        if ($existing?->trashed()) {
            $existing->restore();
        }

        $payload = [
            'order_id' => $order->id,
            'vendor_id' => $detail->vendor_id,
            'payment_method_id' => $paymentMethodId,
            'nota_dinas_id' => $notaDinas->id,
            'nota_dinas_detail_id' => $detail->id,
            'note' => $detail->keperluan,
            'date_expense' => Carbon::parse($notaDinas->tanggal)->addDays(3)->toDateString(),
            'amount' => (int) $detail->jumlah_transfer,
            'no_nd' => $notaDinas->no_nd,
            'image' => $invoiceImage ?: $detail->invoice_file,
            'kategori_transaksi' => 'uang_keluar',
            'payment_stage' => $detail->payment_stage,
            'account_holder' => $detail->account_holder,
            'bank_name' => $detail->bank_name,
            'bank_account' => $detail->bank_account,
        ];

        if ($existing) {
            $existing->forceFill($payload)->save();

            return;
        }

        Expense::query()->create($payload);
    }

    private function seedImage(): ?string
    {
        $sources = [
            public_path('images/logomki.png'),
            public_path('images/logo.png'),
            public_path('logo.png'),
        ];

        $source = collect($sources)->first(fn (string $file) => File::exists($file));
        if (! $source) {
            return null;
        }

        $path = 'expense_wedding/'.date('Y/m').'/bukti-nd.'.(pathinfo($source, PATHINFO_EXTENSION) ?: 'png');
        Storage::disk('public')->put($path, File::get($source));

        return $path;
    }
}
