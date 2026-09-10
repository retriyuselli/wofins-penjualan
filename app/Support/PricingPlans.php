<?php

namespace App\Support;

class PricingPlans
{
    /**
     * Fitur yang digating per paket.
     */
    public const FEATURE_RECONCILIATION = 'reconciliation';

    public const FEATURE_PAYROLL = 'payroll';

    public const FEATURE_ROLE_MANAGEMENT = 'role_management';

    public const FEATURE_ADVANCED_REPORTS = 'advanced_reports';

    public const FEATURE_MULTI_APPROVAL = 'multi_approval';

    public const FEATURE_PROJECTS = 'projects';

    public const FEATURE_SIMULASI = 'simulasi';

    public const FEATURE_BASIC_FINANCE = 'basic_finance';

    public const FEATURE_FIXED_ASSETS = 'fixed_assets';

    public const FEATURE_NOTA_DINAS = 'nota_dinas';

    public const FEATURE_DOCUMENTS = 'documents';

    public const FEATURE_CREW_FREELANCE = 'crew_freelance';

    /**
     * Paket layanan WOFINS — sumber tunggal untuk halaman Harga,
     * formulir pendaftaran, kontak, email, Filament, dan feature gate.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'starter',
                'name' => 'Starter',
                'desc' => 'Kelola proyek wedding, kas, dan laporan dasar lebih rapi — cocok untuk WO solo yang baru merapikan operasional.',
                'price' => '110.000',
                'unit' => null,
                'price_monthly' => 110_000,
                'price_semiannual' => 660_000,
                'price_annual' => 1_320_000,
                'price_biennial' => 2_640_000,
                'price_triennial' => 3_960_000,
                'price_quadrennial' => 5_280_000,
                'seat_limit' => 1,
                'vendor_limit' => null,
                'product_limit' => null,
                'order_limit' => null,
                'prospect_limit' => null,
                'simulasi_limit' => 0,
                'payment_method_limit' => null,
                'fixed_asset_limit' => 0,
                'piutang_limit' => null,
                'pembayaran_piutang_limit' => null,
                'category_limit' => 10,
                'data_pembayaran_limit' => null,
                'expense_limit' => null,
                'expense_ops_limit' => null,
                'pendapatan_lain_limit' => null,
                'pengeluaran_lain_limit' => null,
                'popular' => false,
                'cta' => 'Pilih Paket Starter',
                'cta_class' => 'wf-btn-ghost',
                'check' => 'navy',
                'features' => [
                    [
                        'label' => '1 Pengguna',
                        'tip' => 'Satu akun login untuk Anda sebagai pemilik. Cocok jika operasional masih dikelola sendiri.',
                    ],
                    [
                        'label' => 'Invoice Proyek',
                        'tip' => 'Terbitkan invoice proyek wedding untuk penagihan klien secara rapi dan terdokumentasi.',
                    ],
                    [
                        'label' => 'Management Prospect',
                        'tip' => 'Catat dan kelola calon klien (prospek) — dari lead awal hingga siap closing.',
                    ],
                    [
                        'label' => 'Management Vendor',
                        'tip' => 'Simpan data vendor wedding: kontak, kategori, dan riwayat kerja sama.',
                    ],
                    [
                        'label' => 'Management Product',
                        'tip' => 'Kelola katalog produk/paket wedding untuk dipakai di proyek.',
                    ],
                    [
                        'label' => 'Management Proyek',
                        'tip' => 'Atur proyek wedding: status, detail acara, tim, dan progres hingga selesai.',
                    ],
                    [
                        'label' => 'Management Pengeluaran Wedding',
                        'tip' => 'Catat biaya vendor dan pengeluaran per proyek wedding.',
                    ],
                    [
                        'label' => 'Management Pengeluaran Lain-Lain',
                        'tip' => 'Catat pengeluaran di luar proyek wedding (operasional/lainnya).',
                    ],
                    [
                        'label' => 'Management Pemasukan Wedding',
                        'tip' => 'Catat pembayaran klien dan pemasukan per proyek wedding.',
                    ],
                    [
                        'label' => 'Management Pemasukan Lain-Lain',
                        'tip' => 'Catat pemasukan di luar proyek wedding.',
                    ],
                    [
                        'label' => 'Nota Dinas Untuk Tracking Pengeluaran',
                        'tip' => 'Buat dan lacak nota dinas pengeluaran agar setiap biaya tercatat, tersetujui, dan mudah ditelusuri.',
                    ],
                    [
                        'label' => 'Kas Dan Bank',
                        'tip' => 'Kelola rekening kas dan bank untuk memantau saldo serta arus uang masuk/keluar.',
                    ],
                    [
                        'label' => 'Laporan Keuangan Dasar',
                        'tip' => 'Lihat ringkasan keuangan proyek dan operasional. Rekonsiliasi bank tersedia di paket Professional.',
                    ],
                    [
                        'label' => 'Support Jam Kerja',
                        'tip' => 'Bantuan via saluran standar pada jam kerja. Antrian lebih cepat tersedia di Professional dan Business.',
                    ],
                ],
                'feature_keys' => [
                    self::FEATURE_PROJECTS,
                    self::FEATURE_BASIC_FINANCE,
                    self::FEATURE_NOTA_DINAS,
                ],
                'selectable' => true,
            ],
            [
                'key' => 'professional',
                'name' => 'Professional',
                'desc' => 'Untuk WO solo yang butuh simulasi klien, rekonsiliasi bank, dan payroll — satu akun pemilik.',
                'price' => '180.000',
                'unit' => null,
                'price_monthly' => 180_000,
                'price_semiannual' => 1_080_000,
                'price_annual' => 2_160_000,
                'price_biennial' => 4_320_000,
                'price_triennial' => 6_480_000,
                'price_quadrennial' => 8_640_000,
                'seat_limit' => 1,
                'vendor_limit' => null,
                'product_limit' => null,
                'order_limit' => null,
                'prospect_limit' => null,
                'simulasi_limit' => null,
                'payment_method_limit' => null,
                'fixed_asset_limit' => null,
                'piutang_limit' => null,
                'pembayaran_piutang_limit' => null,
                'category_limit' => 40,
                'data_pembayaran_limit' => null,
                'expense_limit' => null,
                'expense_ops_limit' => null,
                'pendapatan_lain_limit' => null,
                'pengeluaran_lain_limit' => null,
                'popular' => true,
                'cta' => 'Pilih Paket Professional',
                'cta_class' => 'wf-btn-gold',
                'check' => '',
                'features' => [
                    [
                        'label' => '1 Pengguna (Pemilik)',
                        'tip' => 'Satu akun login untuk pemilik. Tim 2 orang atau lebih naik ke paket Business.',
                    ],
                    [
                        'label' => 'Semua Kemampuan Starter',
                        'tip' => 'Termasuk proyek wedding, prospek, vendor, produk, nota dinas, kas, rekening, dan laporan dasar.',
                    ],
                    [
                        'label' => 'Simulasi Wedding',
                        'tip' => 'Buat simulasi paket wedding untuk calon klien, lengkap dengan preview dan unduhan PDF.',
                    ],
                    [
                        'label' => 'Draft Kontrak Kerja',
                        'tip' => 'Siapkan draf kontrak kerja dari data simulasi/proyek sebagai dokumen awal kesepakatan.',
                    ],
                    [
                        'label' => 'Fixed Assets',
                        'tip' => 'Catat dan kelola aset tetap perusahaan: nilai perolehan, depresiasi, serta status aset.',
                    ],
                    [
                        'label' => 'Rekonsiliasi Rekening Koran',
                        'tip' => 'Import rekening koran, cocokkan transaksi, lalu unduh hasil rekonsiliasi. Termasuk laporan arus kas bersih (Net Cash Flow).',
                    ],
                    [
                        'label' => 'Payroll',
                        'tip' => 'Kelola gaji dan slip digital untuk tim inti (master Employee + payroll).',
                    ],
                    [
                        'label' => 'Support Prioritas (1 Hari Kerja)',
                        'tip' => 'Antrian lebih cepat dari Starter, target respon 1 hari kerja, termasuk dampingan setup awal.',
                    ],
                ],
                'feature_keys' => [
                    self::FEATURE_PROJECTS,
                    self::FEATURE_SIMULASI,
                    self::FEATURE_BASIC_FINANCE,
                    self::FEATURE_FIXED_ASSETS,
                    self::FEATURE_NOTA_DINAS,
                    self::FEATURE_RECONCILIATION,
                    self::FEATURE_PAYROLL,
                ],
                'selectable' => true,
            ],
            [
                'key' => 'business',
                'name' => 'Business',
                'desc' => 'Untuk WO dengan banyak proyek dan tim lintas fungsi (hingga 3 pengguna).',
                'price' => '295.000',
                'unit' => null,
                'price_monthly' => 295_000,
                'price_semiannual' => 1_770_000,
                'price_annual' => 3_540_000,
                'price_biennial' => 7_080_000,
                'price_triennial' => 10_620_000,
                'price_quadrennial' => 14_160_000,
                'seat_limit' => 3,
                'vendor_limit' => null,
                'product_limit' => null,
                'order_limit' => null,
                'prospect_limit' => null,
                'simulasi_limit' => null,
                'payment_method_limit' => null,
                'fixed_asset_limit' => null,
                'piutang_limit' => null,
                'pembayaran_piutang_limit' => null,
                'category_limit' => 100,
                'data_pembayaran_limit' => null,
                'expense_limit' => null,
                'expense_ops_limit' => null,
                'pendapatan_lain_limit' => null,
                'pengeluaran_lain_limit' => null,
                'popular' => false,
                'cta' => 'Pilih Paket Business',
                'cta_class' => 'wf-btn-ghost',
                'check' => 'navy',
                'features' => [
                    [
                        'label' => 'Hingga 3 Pengguna',
                        'tip' => 'Maksimal 3 akun tim aktif — cocok untuk owner + AM/finance atau staf operasional.',
                    ],
                    [
                        'label' => 'Semua Fitur Professional',
                        'tip' => 'Termasuk simulasi wedding, draft kontrak kerja, fixed assets, rekonsiliasi & arus kas bersih, payroll, dan seluruh kapasitas Professional.',
                    ],
                    [
                        'label' => 'Crew Freelance + Link Undangan',
                        'tip' => 'Undang crew lewat link khusus. Mereka mengisi data sendiri, tanpa perlu akun pengguna tambahan.',
                    ],
                    [
                        'label' => 'Dokumen & SOP',
                        'tip' => 'Simpan dokumen resmi, SOP perusahaan, dan knowledge base tim dalam satu tempat.',
                    ],
                    [
                        'label' => 'Laporan Lanjutan',
                        'tip' => 'Kinerja AM: pantau target bulanan versus closing/pencapaian penjualan dari data Account Manager Target.',
                    ],
                    [
                        'label' => 'Onboarding & Training Tim',
                        'tip' => 'Dampingan go-live dan training agar seluruh tim siap memakai sistem.',
                    ],
                    [
                        'label' => 'Support WhatsApp Prioritas',
                        'tip' => 'Bantuan langsung via WhatsApp dengan prioritas di atas Professional, selama masa berlangganan aktif.',
                    ],
                ],
                'feature_keys' => [
                    self::FEATURE_PROJECTS,
                    self::FEATURE_SIMULASI,
                    self::FEATURE_BASIC_FINANCE,
                    self::FEATURE_FIXED_ASSETS,
                    self::FEATURE_NOTA_DINAS,
                    self::FEATURE_DOCUMENTS,
                    self::FEATURE_CREW_FREELANCE,
                    self::FEATURE_RECONCILIATION,
                    self::FEATURE_PAYROLL,
                    self::FEATURE_ADVANCED_REPORTS,
                ],
                'selectable' => true,
            ],
            [
                'key' => 'enterprise',
                'name' => 'Enterprise',
                'desc' => 'Hosting, domain, dan kustomisasi alur — minimal berlangganan 2 tahun.',
                'price' => '333.333',
                'unit' => null,
                'price_monthly' => 333_333,
                'price_semiannual' => 2_000_000,
                'price_annual' => 4_000_000,
                'price_biennial' => 8_000_000,
                'price_triennial' => 12_000_000,
                'price_quadrennial' => 16_000_000,
                'min_billing' => 'biennial',
                'pricing_note' => 'Minimal berlangganan 2 tahun',
                'seat_limit' => null,
                'vendor_limit' => null,
                'product_limit' => null,
                'order_limit' => null,
                'prospect_limit' => null,
                'simulasi_limit' => null,
                'payment_method_limit' => null,
                'fixed_asset_limit' => null,
                'piutang_limit' => null,
                'pembayaran_piutang_limit' => null,
                'category_limit' => null,
                'data_pembayaran_limit' => null,
                'expense_limit' => null,
                'expense_ops_limit' => null,
                'pendapatan_lain_limit' => null,
                'pengeluaran_lain_limit' => null,
                'popular' => false,
                'cta' => 'Hubungi Pengembang',
                'cta_class' => 'wf-btn-ghost',
                'cta_url' => 'https://wa.me/6281373183794?text='.rawurlencode('Halo, saya tertarik paket Enterprise WOFINS Rp 333.333/bulan, minimal 2 tahun (Rp 8 juta). Mohon info lebih lanjut.'),
                'check' => 'navy',
                'features' => [
                    [
                        'label' => 'Semua Fitur Starter, Professional & Business',
                        'tip' => 'Termasuk manajemen proyek, simulasi, invoice, keuangan, nota dinas, rekonsiliasi, payroll, dokumen, SOP, crew freelance, dan laporan lanjutan.',
                    ],
                    [
                        'label' => 'Kuota Unlimited',
                        'tip' => 'Pengguna, vendor, produk, proyek, prospek, simulasi, rekening, aset, piutang, dan transaksi tanpa batas.',
                    ],
                    [
                        'label' => 'Domain Gratis',
                        'tip' => 'Domain (.com / .id sesuai ketersediaan) termasuk selama masa berlangganan Enterprise aktif. Syarat: minimal 2 tahun.',
                    ],
                    [
                        'label' => 'Hosting Gratis',
                        'tip' => 'Hosting aplikasi WOFINS termasuk selama masa berlangganan aktif. Setup dibantu tim pengembang.',
                    ],
                    [
                        'label' => 'SSL & Backup Terpusat',
                        'tip' => 'Sertifikat SSL dan cadangan data berkala agar akses aman dan data bisnis terlindungi.',
                    ],
                    [
                        'label' => 'Integrasi & Kustomisasi Alur',
                        'tip' => 'Penyesuaian workflow, laporan, atau integrasi khusus sesuai proses internal WO Anda.',
                    ],
                    [
                        'label' => 'Onboarding, Training & Pendampingan',
                        'tip' => 'Setup go-live, training tim, dan pendampingan operasional sesuai skala perusahaan.',
                    ],
                    [
                        'label' => 'Support Langsung Pengembang',
                        'tip' => 'Prioritas bantuan dan diskusi kebutuhan langsung dengan tim pengembang WOFINS.',
                    ],
                ],
                'feature_keys' => [
                    self::FEATURE_PROJECTS,
                    self::FEATURE_SIMULASI,
                    self::FEATURE_BASIC_FINANCE,
                    self::FEATURE_FIXED_ASSETS,
                    self::FEATURE_NOTA_DINAS,
                    self::FEATURE_DOCUMENTS,
                    self::FEATURE_CREW_FREELANCE,
                    self::FEATURE_RECONCILIATION,
                    self::FEATURE_PAYROLL,
                    self::FEATURE_ADVANCED_REPORTS,
                    self::FEATURE_ROLE_MANAGEMENT,
                    self::FEATURE_MULTI_APPROVAL,
                ],
                'selectable' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(?string $key): ?array
    {
        if (! filled($key)) {
            return null;
        }

        $key = str_replace('-', '_', strtolower(trim($key)));

        if ($key === 'custom') {
            $key = 'enterprise';
        }

        foreach (static::all() as $plan) {
            if ($plan['key'] === $key) {
                return $plan;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function selectableKeys(): array
    {
        return collect(static::all())
            ->where('selectable', true)
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * Durasi checkout keranjang.
     *
     * @return list<string>
     */
    public static function billingKeys(): array
    {
        return ['monthly', 'annual', 'biennial', 'quadrennial'];
    }

