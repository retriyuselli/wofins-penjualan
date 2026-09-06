<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPenambahan;
use App\Models\ProductPengurangan;
use App\Models\ProductVendor;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('slug');

        if ($categories->isEmpty()) {
            $this->command->error('Kategori belum ada. Jalankan CategorySeeder terlebih dahulu.');

            return;
        }

        $productVendors = Vendor::query()
            ->where('status', 'product')
            ->get()
            ->keyBy('slug');

        if ($productVendors->isEmpty()) {
            $this->command->error('Vendor produk belum ada. Jalankan VendorSeeder terlebih dahulu.');

            return;
        }

        $editorId = User::query()->orderBy('id')->value('id');
        $created = 0;

        foreach ($this->products() as $data) {
            $category = $categories->get($data['category_slug']);
            if (! $category) {
                $this->command->warn("Kategori '{$data['category_slug']}' tidak ditemukan, dilewati.");

                continue;
            }

            $parentId = null;
            if (! empty($data['parent_slug'])) {
                $parentId = Product::query()->where('slug', $data['parent_slug'])->value('id');
                if (! $parentId) {
                    $this->command->warn("Parent '{$data['parent_slug']}' belum ada untuk {$data['slug']}.");
                }
            }

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'category_id' => $category->id,
                    'parent_id' => $parentId,
                    'pax' => $data['pax'],
                    'pax_akad' => $data['pax_akad'],
                    'stock' => $data['stock'],
                    'is_active' => $data['is_active'],
                    'is_approved' => $data['is_approved'],
                    'description' => $data['description'],
                    'free_pengurangan' => $data['free_pengurangan'],
                    'image' => $this->seedImage($data['slug']),
                    'last_edited_by_id' => $editorId,
                ]
            );

            $this->syncFacilities($product, $data['facilities'], $productVendors);
            $this->syncPengurangans($product, $data['pengurangans']);
            $this->syncPenambahans($product, $data['penambahans'], $productVendors);
            $this->recalculateTotals($product);

            $created++;
        }

        $this->command->info("✅ ProductSeeder: {$created} produk dibuat/diperbarui sesuai form.");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(): array
    {
        return [
            [
                'name' => 'Nisa Rama_Ballroom Grand_500',
                'slug' => 'nisa-rama-ballroom-grand-500',
                'category_slug' => 'wedding-organizer',
                'parent_slug' => null,
                'pax' => 500,
                'pax_akad' => 150,
                'stock' => 10,
                'is_active' => true,
                'is_approved' => true,
                'description' => '<p>Paket lengkap akad dan resepsi 500 pax di Ballroom Grand Jakarta, termasuk venue, dekorasi, catering, dokumentasi, dan wedding planner.</p>',
                'free_pengurangan' => '<p><strong>Free include:</strong></p><ul><li>Welcome drink 500 pax</li><li>Guest book &amp; souvenir table</li><li>Bridal room 1 hari</li><li>Koordinasi rundown hari H</li></ul>',
                'facilities' => [
                    ['vendor_slug' => 'sewa-ballroom-500-pax', 'quantity' => 1],
                    ['vendor_slug' => 'paket-dekorasi-pelaminan-premium', 'quantity' => 1],
                    ['vendor_slug' => 'paket-catering-300-pax', 'quantity' => 2],
                    ['vendor_slug' => 'paket-dokumentasi-akad-resepsi', 'quantity' => 1],
                    ['vendor_slug' => 'paket-soundsystem-indoor', 'quantity' => 1],
                    ['vendor_slug' => 'paket-make-up-akad-resepsi', 'quantity' => 1],
                    ['vendor_slug' => 'mercedes-benz-s-class', 'quantity' => 1],
                    ['vendor_slug' => 'paket-mc-live-band', 'quantity' => 1],
                    ['vendor_slug' => 'full-wedding-planning', 'quantity' => 1],
                ],
                'pengurangans' => [
                    [
                        'description' => 'Diskon Early Bird 6 Bulan',
                        'amount' => 5000000,
                        'notes' => '<p>Potongan untuk pemesanan minimal 6 bulan sebelum hari H.</p>',
                    ],
                    [
                        'description' => 'Diskon Paket Bundling Venue + WO',
                        'amount' => 2500000,
                        'notes' => '<p>Khusus kombinasi sewa ballroom dan full wedding planning.</p>',
                    ],
                ],
                'penambahans' => [
                    [
                        'vendor_slug' => 'wedding-cake-designer',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Wedding cake 5 susun custom fondant sesuai tema pelaminan.</p>',
                        'notes' => 'Include tasting 1 sesi',
                    ],
                    [
                        'vendor_slug' => 'lighting-design-pro',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Uplighting ballroom, spotlight couple, dan color wash LED.</p>',
                        'notes' => 'Operator lighting standby hingga selesai acara',
                    ],
                ],
            ],
            [
                'name' => 'Nisa Rama_Ballroom Grand_500 Gold',
                'slug' => 'nisa-rama-ballroom-grand-500-gold',
                'category_slug' => 'wedding-organizer',
                'parent_slug' => 'nisa-rama-ballroom-grand-500',
                'pax' => 500,
                'pax_akad' => 150,
                'stock' => 10,
                'is_active' => true,
                'is_approved' => true,
                'description' => '<p>Varian Gold dari paket Ballroom Grand 500 pax: florist, live streaming, spa, calligraphy, dan coordinator hari H.</p>',
                'free_pengurangan' => '<p><strong>Free upgrade Gold:</strong></p><ul><li>Place card calligraphy 50 meja</li><li>Touch up make up 2 jam</li><li>Live streaming backup rekaman</li></ul>',
                'facilities' => [
                    ['vendor_slug' => 'florist-wedding-specialist', 'quantity' => 1],
                    ['vendor_slug' => 'wedding-live-streaming', 'quantity' => 1],
                    ['vendor_slug' => 'bridal-spa-treatment', 'quantity' => 1],
                    ['vendor_slug' => 'bmw-7-series', 'quantity' => 1],
                    ['vendor_slug' => 'wedding-calligraphy-service', 'quantity' => 1],
                    ['vendor_slug' => 'wedding-reception-coordinator', 'quantity' => 1],
                ],
                'pengurangans' => [
                    [
                        'description' => 'Diskon Upgrade Gold',
                        'amount' => 1500000,
                        'notes' => '<p>Potongan jika diambil bersama paket induk Ballroom Grand 500.</p>',
                    ],
                ],
                'penambahans' => [
                    [
                        'vendor_slug' => 'paket-undangan-premium-200',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Undangan premium 200 pcs foil + amplop custom.</p>',
                        'notes' => 'Termasuk digital invitation',
                    ],
                ],
            ],
            [
                'name' => 'Andi Sinta_Garden Puncak_200',
                'slug' => 'andi-sinta-garden-puncak-200',
                'category_slug' => 'venue-gedung',
                'parent_slug' => null,
                'pax' => 200,
                'pax_akad' => 80,
                'stock' => 10,
                'is_active' => true,
                'is_approved' => true,
                'description' => '<p>Paket garden ceremony outdoor di Puncak untuk 200 pax, lengkap tenda cadangan cuaca, florist, lighting, dan dokumentasi.</p>',
                'free_pengurangan' => '<p><strong>Free outdoor:</strong></p><ul><li>Backup tenda hujan</li><li>Generator cadangan</li><li>Welcome mocktail 200 pax</li></ul>',
                'facilities' => [
                    ['vendor_slug' => 'outdoor-garden-ceremony', 'quantity' => 1],
                    ['vendor_slug' => 'florist-wedding-specialist', 'quantity' => 1],
                    ['vendor_slug' => 'wedding-tent-rental', 'quantity' => 1],
                    ['vendor_slug' => 'lighting-design-pro', 'quantity' => 1],
                    ['vendor_slug' => 'paket-catering-300-pax', 'quantity' => 1],
                    ['vendor_slug' => 'paket-soundsystem-indoor', 'quantity' => 1],
                    ['vendor_slug' => 'paket-dokumentasi-akad-resepsi', 'quantity' => 1],
                    ['vendor_slug' => 'paket-undangan-premium-200', 'quantity' => 1],
                    ['vendor_slug' => 'paket-make-up-akad-resepsi', 'quantity' => 1],
                ],
                'pengurangans' => [
                    [
                        'description' => 'Diskon Midweek Garden',
                        'amount' => 3000000,
                        'notes' => '<p>Berlaku untuk acara Senin–Kamis di luar libur nasional.</p>',
                    ],
                    [
                        'description' => 'Potongan Vendor Bundling Outdoor',
                        'amount' => 1500000,
                        'notes' => '<p>Venue garden + tenda + lighting dalam satu paket.</p>',
                    ],
                ],
                'penambahans' => [
                    [
                        'vendor_slug' => 'traditional-gamelan-group',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Gamelan Jawa untuk prosesi akad outdoor.</p>',
                        'notes' => 'Durasi 2 jam termasuk soundcheck',
                    ],
                    [
                        'vendor_slug' => 'wedding-cake-designer',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Naked cake 3 susun tema garden floral.</p>',
                        'notes' => 'Include cake cutting set',
                    ],
                ],
            ],
            [
                'name' => 'Budi Lina_Intimate Chapel_150',
                'slug' => 'budi-lina-intimate-chapel-150',
                'category_slug' => 'make-up-beauty',
                'parent_slug' => null,
                'pax' => 150,
                'pax_akad' => 50,
                'stock' => 10,
                'is_active' => true,
                'is_approved' => true,
                'description' => '<p>Paket intimate 150 pax: make up, dokumentasi, undangan, transport pengantin, dan coordinator hari H.</p>',
                'free_pengurangan' => '<p><strong>Free intimate:</strong></p><ul><li>Trial make up 1 sesi</li><li>Boutonniere pengantin pria</li><li>USB foto 8GB</li></ul>',
                'facilities' => [
                    ['vendor_slug' => 'paket-make-up-akad-resepsi', 'quantity' => 1],
                    ['vendor_slug' => 'mercedes-benz-s-class', 'quantity' => 1],
                    ['vendor_slug' => 'paket-dokumentasi-akad-resepsi', 'quantity' => 1],
                    ['vendor_slug' => 'paket-undangan-premium-200', 'quantity' => 1],
                    ['vendor_slug' => 'wedding-reception-coordinator', 'quantity' => 1],
                    ['vendor_slug' => 'florist-wedding-specialist', 'quantity' => 1],
                    ['vendor_slug' => 'paket-soundsystem-indoor', 'quantity' => 1],
                    ['vendor_slug' => 'traditional-gamelan-group', 'quantity' => 1],
                ],
                'pengurangans' => [
                    [
                        'description' => 'Diskon Intimate Package',
                        'amount' => 2000000,
                        'notes' => '<p>Khusus paket di bawah 200 pax resepsi.</p>',
                    ],
                ],
                'penambahans' => [
                    [
                        'vendor_slug' => 'bridal-spa-treatment',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Facial dan hair spa pre-wedding untuk pengantin wanita.</p>',
                        'notes' => 'Jadwal H-3',
                    ],
                    [
                        'vendor_slug' => 'wedding-calligraphy-service',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Lettering undangan dan place card 150 tamu.</p>',
                        'notes' => 'Include signage welcome board',
                    ],
                ],
            ],
            [
                'name' => 'Raka Maya_Ballroom Thamrin_300',
                'slug' => 'raka-maya-ballroom-thamrin-300',
                'category_slug' => 'venue-gedung',
                'parent_slug' => null,
                'pax' => 300,
                'pax_akad' => 100,
                'stock' => 10,
                'is_active' => true,
                'is_approved' => true,
                'description' => '<p>Paket resepsi ballroom Thamrin 300 pax: venue, dekorasi, catering, sound, MC, lighting, dokumentasi, dan security.</p>',
                'free_pengurangan' => '<p><strong>Free ballroom:</strong></p><ul><li>Valet parkir 4 jam</li><li>LED screen 1 unit</li><li>Security tambahan 2 personel</li></ul>',
                'facilities' => [
                    ['vendor_slug' => 'sewa-ballroom-500-pax', 'quantity' => 1],
                    ['vendor_slug' => 'paket-dekorasi-pelaminan-premium', 'quantity' => 1],
                    ['vendor_slug' => 'paket-catering-300-pax', 'quantity' => 1],
                    ['vendor_slug' => 'paket-soundsystem-indoor', 'quantity' => 1],
                    ['vendor_slug' => 'paket-mc-live-band', 'quantity' => 1],
                    ['vendor_slug' => 'paket-security-8-personel', 'quantity' => 1],
                    ['vendor_slug' => 'paket-dokumentasi-akad-resepsi', 'quantity' => 1],
                    ['vendor_slug' => 'lighting-design-pro', 'quantity' => 1],
                ],
                'pengurangans' => [
                    [
                        'description' => 'Diskon Off Peak Ballroom',
                        'amount' => 4000000,
                        'notes' => '<p>Berlaku untuk tanggal non-weekend di luar Desember.</p>',
                    ],
                    [
                        'description' => 'Potongan Security Bundling',
                        'amount' => 500000,
                        'notes' => '<p>Jika security diambil bersama sewa ballroom.</p>',
                    ],
                ],
                'penambahans' => [
                    [
                        'vendor_slug' => 'wedding-live-streaming',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Live streaming multi-kamera untuk keluarga di luar kota.</p>',
                        'notes' => 'Include rekaman 1 file master',
                    ],
                    [
                        'vendor_slug' => 'wedding-cake-designer',
                        'kategori_transaksi' => 'uang_masuk',
                        'description' => '<p>Wedding cake 4 susun tema modern gold.</p>',
                        'notes' => 'Include cake stand',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $facilities
     * @param  \Illuminate\Support\Collection<string, Vendor>  $productVendors
     */
    private function syncFacilities(Product $product, array $facilities, $productVendors): void
    {
        $keepVendorIds = [];

        foreach ($facilities as $item) {
            /** @var Vendor|null $vendor */
            $vendor = $productVendors->get($item['vendor_slug']);
            if (! $vendor) {
                $this->command->warn("Vendor produk '{$item['vendor_slug']}' tidak ditemukan.");

                continue;
            }

            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $hargaPublish = (int) $vendor->harga_publish;
            $hargaVendor = (int) $vendor->harga_vendor;

            ProductVendor::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'vendor_id' => $vendor->id,
                    'simulasi_produk_id' => null,
                ],
                [
                    'quantity' => $qty,
                    'harga_publish' => $hargaPublish,
                    'harga_vendor' => $hargaVendor,
                    'price_public' => $hargaPublish * $qty,
                    'total_price' => $hargaVendor * $qty,
                    'description' => $item['description'] ?? $vendor->description,
                    'kontrak_kerjasama' => $vendor->kontrak_kerjasama,
                ]
            );

            $keepVendorIds[] = $vendor->id;
        }

        ProductVendor::query()
            ->where('product_id', $product->id)
            ->whereNull('simulasi_produk_id')
            ->when($keepVendorIds, fn ($q) => $q->whereNotIn('vendor_id', $keepVendorIds))
            ->delete();
    }

    /**
     * @param  list<array<string, mixed>>  $pengurangans
     */
    private function syncPengurangans(Product $product, array $pengurangans): void
    {
        ProductPengurangan::query()->where('product_id', $product->id)->delete();

        foreach ($pengurangans as $row) {
            ProductPengurangan::create([
                'product_id' => $product->id,
                'description' => $row['description'],
                'amount' => (int) $row['amount'],
                'notes' => $row['notes'] ?? null,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $penambahans
     * @param  \Illuminate\Support\Collection<string, Vendor>  $productVendors
     */
    private function syncPenambahans(Product $product, array $penambahans, $productVendors): void
    {
        ProductPenambahan::withTrashed()
            ->where('product_id', $product->id)
            ->forceDelete();

        foreach ($penambahans as $row) {
            /** @var Vendor|null $vendor */
            $vendor = $productVendors->get($row['vendor_slug']);
            if (! $vendor) {
                $this->command->warn("Vendor penambahan '{$row['vendor_slug']}' tidak ditemukan.");

                continue;
            }

            $description = $row['description'] ?? $vendor->description;
            if (! empty($row['notes'])) {
                $description .= '<p>'.$row['notes'].'</p>';
            }

            ProductPenambahan::create([
                'product_id' => $product->id,
                'vendor_id' => $vendor->id,
                'harga_publish' => (int) ($row['harga_publish'] ?? $vendor->harga_publish),
                'harga_vendor' => (int) ($row['harga_vendor'] ?? $vendor->harga_vendor),
                'description' => $description,
                'kategori_transaksi' => $row['kategori_transaksi'] ?? 'uang_masuk',
            ]);
        }
    }

    private function recalculateTotals(Product $product): void
    {
        $product->load(['items', 'pengurangans', 'penambahanHarga']);

        $productPrice = (int) $product->items->sum('price_public');
        $pengurangan = (int) $product->pengurangans->sum('amount');
        $penambahanPublish = (int) $product->penambahanHarga->sum('harga_publish');
        $penambahanVendor = (int) $product->penambahanHarga->sum('harga_vendor');

        $product->forceFill([
            'product_price' => $productPrice,
            'pengurangan' => $pengurangan,
            'penambahan' => $penambahanPublish,
            'penambahan_publish' => $penambahanPublish,
            'penambahan_vendor' => $penambahanVendor,
            'price' => $productPrice - $pengurangan + $penambahanPublish,
        ])->save();
    }

    private function seedImage(string $slug): ?string
    {
        $sources = [
            public_path('images/logomki.png'),
            public_path('images/logo.png'),
            public_path('logo.png'),
        ];

        $source = collect($sources)->first(fn (string $path) => File::exists($path));
        if (! $source) {
            return null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'png';
        $path = 'products/'.$slug.'.'.$extension;

        Storage::disk('public')->put($path, File::get($source));

        return $path;
    }
}
