<?php

namespace Database\Seeders;

use App\Enums\MonthEnum;
use App\Models\Company;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\SimulasiProduk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulasiProdukSeeder extends Seeder
{
    public function run(): void
    {
        $prospects = Prospect::query()->get()->keyBy('name_event');
        $neededProspects = collect($this->simulations())->pluck('prospect_name');
        $missingProspects = $neededProspects->reject(fn (string $name) => $prospects->has($name));

        if ($missingProspects->isNotEmpty()) {
            $this->command->warn('Prospek belum lengkap. Menjalankan ProspectSeeder...');
            $this->call(ProspectSeeder::class);
            $prospects = Prospect::query()->get()->keyBy('name_event');
        }

        if ($prospects->isEmpty()) {
            $this->command->error('Prospek belum ada. Jalankan ProspectSeeder terlebih dahulu.');

            return;
        }

        $products = Product::query()->get()->keyBy('slug');
        $neededProducts = collect($this->simulations())->pluck('product_slug');
        $missingProducts = $neededProducts->reject(fn (string $slug) => $products->has($slug));

        if ($missingProducts->isNotEmpty()) {
            $this->command->warn('Produk belum lengkap. Menjalankan ProductSeeder...');
            $this->call(ProductSeeder::class);
            $products = Product::query()->get()->keyBy('slug');
        }

        if ($products->isEmpty()) {
            $this->command->error('Produk belum ada. Jalankan ProductSeeder terlebih dahulu.');

            return;
        }

        $accountManagers = User::role('Account Manager')->orderBy('id')->get();
        if ($accountManagers->isEmpty()) {
            $accountManagers = User::query()->orderBy('id')->get();
        }

        if ($accountManagers->isEmpty()) {
            $this->command->error('User belum ada. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $company = Company::query()->first();
        $nameTtd = filled($company?->owner_name) ? $company->owner_name : 'Rama Dhona Utama';
        $titleTtd = filled($company?->jabatan_owner) ? $company->jabatan_owner : 'Direktur Utama';
        $keepIds = [];
        $created = 0;

        SimulasiProduk::withoutEvents(function () use (
            $prospects,
            $products,
            $accountManagers,
            $nameTtd,
            $titleTtd,
            &$keepIds,
            &$created,
        ): void {
            foreach ($this->simulations() as $index => $data) {
                $prospect = $prospects->get($data['prospect_name']);
                if (! $prospect) {
                    $this->command->warn("Prospek '{$data['prospect_name']}' tidak ditemukan, dilewati.");

                    continue;
                }

                $product = $products->get($data['product_slug']);
                if (! $product) {
                    $this->command->warn("Produk '{$data['product_slug']}' tidak ditemukan, dilewati.");

                    continue;
                }

                $accountManager = $accountManagers->firstWhere('email', $data['am_email'])
                    ?? $accountManagers->firstWhere('id', $prospect->user_id)
                    ?? $accountManagers->values()->get($index % $accountManagers->count());

                $pricing = $this->pricingFromProduct($product);
                $payment = $this->paymentPlan(
                    $pricing['grand_total'],
                    $data['dp_percent'],
                    $data['terms'],
                );

                $existing = SimulasiProduk::withTrashed()
                    ->where('prospect_id', $prospect->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (! $existing) {
                    $existing = SimulasiProduk::withTrashed()
                        ->where('slug', Str::slug((string) $prospect->name_event))
                        ->first();
                }

                $payload = [
                    'prospect_id' => $prospect->id,
                    'product_id' => $product->id,
                    'user_id' => $accountManager->id,
                    'last_edited_by' => $accountManager->id,
                    'slug' => SimulasiProduk::generateUniqueSlug(
                        (string) $prospect->name_event,
                        $existing?->id,
                    ),
                    'contract_number' => $data['contract_number'],
                    'name_ttd' => $nameTtd,
                    'title_ttd' => $titleTtd,
                    'total_price' => $pricing['total_price'],
                    'promo' => 0,
                    'penambahan' => $pricing['penambahan'],
                    'pengurangan' => $pricing['pengurangan'],
                    'grand_total' => $pricing['grand_total'],
                    'payment_dp_amount' => $payment['payment_dp_amount'],
                    'payment_simulation' => $payment['payment_simulation'],
                    'total_simulation' => $payment['total_simulation'],
                    'notes' => $data['notes'],
                ];

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                    }

                    $existing->forceFill($payload)->save();
                    $keepIds[] = $existing->id;
                } else {
                    $createdRecord = SimulasiProduk::query()->create($payload);
                    $keepIds[] = $createdRecord->id;
                }

                $created++;
            }
        });

        if ($keepIds !== []) {
            $removed = SimulasiProduk::query()
                ->whereNotIn('id', $keepIds)
                ->delete();

            if ($removed > 0) {
                $this->command->warn("Simulasi di luar 5 data seed di-soft-delete: {$removed}.");
            }
        }

        $this->command->info("✅ SimulasiProdukSeeder: {$created} simulasi dibuat/diperbarui sesuai form.");
    }

    /**
     * Lima simulasi — field mengikuti SimulasiProdukForm (produk, AM, harga, TTD, DP, termin).
     *
     * @return list<array<string, mixed>>
     */
    private function simulations(): array
    {
        $year = (int) date('Y');

        return [
            [
                'prospect_name' => 'Wedding Andi & Sari',
                'product_slug' => 'nisa-rama-ballroom-grand-500',
                'am_email' => 'rama.dhona@wofins.com',
                'contract_number' => 'SP/001/IX/'.$year,
                'dp_percent' => 30,
                'terms' => [
                    ['persen' => 40, 'bulan' => MonthEnum::Oktober->value, 'tahun' => $year],
                    ['persen' => 60, 'bulan' => MonthEnum::Januari->value, 'tahun' => $year + 1],
                ],
                'notes' => '<p>Simulasi paket Ballroom Grand 500 pax untuk Wedding Andi &amp; Sari.</p><p><strong>Catatan klien:</strong></p><ul><li>Venue hotel 5 bintang, palet gold &amp; putih.</li><li>VIP sekitar 50 tamu, sisanya resepsi 500 pax.</li><li>Harga mengikuti paket dasar, penambahan, dan pengurangan produk.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Budi & Maya',
                'product_slug' => 'andi-sinta-garden-puncak-200',
                'am_email' => 'rina.mardiana@wofins.com',
                'contract_number' => 'SP/002/IX/'.$year,
                'dp_percent' => 25,
                'terms' => [
                    ['persen' => 50, 'bulan' => MonthEnum::November->value, 'tahun' => $year],
                    ['persen' => 50, 'bulan' => MonthEnum::Februari->value, 'tahun' => $year + 1],
                ],
                'notes' => '<p>Simulasi garden outdoor 200 pax untuk Wedding Budi &amp; Maya.</p><p><strong>Request:</strong></p><ul><li>Ceremony outdoor dengan cadangan tenda.</li><li>Dekorasi natural dan menu garden party.</li><li>Pembayaran DP 25% lalu dua termin sama besar.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Dedi & Rina',
                'product_slug' => 'budi-lina-intimate-chapel-150',
                'am_email' => 'adel@wofins.com',
                'contract_number' => 'SP/003/IX/'.$year,
                'dp_percent' => 40,
                'terms' => [
                    ['persen' => 100, 'bulan' => MonthEnum::Desember->value, 'tahun' => $year],
                ],
                'notes' => '<p>Simulasi intimate chapel 150 pax untuk Wedding Dedi &amp; Rina.</p><p><strong>Konsep:</strong></p><ul><li>Tamu terbatas keluarga dan sahabat.</li><li>DP lebih besar (40%) karena scale intimate.</li><li>Pelunasan satu termin sebelum hari H.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Eko & Fitri',
                'product_slug' => 'raka-maya-ballroom-thamrin-300',
                'am_email' => 'sari.ananda@wofins.com',
                'contract_number' => 'SP/004/IX/'.$year,
                'dp_percent' => 20,
                'terms' => [
                    ['persen' => 30, 'bulan' => MonthEnum::Oktober->value, 'tahun' => $year],
                    ['persen' => 30, 'bulan' => MonthEnum::Desember->value, 'tahun' => $year],
                    ['persen' => 40, 'bulan' => MonthEnum::Maret->value, 'tahun' => $year + 1],
                ],
                'notes' => '<p>Simulasi ballroom Thamrin 300 pax untuk Wedding Eko &amp; Fitri.</p><p><strong>Pola bayar:</strong></p><ul><li>DP 20% saat booking.</li><li>Dua termin 30% menjelang akhir tahun.</li><li>Pelunasan 40% H-1 bulan.</li></ul>',
            ],
            [
                'prospect_name' => 'Wedding Fajar & Indira',
                'product_slug' => 'nisa-rama-ballroom-grand-500-gold',
                'am_email' => 'devi.kartika@wofins.com',
                'contract_number' => 'SP/005/IX/'.$year,
                'dp_percent' => 35,
                'terms' => [
                    ['persen' => 25, 'bulan' => MonthEnum::November->value, 'tahun' => $year],
                    ['persen' => 35, 'bulan' => MonthEnum::Januari->value, 'tahun' => $year + 1],
                    ['persen' => 40, 'bulan' => MonthEnum::April->value, 'tahun' => $year + 1],
                ],
                'notes' => '<p>Simulasi varian Gold Ballroom Grand 500 untuk Wedding Fajar &amp; Indira.</p><p><strong>Upgrade Gold:</strong></p><ul><li>Florist, live streaming, spa, dan coordinator hari H.</li><li>Penambahan/pengurangan mengikuti master produk, tidak diubah di simulasi.</li><li>TTD memakai nama owner perusahaan.</li></ul>',
            ],
        ];
    }

    /**
     * Harga mengikuti form: Base Total Price, Penambahan Publish, Total Pengurangan.
     *
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
     * DP + termin = grand total. Setiap termin punya persen, nominal, bulan, dan tahun.
     *
     * @param  list<array{persen: float, bulan: string, tahun: int}>  $terms
     * @return array{payment_dp_amount: int, payment_simulation: list<array<string, mixed>>, total_simulation: int}
     */
    private function paymentPlan(int $grandTotal, int $dpPercent, array $terms): array
    {
        $dpAmount = (int) round($grandTotal * ($dpPercent / 100));
        $remaining = max(0, $grandTotal - $dpAmount);
        $items = [];
        $allocated = 0;
        $lastIndex = count($terms) - 1;

        foreach (array_values($terms) as $index => $term) {
            $persen = (float) $term['persen'];
            $nominal = $index === $lastIndex
                ? ($remaining - $allocated)
                : (int) round($remaining * ($persen / 100));
            $allocated += $nominal;

            $items[] = [
                'persen' => $persen,
                'nominal' => $nominal,
                'bulan' => $term['bulan'],
                'tahun' => (int) $term['tahun'],
            ];
        }

        return [
            'payment_dp_amount' => $dpAmount,
            'payment_simulation' => $items,
            'total_simulation' => $dpAmount + $allocated,
        ];
    }
}
