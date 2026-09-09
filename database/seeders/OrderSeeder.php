<?php

namespace Database\Seeders;

use App\Enums\MonthEnum;
use App\Enums\OrderStatus;
use App\Models\DataPembayaran;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\User;
use App\Support\DocumentNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $prospects = Prospect::query()->get()->keyBy('name_event');
        $neededProspects = collect($this->orders())->pluck('prospect_name');
        $missingProspects = $neededProspects->reject(fn (string $name) => $prospects->has($name));

        if ($missingProspects->isNotEmpty()) {
            $this->command->warn('Prospek belum lengkap. Menjalankan ProspectSeeder...');
            $this->call(ProspectSeeder::class);
            $prospects = Prospect::query()->get()->keyBy('name_event');
        }

        $products = Product::query()->get()->keyBy('slug');
        $neededProducts = collect($this->orders())->pluck('product_slug');
        $missingProducts = $neededProducts->reject(fn (string $slug) => $products->has($slug));

        if ($missingProducts->isNotEmpty()) {
            $this->command->warn('Produk belum lengkap. Menjalankan ProductSeeder...');
            $this->call(ProductSeeder::class);
            $products = Product::query()->get()->keyBy('slug');
        }

        $accountManagers = User::role('Account Manager')->orderBy('id')->get();
        if ($accountManagers->isEmpty()) {
            $accountManagers = User::query()->orderBy('id')->get();
        }

        $eventManagers = Employee::query()->where('position', 'Event Manager')->orderBy('id')->get();
        if ($eventManagers->isEmpty()) {
            $eventManagers = Employee::query()->orderBy('id')->get();
        }

        $paymentMethod = PaymentMethod::query()->where('is_cash', false)->orderBy('id')->first()
            ?? PaymentMethod::query()->orderBy('id')->first();

        if ($prospects->isEmpty() || $products->isEmpty() || $accountManagers->isEmpty() || $eventManagers->isEmpty() || ! $paymentMethod) {
            $this->command->error('Data master belum lengkap. Jalankan UserSeeder, EmployeeSeeder, ProductSeeder, ProspectSeeder, dan PaymentMethodSeeder.');

            return;
        }

        $orderPrefix = DocumentNumber::orderPrefix();
        $keepIds = [];
        $created = 0;

        foreach ($this->orders() as $index => $data) {
            $prospect = $prospects->get($data['prospect_name']);
            $product = $products->get($data['product_slug']);

            if (! $prospect || ! $product) {
                $this->command->warn("Prospek/produk untuk '{$data['prospect_name']}' tidak ditemukan, dilewati.");

                continue;
            }

            $accountManager = $accountManagers->firstWhere('email', $data['am_email'])
                ?? $accountManagers->firstWhere('id', $prospect->user_id)
                ?? $accountManagers->values()->get($index % $accountManagers->count());

            $eventManager = $eventManagers->values()->get($index % $eventManagers->count());
            $pricing = $this->pricingFromProduct($product);
            $payments = $this->paymentsForOrder($pricing['grand_total'], $data);
            $paidAmount = collect($payments)->sum('nominal');
            $closingDate = $payments[0]['tgl_bayar'] ?? Carbon::now()->toDateString();
            $isPaid = $paidAmount >= $pricing['grand_total'];

            $existing = Order::withTrashed()->where('prospect_id', $prospect->id)->first();
            if ($existing?->trashed()) {
                $existing->restore();
            }

            $number = $orderPrefix.'-'.$data['sequence'];
            $slug = $this->uniqueSlug((string) $prospect->name_event, $existing?->id);
            $docKontrak = $this->seedPdf(
                'doc_kontrak/'.$slug.'-kontrak.pdf',
                'Kontrak '.$prospect->name_event,
                $data['no_kontrak'],
            );
            $agreement = $this->seedPdf(
                'agreement_product/'.$slug.'-persetujuan.pdf',
                'Persetujuan Produk '.$prospect->name_event,
                $data['no_kontrak'],
            );
            $proofImage = $this->seedImage('payment-proofs/'.date('Y/m').'/'.$slug.'-bukti.png');
            $invoiceImage = $this->seedImage('expenses/'.$slug.'-invoice.png');

            $payload = [
                'prospect_id' => $prospect->id,
                'slug' => $slug,
                'name' => $prospect->name_event,
                'number' => $number,
                'user_id' => $accountManager->id,
                'employee_id' => $eventManager->id,
                'last_edited_by' => $accountManager->id,
                'no_kontrak' => $data['no_kontrak'],
                'doc_kontrak' => $docKontrak,
                'agreement_product' => $agreement,
                'pax' => (int) ($product->pax ?: 100),
                'note' => $data['note'],
                'total_price' => $pricing['total_price'],
                'paid_amount' => $paidAmount,
                'promo' => 0,
                'penambahan' => $pricing['penambahan'],
                'pengurangan' => $pricing['pengurangan'],
                'grand_total' => $pricing['grand_total'],
                'change_amount' => max(0, $pricing['grand_total'] - $paidAmount),
                'is_paid' => $isPaid,
                'closing_date' => $closingDate,
                'status' => $data['status']->value,
                'kategori_transaksi' => 'uang_masuk',
            ];

            if ($existing) {
                $existing->forceFill($payload)->save();
                $order = $existing;
            } else {
                $order = Order::query()->create($payload);
            }

            $this->syncItems($order, $product, $pricing['total_price']);
            $this->syncPayments($order, $payments, $paymentMethod->id, $proofImage);
            $this->syncExpenses($order, $product, $paymentMethod->id, $invoiceImage, $closingDate);

            $keepIds[] = $order->id;
            $created++;
        }

        if ($keepIds !== []) {
            $removed = 0;
            Order::query()
                ->where(function ($query) use ($orderPrefix): void {
                    $query->where('number', 'like', 'MW-%')
                        ->orWhere('number', 'like', $orderPrefix.'-%');
                })
                ->whereNotIn('id', $keepIds)
                ->each(function (Order $order) use (&$removed): void {
                    $order->delete();
                    $removed++;
                });

            if ($removed > 0) {
                $this->command->warn("Order seeder di luar 5 data di-soft-delete: {$removed}.");
            }
        }

        $this->command->info("✅ OrderSeeder: {$created} order dibuat/diperbarui sesuai form.");
    }

    /**
     * Lima order — field mengikuti OrderForm (prospek, AM, EM, kontrak, produk, DP/termin, pengeluaran).
     *
     * @return list<array<string, mixed>>
     */
    private function orders(): array
    {
        $year = (int) date('Y');

        return [
            [
                'prospect_name' => 'Wedding Andi & Sari',
                'product_slug' => 'nisa-rama-ballroom-grand-500',
                'am_email' => 'rama.dhona@wofins.com',
                'sequence' => '100001',
                'no_kontrak' => 'KONTR/001/IX/'.$year,
                'status' => OrderStatus::Processing,
                'dp_percent' => 30,
                'paid_count' => 2,
                'terms' => [
                    ['persen' => 40, 'bulan' => MonthEnum::Oktober->value, 'tahun' => $year],
                    ['persen' => 60, 'bulan' => MonthEnum::Januari->value, 'tahun' => $year + 1],
                ],
                'note' => '<p>Order dari simulasi Ballroom Grand 500 pax untuk Wedding Andi &amp; Sari.</p><p><strong>Status:</strong> Processing — DP dan termin pertama sudah masuk.</p><ul><li>Venue hotel 5 bintang, palet gold &amp; putih.</li><li>Kontrak dan persetujuan produk sudah ditandatangani.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Budi & Maya',
                'product_slug' => 'andi-sinta-garden-puncak-200',
                'am_email' => 'rina.mardiana@wofins.com',
                'sequence' => '100002',
                'no_kontrak' => 'KONTR/002/IX/'.$year,
                'status' => OrderStatus::Processing,
                'dp_percent' => 25,
                'paid_count' => 2,
                'terms' => [
                    ['persen' => 50, 'bulan' => MonthEnum::November->value, 'tahun' => $year],
                    ['persen' => 50, 'bulan' => MonthEnum::Februari->value, 'tahun' => $year + 1],
                ],
                'note' => '<p>Order garden outdoor 200 pax untuk Wedding Budi &amp; Maya.</p><p><strong>Status:</strong> Processing — DP dan termin pertama sudah dibayar.</p><ul><li>Ceremony outdoor dengan cadangan tenda.</li><li>Event Manager standby rundown hari H.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Dedi & Rina',
                'product_slug' => 'budi-lina-intimate-chapel-150',
                'am_email' => 'adel@wofins.com',
                'sequence' => '100003',
                'no_kontrak' => 'KONTR/003/IX/'.$year,
                'status' => OrderStatus::Done,
                'dp_percent' => 40,
                'paid_count' => 2,
                'terms' => [
                    ['persen' => 100, 'bulan' => MonthEnum::Desember->value, 'tahun' => $year],
                ],
                'note' => '<p>Order intimate chapel 150 pax untuk Wedding Dedi &amp; Rina.</p><p><strong>Status:</strong> Done — pembayaran sudah lunas.</p><ul><li>Tamu terbatas keluarga dan sahabat.</li><li>Pelunasan satu termin sebelum hari H.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Eko & Fitri',
                'product_slug' => 'raka-maya-ballroom-thamrin-300',
                'am_email' => 'sari.ananda@wofins.com',
                'sequence' => '100004',
                'no_kontrak' => 'KONTR/004/IX/'.$year,
                'status' => OrderStatus::Pending,
                'dp_percent' => 20,
                'paid_count' => 1,
                'terms' => [
                    ['persen' => 30, 'bulan' => MonthEnum::Oktober->value, 'tahun' => $year],
                    ['persen' => 30, 'bulan' => MonthEnum::Desember->value, 'tahun' => $year],
                    ['persen' => 40, 'bulan' => MonthEnum::Maret->value, 'tahun' => $year + 1],
                ],
                'note' => '<p>Order ballroom Thamrin 300 pax untuk Wedding Eko &amp; Fitri.</p><p><strong>Status:</strong> Pending — baru DP booking.</p><ul><li>Termin berikutnya sesuai jadwal simulasi.</li><li>Dokumen kontrak sudah diunggah.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Fajar & Indira',
                'product_slug' => 'nisa-rama-ballroom-grand-500-gold',
                'am_email' => 'devi.kartika@wofins.com',
                'sequence' => '100005',
                'no_kontrak' => 'KONTR/005/IX/'.$year,
                'status' => OrderStatus::Processing,
                'dp_percent' => 35,
                'paid_count' => 3,
                'terms' => [
                    ['persen' => 25, 'bulan' => MonthEnum::November->value, 'tahun' => $year],
                    ['persen' => 35, 'bulan' => MonthEnum::Januari->value, 'tahun' => $year + 1],
                    ['persen' => 40, 'bulan' => MonthEnum::April->value, 'tahun' => $year + 1],
                ],
                'note' => '<p>Order varian Gold Ballroom Grand 500 untuk Wedding Fajar &amp; Indira.</p><p><strong>Status:</strong> Processing — DP dan dua termin sudah masuk.</p><ul><li>Upgrade florist, live streaming, dan coordinator hari H.</li><li>Sisa pelunasan mengikuti termin terakhir.</li></ul>',
            ],
        ];
    }

    /**
     * @return array{total_price: int, penambahan: int, pengurangan: int, grand_total: int}
     */
    private function pricingFromProduct(Product $product): array
    {
        $totalPrice = (int) ($product->product_price ?? 0);
        $penambahan = (int) ($product->penambahan_publish ?? 0);
        $pengurangan = (int) ($product->pengurangan ?? 0);

        if ($totalPrice === 0) {
            $totalPrice = (int) $product->items()->sum('price_public');
        }

        if ($penambahan === 0) {
            $penambahan = (int) $product->penambahanHarga()->sum('harga_publish');
        }

        if ($pengurangan === 0) {
            $pengurangan = (int) $product->pengurangans()->sum('amount');
        }

        return [
            'total_price' => $totalPrice,
            'penambahan' => $penambahan,
            'pengurangan' => $pengurangan,
            'grand_total' => $totalPrice + $penambahan - $pengurangan,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{keterangan: string, nominal: int, tgl_bayar: string}>
     */
    private function paymentsForOrder(int $grandTotal, array $data): array
    {
        $dpAmount = (int) round($grandTotal * ($data['dp_percent'] / 100));
        $remaining = max(0, $grandTotal - $dpAmount);
        $plan = [[
            'keterangan' => '1 (DP)',
            'nominal' => $dpAmount,
            'tgl_bayar' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(9)->toDateString(),
        ]];

        $allocated = 0;
        $terms = array_values($data['terms']);
        $lastIndex = count($terms) - 1;

        foreach ($terms as $index => $term) {
            $nominal = $index === $lastIndex
                ? ($remaining - $allocated)
                : (int) round($remaining * ((float) $term['persen'] / 100));
            $allocated += $nominal;

            $plan[] = [
                'keterangan' => ($index + 2).' (Termin '.$term['bulan'].')',
                'nominal' => $nominal,
                'tgl_bayar' => $this->monthToDate((string) $term['bulan'], (int) $term['tahun']),
            ];
        }

        $paidCount = max(1, min((int) $data['paid_count'], count($plan)));

        return array_slice($plan, 0, $paidCount);
    }

    private function syncItems(Order $order, Product $product, int $unitPrice): void
    {
        OrderProduct::query()->where('order_id', $order->id)->delete();

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $unitPrice,
        ]);
    }

    /**
     * @param  list<array{keterangan: string, nominal: int, tgl_bayar: string}>  $payments
     */
    private function syncPayments(Order $order, array $payments, int $paymentMethodId, ?string $proofImage): void
    {
        DataPembayaran::withTrashed()->where('order_id', $order->id)->forceDelete();

        foreach ($payments as $payment) {
            DataPembayaran::query()->create([
                'order_id' => $order->id,
                'keterangan' => $payment['keterangan'],
                'payment_method_id' => $paymentMethodId,
                'nominal' => $payment['nominal'],
                'kategori_transaksi' => 'uang_masuk',
                'tgl_bayar' => $payment['tgl_bayar'],
                'image' => $proofImage,
            ]);
        }
    }

    private function syncExpenses(Order $order, Product $product, int $paymentMethodId, ?string $invoiceImage, string $closingDate): void
    {
        Expense::query()
            ->where('order_id', $order->id)
            ->whereNull('nota_dinas_id')
            ->get()
            ->each(fn (Expense $expense) => $expense->delete());

        $items = $product->items()->with('vendor')->get()->take(2);
        $stages = ['down_payment', 'payment_1'];

        foreach ($items as $index => $item) {
            $vendor = $item->vendor;
            if (! $vendor) {
                continue;
            }

            Expense::query()->create([
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
                'payment_method_id' => $paymentMethodId,
                'note' => 'Pembayaran ke '.$vendor->name.' untuk '.$order->name,
                'date_expense' => Carbon::parse($closingDate)->addDays(7 + ($index * 14))->toDateString(),
                'amount' => max(500000, (int) ($item->harga_vendor ?: $vendor->harga_vendor ?: 1000000)),
                'no_nd' => 'ND-0'.str_pad((string) ($order->id * 10 + $index + 1), 4, '0', STR_PAD_LEFT),
                'image' => $invoiceImage,
                'kategori_transaksi' => 'uang_keluar',
                'payment_stage' => $stages[$index] ?? 'additional',
                'account_holder' => $vendor->account_holder ?: $vendor->pic_name ?: $vendor->name,
                'bank_name' => $vendor->bank_name ?: 'BCA',
                'bank_account' => $vendor->bank_account ?: '1234567890',
            ]);
        }
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $counter = 1;

        while (Order::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function monthToDate(string $bulan, int $tahun): string
    {
        $months = [
            MonthEnum::Januari->value => 1,
            MonthEnum::Februari->value => 2,
            MonthEnum::Maret->value => 3,
            MonthEnum::April->value => 4,
            MonthEnum::Mei->value => 5,
            MonthEnum::Juni->value => 6,
            MonthEnum::Juli->value => 7,
            MonthEnum::Agustus->value => 8,
            MonthEnum::September->value => 9,
            MonthEnum::Oktober->value => 10,
            MonthEnum::November->value => 11,
            MonthEnum::Desember->value => 12,
        ];

        return Carbon::create($tahun, $months[$bulan] ?? 1, 15)->toDateString();
    }

    private function seedPdf(string $path, string $title, string $number): string
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        h1 { font-size: 16px; }
        .meta { color: #6b7280; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="meta">Nomor: {$number}</div>
    <p>Dokumen seed untuk mengisi field unggahan pada form Order.</p>
    <p>Pastikan kontrak dan persetujuan produk sudah ditandatangani sebelum dipakai produksi.</p>
</body>
</html>
HTML;

        Storage::disk('public')->put($path, Pdf::loadHTML($html)->setPaper('a4')->output());

        return $path;
    }

    private function seedImage(string $path): ?string
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

        Storage::disk('public')->put($path, File::get($source));

        return $path;
    }
}