    public static function billingLabel(string $billing): string
    {
        return match ($billing) {
            'semiannual' => '6 bulan',
            'annual' => '12 bulan',
            'biennial' => '24 bulan',
            'triennial' => '36 bulan',
            'quadrennial' => '48 bulan',
            default => '1 bulan',
        };
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return array{amount: int, label: string, period: string, months: int, monthly_equiv: int, savings: int}
     */
    public static function resolveBillingPrice(array $plan, string $billing): array
    {
        $rank = [
            'monthly' => 1,
            'semiannual' => 2,
            'annual' => 3,
            'biennial' => 4,
            'triennial' => 5,
            'quadrennial' => 6,
        ];
        $minBilling = (string) ($plan['min_billing'] ?? '');
        if ($minBilling !== '' && ($rank[$billing] ?? 0) < ($rank[$minBilling] ?? 0)) {
            $billing = $minBilling;
        }

        $monthly = (int) ($plan['price_monthly'] ?? 0);
        $semiannual = (int) ($plan['price_semiannual'] ?? ($monthly * 6));
        $annual = (int) ($plan['price_annual'] ?? ($monthly * 12));
        $biennial = (int) ($plan['price_biennial'] ?? ($monthly * 24));
        $triennial = (int) ($plan['price_triennial'] ?? ($monthly * 36));
        $quadrennial = (int) ($plan['price_quadrennial'] ?? ($monthly * 48));

        return match ($billing) {
            'semiannual' => [
                'amount' => $semiannual,
                'label' => '6 bulan',
                'period' => '6 bulan',
                'months' => 6,
                'monthly_equiv' => $semiannual > 0 ? (int) round($semiannual / 6) : $monthly,
                'savings' => 0,
            ],
            'annual' => [
                'amount' => $annual,
                'label' => '12 bulan',
                'period' => '12 bulan',
                'months' => 12,
                'monthly_equiv' => $annual > 0 ? (int) round($annual / 12) : $monthly,
                'savings' => 0,
            ],
            'biennial' => [
                'amount' => $biennial,
                'label' => '24 bulan',
                'period' => '24 bulan',
                'months' => 24,
                'monthly_equiv' => $biennial > 0 ? (int) round($biennial / 24) : $monthly,
                'savings' => 0,
            ],
            'triennial' => [
                'amount' => $triennial,
                'label' => '36 bulan',
                'period' => '36 bulan',
                'months' => 36,
                'monthly_equiv' => $triennial > 0 ? (int) round($triennial / 36) : $monthly,
                'savings' => 0,
            ],
            'quadrennial' => [
                'amount' => $quadrennial,
                'label' => '48 bulan',
                'period' => '48 bulan',
                'months' => 48,
                'monthly_equiv' => $quadrennial > 0 ? (int) round($quadrennial / 48) : $monthly,
                'savings' => 0,
            ],
            default => [
                'amount' => $monthly,
                'label' => '1 bulan',
                'period' => '1 bulan',
                'months' => 1,
                'monthly_equiv' => $monthly,
                'savings' => 0,
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        $options = [];

        foreach (static::all() as $plan) {
            if (! $plan['selectable']) {
                continue;
            }

            $options[$plan['key']] = static::optionLabel($plan['key']);
        }

        return $options;
    }

    /**
     * Opsi Select Filament ProspectApp.
     * Legacy Hastana hanya ikut jika record sudah memakai key itu.
     *
     * @return array<string, string>
     */
    public static function filamentOptions(?string $currentKey = null): array
    {
        $options = [
            ...static::selectOptions(),
            'lain_lain' => 'Lain-lain (Custom)',
        ];

        $legacy = static::legacyServiceLabels();

        if ($currentKey && isset($legacy[$currentKey])) {
            $options[$currentKey] = $legacy[$currentKey];
        }

        return $options;
    }

    /**
     * Opsi filter tabel (termasuk legacy agar data lama bisa difilter).
     *
     * @return array<string, string>
     */
    public static function filamentFilterOptions(): array
    {
        return [
            ...static::selectOptions(),
            'lain_lain' => 'Lain-lain (Custom)',
            ...static::legacyServiceLabels(),
        ];
    }

    /**
     * Matriks perbandingan untuk halaman Harga (sumber tunggal).
     *
     * @return list<list<string|bool>>
     */
    public static function compareRows(): array
    {
        return [
            ['Jumlah Pengguna', '1 (pemilik)', '1 (pemilik)', 'Hingga 3'],
            ['Invoice, proyek, kas, nota dinas', true, true, true],
            ['Simulasi Wedding', false, true, true],
            ['Draft Kontrak Kerja', false, true, true],
            ['Rekonsiliasi & Arus Kas', false, true, true],
            ['Payroll + Master Karyawan', false, true, true],
            ['Fixed Assets', false, true, true],
            ['Crew Freelance', false, false, true],
            ['Dokumen & SOP', false, false, true],
            ['Laporan Kinerja AM', false, false, true],
            ['Onboarding & Training Tim', false, false, true],
            ['Support', 'Jam kerja', 'Prioritas 1 hari', 'WhatsApp prioritas'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function legacyServiceLabels(): array
    {
        return [
            'hastana' => 'Paket Anggota Hastana (lama)',
            'non_hastana' => 'Paket Non Hastana (lama)',
        ];
    }

    /**
     * Opsi paket untuk Company form (tanpa legacy prospect).
     *
     * @return array<string, string>
     */
    public static function companyPlanOptions(): array
    {
        $options = [];

        foreach (static::all() as $plan) {
            if (! ($plan['selectable'] ?? false)) {
                continue;
            }

            $parts = [];
            foreach ([
                'seat_limit' => 'user',
                'vendor_limit' => 'vendor',
                'product_limit' => 'produk',
                'order_limit' => 'proyek',
                'prospect_limit' => 'prospek',
                'simulasi_limit' => 'simulasi',
                'payment_method_limit' => 'rekening',
                'fixed_asset_limit' => 'aset',
                'piutang_limit' => 'piutang',
                'pembayaran_piutang_limit' => 'bayar piutang',
                'data_pembayaran_limit' => 'pendapatan wedding',
                'expense_limit' => 'pengeluaran wedding',
                'expense_ops_limit' => 'ops',
                'pendapatan_lain_limit' => 'pendapatan lain',
                'pengeluaran_lain_limit' => 'pengeluaran lain',
            ] as $key => $label) {
                $limit = $plan[$key] ?? null;
                if ($limit === null) {
                    continue;
                }
                $parts[] = "{$limit} {$label}";
            }
            $options[$plan['key']] = $parts === []
                ? $plan['name']
                : $plan['name'].' ('.implode(', ', $parts).')';
        }

        return $options;
    }

    public static function optionLabel(?string $key): string
    {
        $plan = static::find($key);

        if ($plan) {
            if ($plan['price']) {
                return "Paket {$plan['name']} — Rp {$plan['price']} / bulan";
            }

            return "Paket {$plan['name']} — Hubungi Kami";
        }

        return match ($key) {
            'hastana' => 'Paket Anggota Hastana (lama)',
            'non_hastana' => 'Paket Non Hastana (lama)',
            'lain_lain' => 'Lain-lain (Custom)',
            default => filled($key) ? (string) $key : '—',
        };
    }

    public static function shortLabel(?string $key): string
    {
        $plan = static::find($key);

        if ($plan) {
            return 'Paket '.$plan['name'];
        }

        return match ($key) {
            'hastana' => 'Paket Hastana',
            'non_hastana' => 'Paket Non Hastana',
            'lain_lain' => 'Lain-lain',
            default => filled($key) ? ucfirst((string) $key) : '—',
        };
    }

    public static function priceDisplay(?string $key): ?string
    {
        $plan = static::find($key);

        if (! $plan) {
            return null;
        }

        if ($plan['price']) {
            return 'Rp '.$plan['price'].'/bulan';
        }

        return 'Hubungi Kami';
    }

    public static function annualAmount(?string $key): ?int
    {
        $plan = static::find($key);

        if ($plan && $plan['price_annual'] !== null) {
            return (int) $plan['price_annual'];
        }

        return match ($key) {
            'hastana' => 8_500_000,
            'non_hastana' => 10_000_000,
            default => null,
        };
    }

    /**
     * @return array<string, int>
     */
    public static function priceMap(): array
    {
        $map = [];

        foreach (static::all() as $plan) {
            if ($plan['price_annual'] !== null) {
                $map[$plan['key']] = $plan['price_annual'];
            }
        }

        $map['hastana'] = 8_500_000;
        $map['non_hastana'] = 10_000_000;

        return $map;
    }

    /**
     * Batas kuota resource: users | vendors | products | orders | prospects | simulasi |
     * categories | payment_methods | fixed_assets | piutangs | pembayaran_piutangs.
     * null = tidak ditetapkan di konfigurasi paket.
     */
    public static function limit(?string $planKey, string $resource): ?int
    {
        $plan = static::find($planKey) ?? static::find('starter');
        $column = match ($resource) {
            'users', 'seats' => 'seat_limit',
            'vendors' => 'vendor_limit',
            'products' => 'product_limit',
            'orders' => 'order_limit',
            'prospects' => 'prospect_limit',
            'simulasi', 'simulations' => 'simulasi_limit',
            'payment_methods', 'rekening' => 'payment_method_limit',
            'fixed_assets', 'aset' => 'fixed_asset_limit',
            'piutangs', 'piutang' => 'piutang_limit',
            'pembayaran_piutangs', 'pembayaran_piutang' => 'pembayaran_piutang_limit',
            'categories', 'kategori' => 'category_limit',
            'data_pembayarans', 'pendapatan_wedding' => 'data_pembayaran_limit',
            'expenses', 'pengeluaran_wedding' => 'expense_limit',
            'expense_ops', 'pengeluaran_ops' => 'expense_ops_limit',
            'pendapatan_lains', 'pendapatan_lain' => 'pendapatan_lain_limit',
            'pengeluaran_lains', 'pengeluaran_lain' => 'pengeluaran_lain_limit',
            default => null,
        };

        if (! $column || ! $plan) {
            return 0;
        }

        $value = $plan[$column] ?? null;

        return $value === null ? null : (int) $value;
    }

    /**
     * Batas kursi paket (null = tidak ditetapkan di konfigurasi paket).
     */
    public static function seatLimit(?string $key): ?int
    {
        return static::limit($key, 'users');
    }

    /**
     * Normalisasi daftar fitur kartu harga (label + tip opsional).
     *
     * @param  array<string, mixed>  $plan
     * @return list<array{label: string, tip: ?string}>
     */
    public static function featureItems(array $plan): array
    {
        $items = [];

        foreach ($plan['features'] ?? [] as $feature) {
            if (is_string($feature)) {
                $items[] = ['label' => $feature, 'tip' => null];

                continue;
            }

            if (! is_array($feature)) {
                continue;
            }

            $label = trim((string) ($feature['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $tip = $feature['tip'] ?? null;
            $tip = is_string($tip) && trim($tip) !== '' ? trim($tip) : null;

            $items[] = ['label' => $label, 'tip' => $tip];
        }

        return $items;
    }

    /**
     * Apakah paket mengizinkan fitur tertentu.
     */
    public static function allows(?string $planKey, string $feature): bool
    {
        $plan = static::find($planKey);

        if (! $plan) {
            // Tanpa paket dikenal: hanya fitur starter dasar
            $plan = static::find('starter');
        }

        $keys = $plan['feature_keys'] ?? [];

        return in_array($feature, $keys, true);
    }

    /**
     * Normalisasi key paket (prospect / query).
     */
    public static function normalizeKey(?string $key): ?string
    {
        if (! filled($key)) {
            return null;
        }

        $key = str_replace('-', '_', strtolower(trim($key)));

        return match ($key) {
            'hastana', 'non_hastana', 'nonhastana' => 'professional', // legacy → Professional
            'starter', 'professional', 'business', 'enterprise' => $key,
            'custom' => 'enterprise',
            'lain_lain' => null,
            default => static::find($key) ? $key : null,
        };
    }
}
