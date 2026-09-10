@extends('layouts.app')

@section('title', 'Harga Paket — WOFINS')

@push('styles')
<style>
        :root {
            --wf-navy: #0b1f3a;
            --wf-navy-deep: #071526;
            --wf-gold: #c9a227;
            --wf-gold-soft: #e8d48b;
            --wf-cream: #f7f4ee;
            --wf-ink: #1a2332;
            --wf-muted: #5c6675;
            --wf-line: #e6e2d9;
            --wf-white: #ffffff;
        }

        .wf-page {
            font-family: 'Poppins', system-ui, sans-serif;
            color: var(--wf-ink);
            background: var(--wf-white);
        }

        [x-cloak] {
            display: none !important;
        }

        .wf-page h1,
        .wf-page h2,
        .wf-page h3 {
            letter-spacing: -0.02em;
        }

        .wf-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--wf-line);
        }

        .wf-btn-navy {
            background: var(--wf-navy);
            color: #fff;
            border-radius: 999px;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease;
        }

        .wf-btn-navy:hover {
            background: var(--wf-navy-deep);
            transform: translateY(-1px);
        }

        .wf-btn-ghost {
            border: 1.5px solid var(--wf-navy);
            color: var(--wf-navy);
            border-radius: 999px;
            font-weight: 700;
            background: #fff;
            transition: background .2s ease;
        }

        .wf-btn-ghost:hover {
            background: var(--wf-cream);
        }

        .wf-btn-gold {
            background: var(--wf-gold);
            color: var(--wf-navy-deep);
            border-radius: 999px;
            font-weight: 800;
            transition: filter .2s ease, transform .2s ease;
        }

        .wf-btn-gold:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .wf-btn-outline-light {
            border: 1.5px solid rgba(255, 255, 255, 0.85);
            color: #fff;
            border-radius: 999px;
            font-weight: 700;
            transition: background .2s ease;
        }

        .wf-btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .wf-price-card {
            background: #fff;
            border: 1px solid var(--wf-line);
            border-radius: 1.5rem;
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            z-index: 1;
            overflow: visible;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .wf-price-card:hover {
            z-index: 5;
            transform: translateY(-3px);
            box-shadow: 0 18px 40px -24px rgba(11, 31, 58, 0.35);
        }

        .wf-price-card:has(.wf-feature-tip:hover),
        .wf-price-card:has(.wf-feature-tip:focus-visible),
        .wf-price-card:has(.wf-feature-tip.is-open) {
            z-index: 40;
        }

        .wf-price-card.is-popular {
            border: 2px solid var(--wf-gold);
            box-shadow: 0 20px 44px -20px rgba(201, 162, 39, 0.45);
        }

        .wf-popular-badge {
            position: absolute;
            top: -0.85rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--wf-gold);
            color: var(--wf-navy-deep);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        .wf-save-pill {
            display: inline-flex;
            align-items: center;
            background: rgba(201, 162, 39, 0.14);
            color: #9a7a12;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
        }

        .wf-billing-select-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .wf-billing-dd {
            position: relative;
            width: min(100%, 17.5rem);
        }

        .wf-billing-trigger {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 1.5px solid var(--wf-line);
            background: #fff;
            border-radius: 0.75rem;
            padding: 0.72rem 0.95rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--wf-navy);
            cursor: pointer;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .wf-billing-trigger:hover {
            border-color: rgba(11, 31, 58, 0.35);
        }

        .wf-billing-trigger.is-open,
        .wf-billing-trigger:focus {
            outline: none;
            border-color: var(--wf-navy);
            box-shadow: 0 0 0 3px rgba(11, 31, 58, 0.12);
        }

        .wf-billing-trigger i {
            font-size: 0.7rem;
            color: var(--wf-muted);
        }

        .wf-billing-menu {
            position: absolute;
            z-index: 40;
            top: calc(100% + 0.4rem);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--wf-line);
            border-radius: 0.75rem;
            box-shadow: 0 16px 40px -20px rgba(11, 31, 58, 0.45);
            padding: 0.35rem;
            overflow: hidden;
        }

        .wf-billing-option {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            border: 0;
            background: transparent;
            border-radius: 0.5rem;
            padding: 0.65rem 0.75rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--wf-navy);
            text-align: left;
            cursor: pointer;
        }

        .wf-billing-option:hover,
        .wf-billing-option.is-active {
            background: rgba(11, 31, 58, 0.06);
        }

        .wf-best-badge {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.22rem 0.55rem;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(201, 162, 39, 0.18);
            color: #8a6d12;
            white-space: nowrap;
        }

        .wf-check {
            width: 1.2rem;
            height: 1.2rem;
            border-radius: 999px;
            background: rgba(201, 162, 39, 0.15);
            color: var(--wf-gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.65rem;
        }

        .wf-check.navy {
            background: rgba(11, 31, 58, 0.08);
            color: var(--wf-navy);
        }

        .wf-feature-tip {
            position: relative;
            display: inline;
            border-bottom: 1px dashed rgba(11, 31, 58, 0.35);
            cursor: help;
            outline: none;
        }

        .wf-feature-tip:hover,
        .wf-feature-tip:focus-visible {
            border-bottom-color: var(--wf-gold);
            color: var(--wf-navy);
        }

        .wf-feature-tip__bubble {
            position: absolute;
            left: 0;
            top: calc(100% + 0.65rem);
            z-index: 50;
            width: min(17.5rem, 70vw);
            padding: 0.75rem 0.9rem;
            border-radius: 0.65rem;
            background: var(--wf-navy);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 400;
            line-height: 1.45;
            letter-spacing: 0;
            text-transform: none;
            box-shadow: 0 14px 28px -16px rgba(7, 21, 38, 0.55);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(4px);
            transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
        }

        .wf-feature-tip__bubble::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: -6px;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid var(--wf-navy);
        }

        .wf-feature-tip:hover .wf-feature-tip__bubble,
        .wf-feature-tip:focus-visible .wf-feature-tip__bubble,
        .wf-feature-tip.is-open .wf-feature-tip__bubble {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .wf-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--wf-line);
            border-radius: 1.25rem;
            background: #fff;
        }

        .wf-compare th,
        .wf-compare td {
            padding: 0.95rem 1.1rem;
            text-align: left;
            border-bottom: 1px solid var(--wf-line);
            white-space: nowrap;
        }

        .wf-compare th {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--wf-navy);
            background: #faf9f6;
        }

        .wf-compare th.col-pro {
            color: #9a7a12;
            background: rgba(201, 162, 39, 0.12);
        }

        .wf-compare td:first-child {
            font-weight: 600;
            color: var(--wf-ink);
            white-space: normal;
            min-width: 8.5rem;
            max-width: 11rem;
            word-break: break-word;
        }

        .wf-compare tr:last-child td {
            border-bottom: 0;
        }

        .wf-faq-item {
            border: 1px solid var(--wf-line);
            border-radius: 0.9rem;
            background: #fff;
            overflow: hidden;
        }

        .wf-cta-box {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #071526 0%, #0b1f3a 55%, #14335a 100%);
            border-radius: 1.5rem;
            color: #fff;
        }

        .wf-cta-shapes {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .wf-footer {
            background: var(--wf-navy);
            color: rgba(255, 255, 255, 0.78);
        }

        .wf-footer a:hover {
            color: #fff;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PricingPlans;

    $pendingOrder = (auth()->check() && class_exists(\App\Models\SubscriptionOrder::class))
        ? \App\Models\SubscriptionOrder::pendingForUser(auth()->user())
        : null;

    $plans = collect(PricingPlans::all())->map(function (array $plan) {
        $monthly = (int) ($plan['price_monthly'] ?? 0);
        $annual = (int) ($plan['price_annual'] ?? 0);
        $biennial = (int) ($plan['price_biennial'] ?? ($monthly * 24));
        $quadrennial = (int) ($plan['price_quadrennial'] ?? ($monthly * 48));
        $annualPerMonth = $annual > 0 ? (int) round($annual / 12) : 0;
        $biennialPerMonth = $biennial > 0 ? (int) round($biennial / 24) : $monthly;
        $quadrennialPerMonth = $quadrennial > 0 ? (int) round($quadrennial / 48) : $monthly;

        $formatIdr = static function (int $amount): string {
            if ($amount <= 0) {
                return '';
            }

            return number_format($amount, 0, ',', '.');
        };

        $plan['monthly_display'] = $formatIdr($monthly);
        $plan['annual_monthly_display'] = $formatIdr($annualPerMonth);
        $plan['annual_total_display'] = $formatIdr($annual);
        $plan['biennial_monthly_display'] = $formatIdr($biennialPerMonth);
        $plan['biennial_total_display'] = $formatIdr($biennial);
        $plan['quadrennial_monthly_display'] = $formatIdr($quadrennialPerMonth);
        $plan['quadrennial_total_display'] = $formatIdr($quadrennial);
        $plan['monthly_yearly_total_display'] = $formatIdr($monthly * 12);
        $plan['has_price'] = $monthly > 0 && $annual > 0;

        return $plan;
    })->all();

    $compareRows = PricingPlans::compareRows();

    $faqs = [
        ['Apakah ada biaya instalasi?', 'Tidak. Semua paket WOFINS tanpa biaya instalasi. Anda hanya membayar biaya berlangganan sesuai paket yang dipilih.'],
        ['Apa yang termasuk domain gratis?', 'Domain dan hosting gratis hanya di paket Enterprise, dengan syarat berlangganan minimal 2 tahun. Setup dibantu tim pengembang saat onboarding.'],
        ['Bisakah saya upgrade paket nanti?', 'Bisa. Anda dapat upgrade kapan saja; selisih biaya akan disesuaikan dengan sisa masa aktif langganan.'],
        ['Apakah ada paket Enterprise?', 'Ya. Paket Enterprise Rp 333.333/bulan dengan minimal berlangganan 2 tahun (Rp 8 juta). Termasuk fitur Business, kuota lebih longgar, domain, hosting, dan kustomisasi alur. Hubungi pengembang untuk aktivasi.'],
        ['Bagaimana dengan kategori?', 'Master kategori dikelola admin platform (super admin). Tim WO tetap bisa memakai kategori yang sudah disediakan.'],
        ['Apa itu crew freelance?', 'Data crew freelance milik company (bukan akun pengguna). Fitur ini hanya di paket Business: tambah crew dan bagikan link undangan agar crew mengisi sendiri tanpa makan kuota pengguna.'],
        ['Apakah data saya aman?', 'Ya. Akses berbasis peran, riwayat aktivitas, approval, backup terpusat, dan audit trail membantu menjaga keamanan data bisnis Anda.'],
        ['Apakah ada masa uji coba?', 'Kami sediakan demo gratis dan konsultasi kebutuhan agar Anda bisa menilai kesesuaian WOFINS sebelum berlangganan.'],
        ['Bagaimana proses onboarding?', 'Setelah paket dipilih, tim kami membantu setup perusahaan, pengguna, dan alur kerja inti agar tim Anda siap memakai sistem.'],
    ];
@endphp

    <div class="wf-page" x-data="{
        openFaq: 0,
        billing: 'quadrennial',
        billingOpen: false,
        openTip: null,
        billingLabels: {
            monthly: '1 bulan',
            annual: '12 bulan',
            biennial: '24 bulan',
            quadrennial: '48 bulan',
        },
    }">
        @include('front.partials.wf-nav')

        {{-- Intro --}}
        <section class="pt-14 pb-10 bg-gradient-to-b from-white to-[var(--wf-cream)]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-[var(--wf-gold)] mb-3">Harga</p>
                <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-[var(--wf-navy)] leading-tight">
                    Pilih Paket yang Sesuai dengan Kebutuhan Wedding Organizer Anda
                </h1>
                <p class="mt-4 text-[var(--wf-muted)] max-w-2xl mx-auto">
                    Paket WOFINS dirancang fleksibel — dari WO yang baru merapikan operasional hingga tim Business, plus paket Enterprise sesuai kebutuhan.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-sm font-semibold text-[var(--wf-navy)]">
                    @foreach (['Tanpa biaya instalasi', 'Update & support gratis', 'Aman & terpercaya'] as $benefit)
                        <span class="inline-flex items-center gap-2">
                            <span class="wf-check"><i class="fa-solid fa-check"></i></span>
                            {{ $benefit }}
                        </span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Pricing cards --}}
        <section class="pb-14 pt-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if ($pendingOrder)
                    <div class="max-w-5xl mx-auto mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-amber-900">Pesanan menunggu tinjauan</p>
                            <p class="mt-1 text-sm text-amber-800/90">
                                Kode <strong>{{ $pendingOrder->order_code }}</strong> ({{ $pendingOrder->plan_name }} · {{ $pendingOrder->billing_label }})
                                masih diproses. Selesaikan dulu — disetujui atau ditolak — sebelum memesan paket lain.
                            </p>
                        </div>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('pesanan-saya.show') ? route('pesanan-saya.show', $pendingOrder->order_code) : route('kontak') }}"
                           class="wf-btn-navy shrink-0 inline-flex items-center justify-center px-5 py-2.5 text-sm">
                            Lihat pesanan
                        </a>
                    </div>
                @endif

                <div class="wf-billing-select-wrap">
                    <div class="wf-billing-dd"
                         @click.outside="billingOpen = false"
                         @keydown.escape.window="billingOpen = false">
                        <button type="button"
                                class="wf-billing-trigger"
                                :class="{ 'is-open': billingOpen }"
                                @click="billingOpen = !billingOpen"
                                :aria-expanded="billingOpen.toString()"
                                aria-haspopup="listbox"
                                aria-label="Pilih durasi berlangganan">
                            <span x-text="billingLabels[billing]"></span>
                            <i class="fa-solid" :class="billingOpen ? 'fa-chevron-up' : 'fa-chevron-down'" aria-hidden="true"></i>
                        </button>
                        <div class="wf-billing-menu"
                             x-show="billingOpen"
                             x-cloak
                             x-transition.opacity.duration.120ms
                             role="listbox"
                             aria-label="Durasi berlangganan">
                            <template x-for="opt in [
                                { value: 'monthly', label: '1 bulan' },
                                { value: 'annual', label: '12 bulan' },
                                { value: 'biennial', label: '24 bulan' },
                                { value: 'quadrennial', label: '48 bulan' },
                            ]" :key="opt.value">
                                <button type="button"
                                        class="wf-billing-option"
                                        role="option"
                                        :class="{ 'is-active': billing === opt.value }"
                                        :aria-selected="(billing === opt.value).toString()"
                                        @click="billing = opt.value; billingOpen = false">
                                    <span x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu paket: terpusat seperti semula, sedikit lebih lebar --}}
            <div class="w-full max-w-[92rem] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 pt-1">
                    @foreach ($plans as $plan)
                        <div class="wf-price-card {{ $plan['popular'] ? 'is-popular' : '' }}">
                            @if ($plan['popular'])
                                <span class="wf-popular-badge">PALING POPULER</span>
                            @endif

                            <div class="mb-5 {{ $plan['popular'] ? 'pt-2' : '' }}">
                                <h3 class="text-xl font-bold text-[var(--wf-navy)]">{{ $plan['name'] }}</h3>
                                <p class="mt-1 text-sm text-[var(--wf-muted)]">{{ $plan['desc'] }}</p>
                            </div>

                            <div class="mb-5">
                                @if ($plan['has_price'] && ($plan['min_billing'] ?? '') === 'biennial')
                                    <div class="flex flex-wrap items-end gap-1 min-w-0" x-show="billing !== 'quadrennial'" x-cloak>
                                        <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1">Rp</span>
                                        <span class="text-4xl font-extrabold text-[var(--wf-navy)] leading-none break-all">{{ $plan['biennial_monthly_display'] }}</span>
                                        <span class="text-sm text-[var(--wf-muted)] mb-1">/ bulan</span>
                                    </div>
                                    <div class="mt-2 text-xs text-[var(--wf-muted)] break-words" x-show="billing !== 'quadrennial'" x-cloak>
                                        Dibayar 24 bulan Rp {{ $plan['biennial_total_display'] }}
                                    </div>

                                    <div class="flex flex-wrap items-end gap-1 min-w-0" x-show="billing === 'quadrennial'" x-cloak>
                                        <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1">Rp</span>
                                        <span class="text-4xl font-extrabold text-[var(--wf-navy)] leading-none break-all">{{ $plan['quadrennial_monthly_display'] }}</span>
                                        <span class="text-sm text-[var(--wf-muted)] mb-1">/ bulan</span>
                                    </div>
                                    <div class="mt-2 text-xs text-[var(--wf-muted)] break-words" x-show="billing === 'quadrennial'" x-cloak>
                                        Dibayar 48 bulan Rp {{ $plan['quadrennial_total_display'] }}
                                    </div>
                                    <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--wf-navy)] break-words">
                                        <i class="fa-solid fa-lock text-[10px] shrink-0" aria-hidden="true"></i>
                                        <span>Terkunci: minimal 2 tahun</span>
                                    </div>
                                @elseif ($plan['has_price'])
                                    {{-- 1 bulan --}}
                                    <div class="flex flex-wrap items-end gap-1 min-w-0" x-show="billing === 'monthly'" x-cloak>
                                        @if (! empty($plan['price_prefix']))
                                            <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1 w-full sm:w-auto">{{ $plan['price_prefix'] }}</span>
                                        @endif
                                        <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1">Rp</span>
                                        <span class="text-4xl font-extrabold text-[var(--wf-navy)] leading-none break-all">{{ $plan['monthly_display'] }}</span>
                                        <span class="text-sm text-[var(--wf-muted)] mb-1">/ bulan</span>
                                    </div>
                                    <div class="mt-2 text-xs text-[var(--wf-muted)] break-words" x-show="billing === 'monthly'" x-cloak>
                                        Ditagih bulanan
                                    </div>

                                    {{-- 12 bulan --}}
                                    <div class="flex flex-wrap items-end gap-1 min-w-0" x-show="billing === 'annual'" x-cloak>
                                        @if (! empty($plan['price_prefix']))
                                            <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1 w-full sm:w-auto">{{ $plan['price_prefix'] }}</span>
                                        @endif
                                        <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1">Rp</span>
                                        <span class="text-4xl font-extrabold text-[var(--wf-navy)] leading-none break-all">{{ $plan['annual_monthly_display'] }}</span>
                                        <span class="text-sm text-[var(--wf-muted)] mb-1">/ bulan</span>
                                    </div>
                                    <div class="mt-2 text-xs text-[var(--wf-muted)] break-words" x-show="billing === 'annual'" x-cloak>
                                        Dibayar 12 bulan Rp {{ $plan['annual_total_display'] }}
                                    </div>

                                    {{-- 24 bulan --}}
                                    <div class="flex flex-wrap items-end gap-1 min-w-0" x-show="billing === 'biennial'" x-cloak>
                                        @if (! empty($plan['price_prefix']))
                                            <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1 w-full sm:w-auto">{{ $plan['price_prefix'] }}</span>
                                        @endif
                                        <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1">Rp</span>
                                        <span class="text-4xl font-extrabold text-[var(--wf-navy)] leading-none break-all">{{ $plan['biennial_monthly_display'] }}</span>
                                        <span class="text-sm text-[var(--wf-muted)] mb-1">/ bulan</span>
                                    </div>
                                    <div class="mt-2 text-xs text-[var(--wf-muted)] break-words" x-show="billing === 'biennial'" x-cloak>
                                        Dibayar 24 bulan Rp {{ $plan['biennial_total_display'] }}
                                    </div>

                                    {{-- 48 bulan --}}
                                    <div class="flex flex-wrap items-end gap-1 min-w-0" x-show="billing === 'quadrennial'" x-cloak>
                                        @if (! empty($plan['price_prefix']))
                                            <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1 w-full sm:w-auto">{{ $plan['price_prefix'] }}</span>
                                        @endif
                                        <span class="text-sm font-semibold text-[var(--wf-muted)] mb-1">Rp</span>
                                        <span class="text-4xl font-extrabold text-[var(--wf-navy)] leading-none break-all">{{ $plan['quadrennial_monthly_display'] }}</span>
                                        <span class="text-sm text-[var(--wf-muted)] mb-1">/ bulan</span>
                                    </div>
                                    <div class="mt-2 text-xs text-[var(--wf-muted)] break-words" x-show="billing === 'quadrennial'" x-cloak>
                                        Dibayar 48 bulan Rp {{ $plan['quadrennial_total_display'] }}
                                    </div>
                                    @if (! empty($plan['pricing_note']))
                                        <div class="mt-1 text-xs text-[var(--wf-muted)] break-words">{{ $plan['pricing_note'] }}</div>
                                    @endif
                                @else
                                    <p class="text-3xl font-extrabold text-[var(--wf-navy)] leading-tight">Enterprise</p>
                                    <p class="mt-2 text-xs text-[var(--wf-muted)]">Harga & scope hubungi pengembang</p>
                                @endif
                            </div>

                            <ul class="space-y-2.5 mb-8 flex-1">
                                @foreach (\App\Support\PricingPlans::featureItems($plan) as $feature)
                                    <li class="flex items-start gap-2.5 text-sm text-[var(--wf-ink)]">
                                        <span class="wf-check {{ $plan['check'] }}"><i class="fa-solid fa-check"></i></span>
                                        @if ($feature['tip'])
                                            <span class="wf-feature-tip"
                                                  tabindex="0"
                                                  role="button"
                                                  aria-label="{{ $feature['label'] }}: {{ $feature['tip'] }}"
                                                  @click.prevent="openTip = openTip === '{{ $plan['key'] }}-{{ $loop->index }}' ? null : '{{ $plan['key'] }}-{{ $loop->index }}'"
                                                  @keydown.escape.window="openTip = null"
                                                  :class="{ 'is-open': openTip === '{{ $plan['key'] }}-{{ $loop->index }}' }">
                                                {{ $feature['label'] }}
                                                <span class="wf-feature-tip__bubble" role="tooltip">{{ $feature['tip'] }}</span>
                                            </span>
                                        @else
                                            <span>{{ $feature['label'] }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if ($pendingOrder && ($plan['selectable'] ?? true))
                                <span class="{{ $plan['cta_class'] }} w-full inline-flex items-center justify-center px-4 py-3 text-sm text-center opacity-50 cursor-not-allowed pointer-events-none"
                                      aria-disabled="true"
                                      title="Selesaikan pesanan {{ $pendingOrder->order_code }} terlebih dahulu">
                                    Menunggu tinjauan pesanan
                                </span>
                            @elseif (! empty($plan['cta_url']) || ! ($plan['selectable'] ?? true))
                                <a href="{{ $plan['cta_url'] ?? 'https://wa.me/6281373183794?text='.rawurlencode('Halo, saya ingin konsultasi paket Enterprise WOFINS.') }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="{{ $plan['cta_class'] }} w-full inline-flex items-center justify-center px-4 py-3 text-sm text-center">
                                    {{ $plan['cta'] }}
                                </a>
                            @elseif (\Illuminate\Support\Facades\Route::has('keranjang'))
                                <a :href="'{{ route('keranjang') }}?paket={{ $plan['key'] }}&billing=' + billing"
                                   class="{{ $plan['cta_class'] }} w-full inline-flex items-center justify-center px-4 py-3 text-sm text-center">
                                    {{ $plan['cta'] }}
                                </a>
                            @else
                                <a :href="'{{ route('kontak') }}?paket={{ $plan['key'] }}&billing=' + billing"
                                   class="{{ $plan['cta_class'] }} w-full inline-flex items-center justify-center px-4 py-3 text-sm text-center">
                                    {{ $plan['cta'] }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mt-8 rounded-2xl bg-[#eef1f5] border border-[var(--wf-line)] px-5 py-4 flex flex-col sm:flex-row items-center justify-center gap-3 text-sm text-[var(--wf-navy)]">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white text-[var(--wf-gold)] shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <p class="text-center sm:text-left font-medium">
                        Data bisnis Anda dilindungi dengan akses berbasis peran, backup terpusat, dan audit trail.
                    </p>
                </div>
            </div>
        </section>

        {{-- Comparison --}}
        <section class="py-14 bg-[var(--wf-cream)]/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[var(--wf-navy)] text-center mb-8">Perbandingan Fitur Paket</h2>
                <div class="wf-table-wrap">
                    <table class="wf-compare w-full text-sm">
                        <thead>
                            <tr>
                                <th>Fitur Utama</th>
                                <th>Starter</th>
                                <th class="col-pro">Professional</th>
                                <th>Business</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compareRows as $row)
                                <tr>
                                    @foreach ($row as $i => $cell)
                                        <td>
                                            @if ($i === 0)
                                                {{ $cell }}
                                            @elseif ($cell === true)
                                                <i class="fa-solid fa-check text-emerald-500"></i>
                                            @elseif ($cell === false)
                                                <span class="text-[var(--wf-muted)]">—</span>
                                            @else
                                                <span class="{{ $i === 2 ? 'font-semibold text-[#9a7a12]' : 'text-[var(--wf-muted)]' }}">{{ $cell }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- FAQ + CTA --}}
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-8 items-start">
                    <div>
                        <h2 class="text-3xl font-bold text-[var(--wf-navy)] mb-6">FAQ</h2>
                        <div class="space-y-3">
                            @foreach ($faqs as $i => $faq)
                                <div class="wf-faq-item">
                                    <button type="button"
                                            class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left font-bold text-[var(--wf-navy)]"
                                            @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}">
                                        <span>{{ $faq[0] }}</span>
                                        <i class="fa-solid fa-chevron-down text-xs text-[var(--wf-muted)] transition-transform"
                                           :class="openFaq === {{ $i }} && 'rotate-180'"></i>
                                    </button>
                                    <div x-show="openFaq === {{ $i }}" x-cloak class="px-5 pb-4 text-sm text-[var(--wf-muted)] leading-relaxed">
                                        {{ $faq[1] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('home') }}#faq" class="inline-flex items-center gap-2 mt-5 text-sm font-bold text-[var(--wf-gold)] hover:brightness-90">
                            Lihat semua pertanyaan
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                    <div class="wf-cta-box p-8 sm:p-10 min-h-[22rem]">
                        <div class="wf-cta-shapes" aria-hidden="true">
                            <span class="absolute -right-10 -top-10 w-56 h-56 rounded-full bg-[rgba(201,162,39,0.16)]"></span>
                            <span class="absolute right-10 bottom-0 w-40 h-40 rounded-full bg-[rgba(70,140,200,0.18)] blur-[1px]"></span>
                            <span class="absolute right-[30%] top-[35%] w-24 h-24 rounded-full border border-[rgba(232,212,139,0.35)]"></span>
                            <span class="absolute right-[18%] top-[22%] w-3 h-3 rounded-full bg-[rgba(232,212,139,0.55)]"></span>
                            <span class="absolute right-[40%] bottom-[22%] w-2 h-2 rounded-full bg-white/40"></span>
                            <svg class="absolute right-0 top-0 h-full w-1/2 opacity-50" viewBox="0 0 280 320" fill="none" preserveAspectRatio="xMaxYMid slice">
                                <path d="M180 40c40 24 72 70 78 120s-16 96-56 124" stroke="rgba(232,212,139,0.4)" stroke-width="1.5"/>
                                <circle cx="220" cy="100" r="36" fill="rgba(201,162,39,0.1)" stroke="rgba(232,212,139,0.3)"/>
                                <circle cx="200" cy="220" r="52" fill="rgba(56,120,180,0.1)" stroke="rgba(140,190,230,0.25)"/>
                                <rect x="150" y="130" width="44" height="44" rx="12" transform="rotate(16 172 152)" stroke="rgba(255,255,255,0.18)" fill="rgba(255,255,255,0.04)"/>
                            </svg>
                        </div>

                        <div class="relative z-10 max-w-md">
                            <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center text-[var(--wf-gold-soft)] mb-5">
                                <i class="fa-regular fa-calendar text-xl"></i>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-bold leading-tight">
                                Butuh Paket yang Sesuai dengan Bisnis Anda?
                            </h3>
                            <p class="mt-3 text-white/75 text-sm leading-relaxed">
                                Jadwalkan demo gratis. Kami bantu petakan kebutuhan WO Anda dan rekomendasikan paket yang paling pas.
                            </p>
                            <div class="mt-7 flex flex-col sm:flex-row gap-3">
                                <a href="{{ route('kontak') }}" class="wf-btn-gold inline-flex items-center justify-center px-5 py-3 text-sm">
                                    Jadwalkan Demo Gratis
                                </a>
                                <a href="{{ route('kontak') }}" class="wf-btn-outline-light inline-flex items-center justify-center px-5 py-3 text-sm">
                                    Konsultasi Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('front.partials.wf-footer')

        <a href="https://wa.me/6281373183794?text={{ urlencode('Halo, saya ingin tanya paket harga WOFINS.') }}"
           class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white shadow-lg flex items-center justify-center hover:scale-105 transition"
           aria-label="WhatsApp">
            <i class="fa-brands fa-whatsapp text-2xl"></i>
        </a>
    </div>
@endsection
