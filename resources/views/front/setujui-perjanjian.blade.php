@extends('layouts.app')

@section('title', 'Persetujuan Perjanjian Berlangganan — WOFINS')

@push('styles')
@include('front.partials.wf-front-base-styles')
<style>
    .wf-gate {
        min-height: 100dvh;
        background: rgba(7, 21, 38, 0.55);
        padding: max(1rem, env(safe-area-inset-top)) 1rem max(1rem, env(safe-area-inset-bottom));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wf-gate-card {
        width: min(720px, 100%);
        max-height: min(92dvh, 900px);
        background: #fff;
        border-radius: 1.25rem;
        border: 1px solid var(--wf-line);
        box-shadow: 0 24px 60px -28px rgba(7, 21, 38, 0.55);
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
    }

    .wf-gate-head {
        padding: 1.1rem 1.15rem 0.85rem;
        border-bottom: 1px solid var(--wf-line);
        flex-shrink: 0;
    }

    .wf-gate-scroll {
        overflow: auto;
        padding: 1rem 1.15rem;
        min-height: 0;
        flex: 1;
        -webkit-overflow-scrolling: touch;
    }

    .wf-gate-scroll h2 {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--wf-navy);
        margin: 0.85rem 0 0.3rem;
    }

    .wf-gate-scroll p,
    .wf-gate-scroll li {
        font-size: 0.72rem;
        line-height: 1.45;
        color: var(--wf-muted);
        overflow-wrap: anywhere;
    }

    .wf-gate-scroll ol,
    .wf-gate-scroll ul {
        padding-left: 1.2rem;
        margin: 0.3rem 0 0.55rem;
    }

    .wf-gate-scroll ol {
        list-style: decimal !important;
    }

    .wf-gate-scroll ul {
        list-style: disc !important;
    }

    .wf-gate-scroll li {
        display: list-item;
        font-size: 0.72rem;
        line-height: 1.45;
        color: var(--wf-muted);
        overflow-wrap: anywhere;
    }

    .wf-gate-scroll ol ol,
    .wf-gate-scroll ol ul,
    .wf-gate-scroll ul ul {
        margin: 0.25rem 0 0.15rem;
    }

    .wf-gate-scroll .plan-wrap {
        overflow-x: auto;
        max-width: 100%;
        margin: 0.4rem 0 0.7rem;
        -webkit-overflow-scrolling: touch;
    }

    .wf-gate-scroll table.plan-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 0.65rem;
        line-height: 1.35;
        margin: 0;
    }

    .wf-gate-scroll table.plan-table th,
    .wf-gate-scroll table.plan-table td {
        border: 1px solid var(--wf-line);
        padding: 4px 5px;
        vertical-align: top;
        text-align: left;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .wf-gate-scroll table.plan-table th {
        background: var(--wf-cream);
        color: var(--wf-navy);
        font-weight: 700;
        width: auto;
    }

    .wf-gate-scroll table.plan-table .col-plan { width: 18%; }
    .wf-gate-scroll table.plan-table .col-price { width: 20%; white-space: nowrap; }
    .wf-gate-scroll table.plan-table .col-seat { width: 16%; }
    .wf-gate-scroll table.plan-table td:nth-child(2) { white-space: nowrap; }

    .wf-gate-meta {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.7rem;
        margin-bottom: 0.75rem;
    }

    .wf-gate-meta th,
    .wf-gate-meta td {
        border: 1px solid var(--wf-line);
        padding: 0.45rem 0.55rem;
        text-align: left;
        vertical-align: top;
        word-break: break-word;
    }

    .wf-gate-meta th {
        width: 36%;
        background: var(--wf-cream);
        color: var(--wf-navy);
    }

    .wf-gate-foot {
        border-top: 1px solid var(--wf-line);
        padding: 0.9rem 1.15rem max(0.9rem, env(safe-area-inset-bottom));
        flex-shrink: 0;
        background: #fff;
    }

    .wf-gate-actions {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin-top: 0.75rem;
    }

    .wf-gate-actions .wf-btn-navy,
    .wf-gate-actions .wf-btn-ghost {
        width: 100%;
        text-align: center;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }

    @media (min-width: 480px) {
        .wf-gate-actions {
            flex-direction: row;
            flex-wrap: wrap;
        }
        .wf-gate-actions .wf-btn-navy {
            flex: 1 1 auto;
        }
        .wf-gate-actions .wf-btn-ghost {
            width: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="wf-page wf-gate" x-data="{ agreed: false }">
    <div class="wf-gate-card" role="dialog" aria-modal="true" aria-labelledby="gate-title">
        <div class="wf-gate-head">
            <p class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-[var(--wf-gold)]">Wajib disetujui</p>
            <h1 id="gate-title" class="text-lg font-bold leading-tight text-[var(--wf-navy)] sm:text-xl">
                Perjanjian Berlangganan WOFINS
            </h1>
            <p class="mt-2 text-sm text-[var(--wf-muted)]">
                Baca ringkasan di bawah, lalu setujui untuk masuk ke aplikasi.
                Salinan lengkap akan dikirim ke <strong class="break-all text-[var(--wf-navy)]">{{ $user->email }}</strong>.
            </p>
        </div>

        <div class="wf-gate-scroll">
            <table class="wf-gate-meta">
                <tr>
                    <th>Pihak Pertama</th>
                    <td>{{ $data['provider']['legal_name'] }} ({{ $data['provider']['brand'] }})</td>
                </tr>
                <tr>
                    <th>Pihak Kedua</th>
                    <td>{{ $data['legal_name'] }} — {{ $data['owner_name'] }}, {{ $data['owner_title'] }}</td>
                </tr>
                <tr>
                    <th>Paket</th>
                    <td>{{ $data['plan_name'] }} · {{ $data['billing_label'] }}</td>
                </tr>
                <tr>
                    <th>Nilai</th>
                    <td>{{ $data['amount_label'] }}</td>
                </tr>
                <tr>
                    <th>Versi</th>
                    <td>{{ $version }}</td>
                </tr>
            </table>

            @foreach ($data['articles'] as $article)
                <h2>{{ $article['title'] }}</h2>
                {!! $article['body'] !!}
            @endforeach
        </div>

        <form class="wf-gate-foot" method="post" action="{{ route('subscription-agreement.accept') }}">
            @csrf
            <label class="flex items-start gap-3 text-sm text-[var(--wf-ink)]">
                <input type="checkbox" name="agree" value="1" required
                    class="mt-1 h-4 w-4 shrink-0"
                    x-model="agreed">
                <span>
                    Saya, <strong>{{ $user->name }}</strong>, telah membaca dan menyetujui
                    Perjanjian Berlangganan WOFINS versi {{ $version }} atas nama
                    <strong>{{ $data['legal_name'] }}</strong>.
                </span>
            </label>
            @error('agree')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="wf-gate-actions">
                <button type="submit" class="wf-btn-navy" :disabled="!agreed" :class="!agreed && 'opacity-50'">
                    Setuju dan masuk
                </button>
                <a href="{{ route('companies.subscription-agreement', $company) }}" target="_blank" rel="noopener" class="wf-btn-ghost">
                    Unduh PDF
                </a>
            </div>
        </form>
        <form method="post" action="{{ route('logout') }}" class="px-4 pb-4 text-center">
            @csrf
            <button type="submit" class="text-xs text-[var(--wf-muted)] underline">Keluar</button>
        </form>
    </div>
</div>
@endsection
