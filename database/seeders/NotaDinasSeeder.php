<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\NotaDinas;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NotaDinasSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()->with('user')->orderBy('id')->get();
        if ($orders->isEmpty()) {
            $this->command->warn('Order belum ada. Menjalankan OrderSeeder...');
            $this->call(OrderSeeder::class);
            $orders = Order::query()->with('user')->orderBy('id')->get();
        }

        if ($orders->isEmpty()) {
            $this->command->error('Order belum ada. Jalankan OrderSeeder terlebih dahulu.');

            return;
        }

        $pengirim = User::role('Account Manager')->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();
        $penerima = User::role('Finance')->orderBy('id')->first()
            ?? User::query()->where('id', '!=', $pengirim?->id)->orderBy('id')->first()
            ?? $pengirim;
        $approver = User::role('super_admin')->orderBy('id')->first() ?? $penerima;

        if (! $pengirim || ! $penerima) {
            $this->command->error('User belum ada. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $year = (int) date('Y');
        $keepIds = [];
        $created = 0;

        foreach ($orders->values() as $index => $order) {
            $sequence = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $noNd = "ND/BIS/{$sequence}/{$year}";
            $tanggal = Carbon::parse($order->closing_date ?: now())->toDateString();
            $pengirimId = $order->user_id ?: $pengirim->id;

            $existing = NotaDinas::withTrashed()->where('no_nd', $noNd)->first();
            if ($existing?->trashed()) {
                $existing->restore();
            }

            $payload = [
                'kategori_nd' => 'BIS',
                'tanggal' => $tanggal,
                'pengirim_id' => $pengirimId,
                'penerima_id' => $penerima->id,
                'sifat' => $index % 2 === 0 ? 'Segera' : 'Biasa',
                'hal' => 'Permintaan Transfer Vendor '.$order->name,
                'catatan' => 'Nota dinas untuk pembayaran vendor wedding pada '.$order->name.'. Mohon diproses sesuai tahap pembayaran.',
                'status' => 'disetujui',
                'approved_by' => $approver?->id,
                'approved_at' => Carbon::parse($tanggal)->addDay(),
                'nd_upload' => $this->seedPdf($noNd, $order->name),
            ];

            if ($existing) {
                $existing->forceFill($payload)->save();
                $keepIds[] = $existing->id;
            } else {
                $createdNd = NotaDinas::query()->create(array_merge($payload, ['no_nd' => $noNd]));
                $keepIds[] = $createdNd->id;
            }

            $created++;
        }

        if ($keepIds !== []) {
            $extras = NotaDinas::query()->whereNotIn('id', $keepIds)->get();
            foreach ($extras as $notaDinas) {
                $detailIds = $notaDinas->details()->withTrashed()->pluck('id');
                Expense::withTrashed()->whereIn('nota_dinas_detail_id', $detailIds)->forceDelete();
                Expense::withTrashed()->where('nota_dinas_id', $notaDinas->id)->forceDelete();
                $notaDinas->details()->withTrashed()->each(fn ($detail) => $detail->forceDelete());
                $notaDinas->forceDelete();
            }
        }

        $this->command->info("✅ NotaDinasSeeder: {$created} nota dinas dibuat/diperbarui (satu per order).");
    }

    private function seedPdf(string $noNd, string $eventName): string
    {
        $path = 'nota-dinas-uploads/'.Str::slug($noNd).'.pdf';
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
    <h1>Nota Dinas</h1>
    <div class="meta">{$noNd}</div>
    <p>Permintaan transfer vendor untuk {$eventName}.</p>
    <p>Dokumen seed untuk mengisi lampiran form Nota Dinas.</p>
</body>
</html>
HTML;

        Storage::disk('public')->put($path, Pdf::loadHTML($html)->setPaper('a4')->output());

        return $path;
    }
}
