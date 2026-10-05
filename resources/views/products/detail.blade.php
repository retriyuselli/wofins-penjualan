@extends('layouts.app')

@section('title', \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $product->name))).' — WOFINS')

@push('styles')
    @include('front.partials.wf-front-base-styles')
    <style>
        .wf-include ul,
        .wf-include ol {
            margin: 0.35rem 0 0;
            padding-left: 1.25rem;
        }

        .wf-include ul {
            list-style: disc;
        }

        .wf-include ol {
            list-style: decimal;
        }

        .wf-include li {
            margin-top: 0.35rem;
            padding-left: 0.15rem;
        }

        .wf-include li p,
        .wf-include > p {
            margin: 0;
        }

        .wf-include > p + p,
        .wf-include > p + ul,
        .wf-include > p + ol,
        .wf-include > ul + p,
        .wf-include > ol + p {
            margin-top: 0.5rem;
        }
    </style>
@endpush

@section('content')
    @php
        $title = \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $product->name)));
        $publishTotal = (int) ($product->product_price ?? 0);
        if ($publishTotal <= 0) {
            $publishTotal = (int) ($product->price ?? 0);
        }

        $facilityName = function (?string $name): string {
            $name = trim((string) $name);
            $name = preg_replace('/^\s*\[(?:master|produk)\]\s*/i', '', $name) ?? $name;
            $name = str_replace('_', ' ', $name);

            return \Illuminate\Support\Str::title(strtolower(trim($name)));
        };

        $includedHtml = function (?string $html): string {
            $html = trim((string) $html);
            if ($html === '' || trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === '') {
                return '';
            }

            $html = strip_tags($html, '<ul><ol><li><p><br><strong><em><b><i>');
            $html = preg_replace('/<\s*(ul|ol|li|p|strong|em|b|i)\b[^>]*>/i', '<$1>', $html) ?? $html;
            $html = preg_replace('/<\s*br\b[^>]*>/i', '<br>', $html) ?? $html;

            return trim($html);
        };
    @endphp

    <div class="wf-page min-h-screen bg-[var(--wf-cream)]">
        @include('front.partials.wf-nav')

        <section class="py-6 sm:py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <a href="{{ route('product') }}"
                    class="inline-flex items-center text-sm font-semibold text-[var(--wf-navy)] hover:text-[var(--wf-gold)]">
                    <svg class="mr-2 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke toko
                </a>

                <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-start">
                    <div class="min-w-0 overflow-hidden rounded-2xl border border-[var(--wf-line)] bg-white">
                        <div class="aspect-[4/3] bg-[var(--wf-navy)]">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $title }}" class="h-full w-full max-w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-end p-6">
                                    <span class="text-sm font-bold uppercase tracking-[0.18em] text-[var(--wf-gold)]">
                                        {{ $product->category->name ?? 'Paket' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="min-w-0">
                        @if ($product->category)
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--wf-gold)]">{{ $product->category->name }}</p>
                        @endif
                        <h1 class="mt-2 break-words text-2xl font-bold leading-tight text-[var(--wf-navy)] sm:text-3xl">{{ $title }}</h1>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($product->pax)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[var(--wf-navy)]">Resepsi {{ number_format($product->pax, 0, ',', '.') }} pax</span>
                            @endif
                            @if ($product->pax_akad)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[var(--wf-navy)]">Akad {{ number_format($product->pax_akad, 0, ',', '.') }} pax</span>
                            @endif
                        </div>

                        @if ($product->description)
                            <div class="mt-5 break-words text-sm leading-relaxed text-[var(--wf-ink)]">
                                {!! nl2br(e(html_entity_decode(strip_tags((string) $product->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) !!}
                            </div>
                        @endif

                        @if ($publishTotal > 0)
                            <div class="mt-6">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--wf-muted)]">Harga paket</p>
                                <p class="mt-1 break-words text-2xl font-extrabold text-[var(--wf-navy)] sm:text-3xl">
                                    Rp {{ number_format($publishTotal, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                            <a href="{{ route('kontak') }}" class="wf-btn-gold inline-flex w-full items-center justify-center px-5 py-3 text-sm sm:w-auto">
                                Tanya paket ini
                            </a>
                            <button type="button" id="wf-share" class="wf-btn-ghost inline-flex w-full items-center justify-center px-5 py-3 text-sm sm:w-auto">
                                Bagikan
                            </button>
                            <a href="{{ route('products.cetak', $product) }}" target="_blank" rel="noopener"
                                class="wf-btn-ghost inline-flex w-full items-center justify-center px-5 py-3 text-sm sm:w-auto">
                                Cetak
                            </a>
                        </div>
                        <p id="wf-share-note" class="mt-2 hidden break-all text-xs font-semibold text-[var(--wf-navy)]" role="status">Tautan disalin</p>
                    </div>
                </div>

                @if ($product->items && $product->items->count())
                    <div class="mt-8">
                        <h2 class="text-lg font-bold text-[var(--wf-navy)]">Yang termasuk</h2>
                        <div class="mt-3 flex flex-col gap-3 sm:hidden">
                            @foreach ($product->items as $item)
                                @include('products.partials.included-card')
                            @endforeach
                        </div>
                        <div class="mt-3 hidden sm:grid sm:grid-cols-2 sm:items-start sm:gap-3">
                            @foreach ([0, 1] as $column)
                                <div class="flex min-w-0 flex-col gap-3">
                                    @foreach ($product->items as $item)
                                        @continue($loop->index % 2 !== $column)
                                        @include('products.partials.included-card')
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        @include('front.partials.wf-footer')
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('wf-share')?.addEventListener('click', async function () {
            const url = window.location.href;
            const title = document.title;
            const note = document.getElementById('wf-share-note');

            if (navigator.share) {
                try {
                    await navigator.share({ title, url });
                    return;
                } catch (error) {
                    if (error && error.name === 'AbortError') {
                        return;
                    }
                }
            }

            const copied = await (async function () {
                try {
                    await navigator.clipboard.writeText(url);
                    return true;
                } catch (error) {
                    const field = document.createElement('textarea');
                    field.value = url;
                    field.setAttribute('readonly', '');
                    field.style.position = 'fixed';
                    field.style.left = '-9999px';
                    document.body.appendChild(field);
                    field.select();
                    let ok = false;
                    try {
                        ok = document.execCommand('copy');
                    } catch (copyError) {
                        ok = false;
                    }
                    field.remove();
                    return ok;
                }
            })();

            if (note) {
                note.textContent = copied ? 'Tautan disalin' : url;
                note.classList.remove('hidden');
            }
        });
    </script>
@endpush
