@extends('layouts.app')

@section('title', 'Lupa Password — WOFINS')

@push('styles')
@include('front.partials.wf-front-base-styles')
<style>
        .wf-auth {
            font-family: 'Poppins', system-ui, sans-serif;
            color: var(--wf-ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse 80% 50% at 10% 20%, rgba(201, 162, 39, 0.12), transparent 55%),
                radial-gradient(ellipse 60% 40% at 90% 80%, rgba(11, 31, 58, 0.08), transparent 50%),
                linear-gradient(180deg, #fff 0%, var(--wf-cream) 100%);
        }

        .wf-auth > header,
        .wf-auth > .wf-auth-main {
            position: relative;
            z-index: 1;
        }

        .wf-auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            padding-top: 2.5rem;
            padding-bottom: 3.5rem;
        }

        @media (min-width: 640px) {
            .wf-auth-main {
                padding-top: 3.5rem;
                padding-bottom: 4rem;
            }
        }

        .wf-auth-panel {
            position: relative;
            overflow: hidden;
        }

        .wf-auth-panel .wf-deco__blob--a {
            opacity: 0.55;
            background: radial-gradient(circle at 30% 30%, rgba(201, 162, 39, 0.5), transparent 70%);
        }

        .wf-auth-panel .wf-deco__ring--a,
        .wf-auth-panel .wf-deco__ring--b {
            border-color: rgba(255, 255, 255, 0.18);
        }

        .wf-auth-panel .wf-deco__sq--a {
            border-color: rgba(201, 162, 39, 0.45);
        }

        .wf-auth-panel .wf-deco__dot--a {
            background: var(--wf-gold-soft);
            opacity: 0.7;
        }
    </style>
@endpush

@section('content')
    <div class="wf-auth">
        @include('front.partials.wf-deco-shapes')

        <header class="shrink-0 border-b border-[var(--wf-line)]/70 bg-white/70 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center min-w-0">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-2xl font-bold text-[var(--wf-navy)] tracking-wide">
                    WOFINS
                </a>
            </div>
        </header>

        <div class="wf-auth-main">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 min-w-0">
            <div class="grid lg:grid-cols-2 rounded-3xl overflow-hidden border border-[var(--wf-line)] bg-white shadow-[0_24px_60px_-28px_rgba(11,31,58,0.35)]">
                <div class="wf-auth-panel relative min-h-[180px] sm:min-h-[240px] lg:min-h-[480px] text-white"
                     style="background: linear-gradient(145deg, #071526 0%, #0b1f3a 55%, #14335a 100%);">
                    @include('front.partials.wf-deco-shapes')
                    <div class="absolute inset-0 opacity-25">
                        <img src="{{ route('brand.login-image') }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                    </div>
                    <div class="relative z-10 h-full flex flex-col justify-end p-6 sm:p-10">
                        <p class="text-xs font-bold tracking-[0.2em] uppercase text-[var(--wf-gold)]">WOFINS</p>
                        <h2 class="mt-3 text-xl sm:text-3xl font-bold leading-tight break-words">
                            Reset password dikelola administrator
                        </h2>
                        <p class="mt-3 text-sm text-white/75 max-w-sm">
                            Untuk keamanan, tautan reset mandiri tidak digunakan di sistem ini.
                        </p>
                    </div>
                </div>

                <div class="p-6 sm:p-10 flex items-center min-w-0">
                    <div class="w-full max-w-md mx-auto min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--wf-navy)] break-words">Lupa password?</h1>
                        <p class="mt-2 text-sm text-[var(--wf-muted)] leading-relaxed">
                            Hubungi administrator perusahaan Anda untuk mengatur ulang akses akun.
                        </p>

                        <div class="mt-6 rounded-2xl border border-[var(--wf-line)] bg-[var(--wf-cream)]/60 p-4 sm:p-5">
                            <p class="text-sm font-semibold text-[var(--wf-navy)]">Yang perlu disampaikan</p>
                            <ul class="mt-2 space-y-1.5 text-sm text-[var(--wf-muted)] list-disc pl-4">
                                <li>Nama lengkap dan email akun</li>
                                <li>Permintaan reset password</li>
                            </ul>
                        </div>

                        <a href="{{ route('front.login') }}"
                           class="wf-btn-navy mt-8 w-full inline-flex items-center justify-center px-5 py-3.5 text-sm">
                            Kembali ke login
                        </a>

                        <p class="mt-5 text-center text-xs text-[var(--wf-muted)] break-words">
                            Butuh bantuan WOFINS?
                            <a href="{{ config('wofins.license.contact_url', 'https://wofins.id/kontak') }}" class="font-semibold text-[var(--wf-navy)] hover:text-[var(--wf-gold)]">Hubungi kami</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection
