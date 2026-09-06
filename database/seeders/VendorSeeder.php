<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Vendor;
use App\Models\VendorPriceHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('slug');

        if ($categories->isEmpty()) {
            $this->command->error('Kategori belum ada. Jalankan CategorySeeder terlebih dahulu.');

            return;
        }

        $groups = $this->vendorGroups();
        $createdParents = 0;
        $createdProducts = 0;

        foreach ($groups as $group) {
            $category = $categories->get($group['category_slug']);
            if (! $category) {
                $this->command->warn("Kategori '{$group['category_slug']}' tidak ditemukan, dilewati.");

                continue;
            }

            $parentData = $group['vendor'];
            $parent = Vendor::updateOrCreate(
                ['slug' => $parentData['slug']],
                $this->vendorAttributes($parentData, $category->id, [
                    'status' => 'vendor',
                    'parent_id' => null,
                    'is_master' => $parentData['is_master'] ?? true,
                    'is_published' => $parentData['is_published'] ?? true,
                    'stock' => $parentData['stock'] ?? 0,
                    'harga_publish' => 0,
                    'harga_vendor' => 0,
                ])
            );
            $createdParents++;

            foreach ($group['products'] as $productData) {
                $product = Vendor::updateOrCreate(
                    ['slug' => $productData['slug']],
                    $this->vendorAttributes($productData, $category->id, [
                        'status' => 'product',
                        'parent_id' => $parent->id,
                        'is_master' => false,
                        'is_published' => $productData['is_published'] ?? true,
                        'stock' => $productData['stock'] ?? 10,
                        'bank_name' => $parent->bank_name,
                        'bank_account' => $parent->bank_account,
                        'account_holder' => $parent->account_holder,
                    ])
                );
                $this->seedPriceHistory($product);
                $createdProducts++;
            }
        }

        $this->command->info("✅ VendorSeeder: {$createdParents} vendor induk dan {$createdProducts} produk dibuat/diperbarui.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function vendorAttributes(array $data, int $categoryId, array $overrides): array
    {
        $publish = (int) ($data['harga_publish'] ?? $overrides['harga_publish'] ?? 0);
        $vendorPrice = (int) ($data['harga_vendor'] ?? $overrides['harga_vendor'] ?? 0);

        return array_merge([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'phone' => $data['phone'],
            'pic_name' => $data['pic_name'],
            'address' => $data['address'],
            'description' => $data['description'],
            'category_id' => $categoryId,
            'harga_publish' => $publish,
            'harga_vendor' => $vendorPrice,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'account_holder' => $data['account_holder'] ?? null,
            'kontrak_kerjasama' => null,
        ], $overrides);
    }

    private function seedPriceHistory(Vendor $vendor): void
    {
        if (! Schema::hasTable('vendor_price_histories')) {
            return;
        }

        $from = now()->startOfYear()->toDateString();
        $to = now()->endOfYear()->toDateString();

        VendorPriceHistory::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->where('effective_from', '!=', $from)
            ->update(['status' => 'inactive']);

        VendorPriceHistory::updateOrCreate(
            [
                'vendor_id' => $vendor->id,
                'effective_from' => $from,
            ],
            [
                'harga_publish' => (int) $vendor->harga_publish,
                'harga_vendor' => (int) $vendor->harga_vendor,
                'effective_to' => $to,
                'status' => 'active',
                'description' => 'Harga periode berjalan',
            ]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function vendorGroups(): array
    {
        return [
            [
                'category_slug' => 'dekorasi-pelaminan',
                'vendor' => [
                    'name' => 'Dekorasi Mewah Jakarta',
                    'slug' => 'dekorasi-mewah-jakarta',
                    'pic_name' => 'Ibu Sari Dewi',
                    'phone' => '8123456789',
                    'address' => 'Jl. Kemang Raya No. 123, Jakarta Selatan',
                    'description' => '<p>Spesialis dekorasi wedding mewah dengan pengalaman 10+ tahun. Melayani dekorasi pelaminan, backdrop, bunga, lighting, dan tata ruang.</p>',
                    'bank_name' => 'BCA',
                    'bank_account' => '1234567890',
                    'account_holder' => 'Sari Dewi Decoration',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Dekorasi Pelaminan Premium',
                        'slug' => 'paket-dekorasi-pelaminan-premium',
                        'pic_name' => 'Ibu Sari Dewi',
                        'phone' => '8123456789',
                        'address' => 'Jl. Kemang Raya No. 123, Jakarta Selatan',
                        'description' => '<p><strong>Include:</strong></p><ul><li>Dekorasi pelaminan custom</li><li>Backdrop &amp; bunga</li><li>Lighting ambience</li><li>Konsultasi tema</li></ul>',
                        'harga_publish' => 15000000,
                        'harga_vendor' => 12000000,
                        'stock' => 8,
                    ],
                    [
                        'name' => 'Florist Wedding Specialist',
                        'slug' => 'florist-wedding-specialist',
                        'pic_name' => 'Sinta Bunga',
                        'phone' => '8901234567',
                        'address' => 'Jl. Melawai No. 234, Jakarta Selatan',
                        'description' => '<p><strong>Layanan:</strong></p><ul><li>Bridal bouquet</li><li>Boutonniere</li><li>Centerpiece</li><li>Dekorasi altar</li></ul>',
                        'harga_publish' => 4500000,
                        'harga_vendor' => 3600000,
                        'stock' => 12,
                    ],
                ],
            ],
            [
                'category_slug' => 'catering-makanan',
                'vendor' => [
                    'name' => 'Catering Premium Bogor',
                    'slug' => 'catering-premium-bogor',
                    'pic_name' => 'Bapak Ahmad Rizki',
                    'phone' => '8234567890',
                    'address' => 'Jl. Raya Bogor No. 456, Bogor',
                    'description' => '<p>Layanan catering premium untuk wedding dengan menu Indonesia dan Internasional.</p>',
                    'bank_name' => 'Mandiri',
                    'bank_account' => '2345678901',
                    'account_holder' => 'Ahmad Rizki Catering',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Catering 300 Pax',
                        'slug' => 'paket-catering-300-pax',
                        'pic_name' => 'Bapak Ahmad Rizki',
                        'phone' => '8234567890',
                        'address' => 'Jl. Raya Bogor No. 456, Bogor',
                        'description' => '<p><strong>Paket:</strong></p><ul><li>300 pax</li><li>Menu Indonesia &amp; Internasional</li><li>Prasmanan + dessert</li></ul>',
                        'harga_publish' => 8500000,
                        'harga_vendor' => 7000000,
                        'stock' => 10,
                    ],
                    [
                        'name' => 'Wedding Cake Designer',
                        'slug' => 'wedding-cake-designer',
                        'pic_name' => 'Chef Miranda',
                        'phone' => '8123456780',
                        'address' => 'Jl. Pondok Indah No. 890, Jakarta Selatan',
                        'description' => '<p>Custom multi-tier cake dengan fondant dan sugar art.</p>',
                        'harga_publish' => 3500000,
                        'harga_vendor' => 2800000,
                        'stock' => 6,
                    ],
                ],
            ],
            [
                'category_slug' => 'foto-video',
                'vendor' => [
                    'name' => 'Foto & Video Cinematic',
                    'slug' => 'foto-video-cinematic',
                    'pic_name' => 'Dedi Photographer',
                    'phone' => '8345678901',
                    'address' => 'Jl. Sudirman No. 789, Jakarta Pusat',
                    'description' => '<p>Jasa foto dan video wedding cinematic dengan peralatan profesional.</p>',
                    'bank_name' => 'BRI',
                    'bank_account' => '3456789012',
                    'account_holder' => 'Dedi Cinematic Studio',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Dokumentasi Akad + Resepsi',
                        'slug' => 'paket-dokumentasi-akad-resepsi',
                        'pic_name' => 'Dedi Photographer',
                        'phone' => '8345678901',
                        'address' => 'Jl. Sudirman No. 789, Jakarta Pusat',
                        'description' => '<p><strong>Include:</strong></p><ul><li>Pre-wedding</li><li>Dokumentasi hari H</li><li>Cinematic video</li><li>Album premium</li></ul>',
                        'harga_publish' => 12000000,
                        'harga_vendor' => 9500000,
                        'stock' => 5,
                    ],
                    [
                        'name' => 'Wedding Live Streaming',
                        'slug' => 'wedding-live-streaming',
                        'pic_name' => 'Digital Stream',
                        'phone' => '8798901234',
                        'address' => 'Jl. Cilandak No. 497, Jakarta Selatan',
                        'description' => '<p>Live streaming multi-kamera dengan rekaman cadangan.</p>',
                        'harga_publish' => 3500000,
                        'harga_vendor' => 2800000,
                        'stock' => 15,
                    ],
                ],
            ],
            [
                'category_slug' => 'sound-system-audio',
                'vendor' => [
                    'name' => 'Soundsystem Pro Jakarta',
                    'slug' => 'soundsystem-pro-jakarta',
                    'pic_name' => 'Eko Saputra',
                    'phone' => '8456789012',
                    'address' => 'Jl. Gatot Subroto No. 321, Jakarta Selatan',
                    'description' => '<p>Soundsystem profesional untuk indoor dan outdoor wedding.</p>',
                    'bank_name' => 'BNI',
                    'bank_account' => '4567890123',
                    'account_holder' => 'Eko Audio Visual',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Soundsystem Indoor',
                        'slug' => 'paket-soundsystem-indoor',
                        'pic_name' => 'Eko Saputra',
                        'phone' => '8456789012',
                        'address' => 'Jl. Gatot Subroto No. 321, Jakarta Selatan',
                        'description' => '<p><strong>Peralatan:</strong></p><ul><li>Speaker line array</li><li>Mixing console digital</li><li>Microphone wireless</li><li>Operator</li></ul>',
                        'harga_publish' => 5500000,
                        'harga_vendor' => 4200000,
                        'stock' => 7,
                    ],
                    [
                        'name' => 'Lighting Design Pro',
                        'slug' => 'lighting-design-pro',
                        'pic_name' => 'Agus Lighting',
                        'phone' => '8012345678',
                        'address' => 'Jl. Casablanca No. 567, Jakarta Selatan',
                        'description' => '<p>Uplighting, spotlight couple, dan color wash LED.</p>',
                        'harga_publish' => 6500000,
                        'harga_vendor' => 5200000,
                        'stock' => 9,
                    ],
                ],
            ],
            [
                'category_slug' => 'make-up-beauty',
                'vendor' => [
                    'name' => 'Make Up Artist Professional',
                    'slug' => 'make-up-artist-professional',
                    'pic_name' => 'Fitri Make Up',
                    'phone' => '8567890123',
                    'address' => 'Jl. Kemang Selatan No. 654, Jakarta',
                    'description' => '<p>Make up pengantin profesional, tradisional hingga modern.</p>',
                    'bank_name' => 'CIMB Niaga',
                    'bank_account' => '5678901234',
                    'account_holder' => 'Fitri Maharani',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Make Up Akad & Resepsi',
                        'slug' => 'paket-make-up-akad-resepsi',
                        'pic_name' => 'Fitri Make Up',
                        'phone' => '8567890123',
                        'address' => 'Jl. Kemang Selatan No. 654, Jakarta',
                        'description' => '<p><strong>Layanan:</strong></p><ul><li>Make up akad &amp; resepsi</li><li>Hair styling</li><li>Trial make up</li><li>Touch up</li></ul>',
                        'harga_publish' => 3500000,
                        'harga_vendor' => 2800000,
                        'stock' => 10,
                    ],
                    [
                        'name' => 'Bridal Spa Treatment',
                        'slug' => 'bridal-spa-treatment',
                        'pic_name' => 'Lina Spa',
                        'phone' => '8889012345',
                        'address' => 'Jl. Senayan No. 548, Jakarta Selatan',
                        'description' => '<p>Facial, body treatment, dan hair spa pre-wedding.</p>',
                        'harga_publish' => 3500000,
                        'harga_vendor' => 2800000,
                        'stock' => 8,
                    ],
                ],
            ],
            [
                'category_slug' => 'transportation',
                'vendor' => [
                    'name' => 'Wedding Car Rental Luxury',
                    'slug' => 'wedding-car-rental-luxury',
                    'pic_name' => 'Indra Mobil',
                    'phone' => '8678901234',
                    'address' => 'Jl. HR Rasuna Said No. 987, Jakarta',
                    'description' => '<p>Rental mobil mewah untuk pengantin, unit terawat dengan dekorasi.</p>',
                    'bank_name' => 'Danamon',
                    'bank_account' => '6789012345',
                    'account_holder' => 'Indra Luxury Car',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Mercedes Benz S-Class',
                        'slug' => 'mercedes-benz-s-class',
                        'pic_name' => 'Indra Mobil',
                        'phone' => '8678901234',
                        'address' => 'Jl. HR Rasuna Said No. 987, Jakarta',
                        'description' => '<p>Sewa harian termasuk driver dan dekorasi bunga.</p>',
                        'harga_publish' => 2500000,
                        'harga_vendor' => 2000000,
                        'stock' => 3,
                    ],
                    [
                        'name' => 'BMW 7 Series',
                        'slug' => 'bmw-7-series',
                        'pic_name' => 'Indra Mobil',
                        'phone' => '8678901234',
                        'address' => 'Jl. HR Rasuna Said No. 987, Jakarta',
                        'description' => '<p>Sewa harian termasuk driver dan BBM area Jakarta.</p>',
                        'harga_publish' => 2200000,
                        'harga_vendor' => 1800000,
                        'stock' => 2,
                    ],
                ],
            ],
            [
                'category_slug' => 'entertainment-mc',
                'vendor' => [
                    'name' => 'Entertainment Wedding Organizer',
                    'slug' => 'entertainment-wedding-organizer',
                    'pic_name' => 'Fajar Entertainment',
                    'phone' => '8789012345',
                    'address' => 'Jl. Senopati No. 159, Jakarta Selatan',
                    'description' => '<p>MC, band, dancer, dan pertunjukan tradisional untuk wedding.</p>',
                    'bank_name' => 'Permata',
                    'bank_account' => '7890123456',
                    'account_holder' => 'Fajar Media Entertainment',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket MC + Live Band',
                        'slug' => 'paket-mc-live-band',
                        'pic_name' => 'Ricky Band',
                        'phone' => '8801234567',
                        'address' => 'Jl. Blok M No. 246, Jakarta Selatan',
                        'description' => '<p>MC bilingual dan live band (vocal, gitar, bass, drum, keyboard).</p>',
                        'harga_publish' => 8500000,
                        'harga_vendor' => 7000000,
                        'stock' => 6,
                    ],
                    [
                        'name' => 'Traditional Gamelan Group',
                        'slug' => 'traditional-gamelan-group',
                        'pic_name' => 'Pak Slamet',
                        'phone' => '8234567801',
                        'address' => 'Jl. Yogyakarta No. 123, Yogyakarta',
                        'description' => '<p>Gamelan Jawa lengkap untuk prosesi adat.</p>',
                        'harga_publish' => 4000000,
                        'harga_vendor' => 3200000,
                        'stock' => 4,
                    ],
                ],
            ],
            [
                'category_slug' => 'wedding-organizer',
                'vendor' => [
                    'name' => 'Wedding Planner Consultant',
                    'slug' => 'wedding-planner-consultant',
                    'pic_name' => 'Ibu Ratna',
                    'phone' => '8456780123',
                    'address' => 'Jl. Kuningan No. 789, Jakarta Selatan',
                    'description' => '<p>Full planning dari konsep hingga hari H.</p>',
                    'bank_name' => 'BRI',
                    'bank_account' => '4567801234',
                    'account_holder' => 'Ratna Wedding Consultant',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Full Wedding Planning',
                        'slug' => 'full-wedding-planning',
                        'pic_name' => 'Ibu Ratna',
                        'phone' => '8456780123',
                        'address' => 'Jl. Kuningan No. 789, Jakarta Selatan',
                        'description' => '<p><strong>Services:</strong></p><ul><li>Vendor coordination</li><li>Budget management</li><li>Timeline execution</li><li>Day-of coordination</li></ul>',
                        'harga_publish' => 12000000,
                        'harga_vendor' => 9500000,
                        'stock' => 5,
                    ],
                    [
                        'name' => 'Wedding Reception Coordinator',
                        'slug' => 'wedding-reception-coordinator',
                        'pic_name' => 'Tia Coordinator',
                        'phone' => '8293456789',
                        'address' => 'Jl. Gatot Subroto No. 982, Jakarta Selatan',
                        'description' => '<p>Koordinasi hari H: rundown, vendor, dan tamu.</p>',
                        'harga_publish' => 4500000,
                        'harga_vendor' => 3600000,
                        'stock' => 8,
                    ],
                ],
            ],
            [
                'category_slug' => 'venue-gedung',
                'vendor' => [
                    'name' => 'Venue Ballroom Grand',
                    'slug' => 'venue-ballroom-grand',
                    'pic_name' => 'Dewi Sartika',
                    'phone' => '8890123456',
                    'address' => 'Jl. Thamrin No. 88, Jakarta Pusat',
                    'description' => '<p>Ballroom 500–1000 tamu dengan fasilitas modern.</p>',
                    'bank_name' => 'BCA',
                    'bank_account' => '8901234567',
                    'account_holder' => 'Grand Ballroom Jakarta',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Sewa Ballroom 500 Pax',
                        'slug' => 'sewa-ballroom-500-pax',
                        'pic_name' => 'Dewi Sartika',
                        'phone' => '8890123456',
                        'address' => 'Jl. Thamrin No. 88, Jakarta Pusat',
                        'description' => '<p>Ballroom AC, stage, parkir, dan bridal room.</p>',
                        'harga_publish' => 25000000,
                        'harga_vendor' => 20000000,
                        'stock' => 4,
                    ],
                    [
                        'name' => 'Outdoor Garden Ceremony',
                        'slug' => 'outdoor-garden-ceremony',
                        'pic_name' => 'Budi Outdoor',
                        'phone' => '8780123456',
                        'address' => 'Jl. Puncak No. 987, Bogor',
                        'description' => '<p>Garden ceremony dengan backup tenda cuaca.</p>',
                        'harga_publish' => 18000000,
                        'harga_vendor' => 14500000,
                        'stock' => 3,
                    ],
                ],
            ],
            [
                'category_slug' => 'undangan-souvenir',
                'vendor' => [
                    'name' => 'Invitation Card Designer',
                    'slug' => 'invitation-card-designer',
                    'pic_name' => 'Rina Design',
                    'phone' => '8345678012',
                    'address' => 'Jl. Cipete No. 456, Jakarta Selatan',
                    'description' => '<p>Undangan custom, emboss, foil, dan digital.</p>',
                    'bank_name' => 'Mandiri',
                    'bank_account' => '3456780123',
                    'account_holder' => 'Rina Creative Design',
                    'is_master' => true,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Undangan Premium 200 pcs',
                        'slug' => 'paket-undangan-premium-200',
                        'pic_name' => 'Rina Design',
                        'phone' => '8345678012',
                        'address' => 'Jl. Cipete No. 456, Jakarta Selatan',
                        'description' => '<p>Kertas premium, foil, dan amplop custom.</p>',
                        'harga_publish' => 2500000,
                        'harga_vendor' => 2000000,
                        'stock' => 20,
                    ],
                    [
                        'name' => 'Wedding Calligraphy Service',
                        'slug' => 'wedding-calligraphy-service',
                        'pic_name' => 'Maya Calligraphy',
                        'phone' => '8697890123',
                        'address' => 'Jl. Benda No. 396, Jakarta Selatan',
                        'description' => '<p>Lettering undangan, place card, dan signage.</p>',
                        'harga_publish' => 2800000,
                        'harga_vendor' => 2300000,
                        'stock' => 10,
                    ],
                ],
            ],
            [
                'category_slug' => 'lainnya',
                'vendor' => [
                    'name' => 'Security Wedding Service',
                    'slug' => 'security-wedding-service',
                    'pic_name' => 'Joko Security',
                    'phone' => '8678012345',
                    'address' => 'Jl. Pancoran No. 654, Jakarta Selatan',
                    'description' => '<p>Keamanan acara, parkir, dan crowd control.</p>',
                    'bank_name' => 'Danamon',
                    'bank_account' => '6780123456',
                    'account_holder' => 'Joko Security Service',
                    'is_master' => false,
                    'is_published' => true,
                ],
                'products' => [
                    [
                        'name' => 'Paket Security 8 Personel',
                        'slug' => 'paket-security-8-personel',
                        'pic_name' => 'Joko Security',
                        'phone' => '8678012345',
                        'address' => 'Jl. Pancoran No. 654, Jakarta Selatan',
                        'description' => '<p>8 personel, parkir, dan pengamanan souvenir.</p>',
                        'harga_publish' => 2800000,
                        'harga_vendor' => 2300000,
                        'stock' => 12,
                    ],
                    [
                        'name' => 'Wedding Tent Rental',
                        'slug' => 'wedding-tent-rental',
                        'pic_name' => 'Jaya Tenda',
                        'phone' => '8778901234',
                        'address' => 'Jl. Cakung No. 437, Jakarta Timur',
                        'description' => '<p>Tenda, flooring, dan lighting outdoor.</p>',
                        'harga_publish' => 8500000,
                        'harga_vendor' => 7000000,
                        'stock' => 5,
                    ],
                ],
            ],
        ];
    }
}
