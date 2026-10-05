@extends('layouts.app')

@section('title', 'Produk — WOFINS')

@push('styles')
    @include('front.partials.wf-front-base-styles')
@endpush

@section('content')
    <div class="wf-page min-h-screen bg-[var(--wf-cream)]">
        @include('front.partials.wf-nav')

        <section class="border-b border-[var(--wf-line)] bg-white">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--wf-gold)]">Toko</p>
                        <h1 class="mt-2 break-words text-3xl font-bold leading-tight text-[var(--wf-navy)] sm:text-4xl">Paket Pernikahan</h1>
                        <p class="mt-2 text-sm text-[var(--wf-muted)] sm:text-base">
                            {{ $products->total() }} paket
                            @if ($categorySlug !== '')
                                di kategori ini
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('product') }}" method="GET" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row lg:max-w-md">
                        @if ($categorySlug !== '')
                            <input type="hidden" name="kategori" value="{{ $categorySlug }}">
                        @endif
                        <label for="q" class="sr-only">Cari produk</label>
                        <input type="text" name="q" id="q" value="{{ $search }}"
                            class="min-w-0 w-full flex-1 rounded-full border border-[var(--wf-line)] bg-[var(--wf-cream)] px-4 py-2.5 text-sm text-[var(--wf-ink)] focus:border-[var(--wf-navy)] focus:bg-white focus:ring-[var(--wf-gold)]"
                            placeholder="Cari paket">
                        <button type="submit" class="wf-btn-navy w-full shrink-0 px-5 py-2.5 text-sm sm:w-auto">Cari</button>
                    </form>
                </div>

                @if ($categories->isNotEmpty())
                    <div class="mt-6 flex max-w-full gap-2 overflow-x-auto pb-1 [-webkit-overflow-scrolling:touch]">
                        <a href="{{ route('product', array_filter(['q' => $search !== '' ? $search : null])) }}"
                            class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-bold {{ $categorySlug === '' ? 'bg-[var(--wf-navy)] text-white' : 'bg-[var(--wf-cream)] text-[var(--wf-navy)]' }}">
                            Semua
                        </a>
                        @foreach ($categories as $category)
                            <a href="{{ route('product', array_filter(['q' => $search !== '' ? $search : null, 'kategori' => $category->slug])) }}"
                                class="shrink-0 whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-bold {{ $categorySlug === $category->slug ? 'bg-[var(--wf-navy)] text-white' : 'bg-[var(--wf-cream)] text-[var(--wf-navy)]' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="py-6 sm:py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if ($products->count() === 0)
                    <div class="rounded-2xl border border-dashed border-[var(--wf-line)] bg-white px-5 py-12 text-center">
                        <p class="text-base font-semibold text-[var(--wf-navy)]">Paket tidak ditemukan</p>
                        <p class="mt-1 text-sm text-[var(--wf-muted)]">Coba kata kunci lain, atau lihat semua paket.</p>
                        <a href="{{ route('product') }}" class="wf-btn-ghost mt-5 inline-flex w-full justify-center px-5 py-2.5 text-sm sm:w-auto">Lihat semua</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($products as $product)
                            @php
                                $title = \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $product->name)));
                                $publishTotal = (int) ($product->product_price ?? 0);
                                if ($publishTotal <= 0) {
                                    $publishTotal = (int) ($product->price ?? 0);
                                }
                            @endphp
                            <a href="{{ route('products.show', $product) }}"
                                class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[var(--wf-line)] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="relative aspect-[4/3] overflow-hidden bg-[var(--wf-cream)]">
                                    @if ($product->image)
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $title }}"
                                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-end bg-[var(--wf-navy)] p-4">
                                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--wf-gold)]">
                                                {{ $product->category->name ?? 'Paket' }}
                                            </span>
                                        </div>
                                    @endif
                                    @if ($product->category && $product->image)
                                        <span class="absolute left-3 top-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[var(--wf-navy)]">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex min-w-0 flex-1 flex-col gap-3 p-4">
                                    <h2 class="break-words text-base font-bold leading-snug text-[var(--wf-navy)]">{{ $title }}</h2>
                                    @if ($publishTotal > 0)
                                        <p class="break-words text-sm font-extrabold text-[var(--wf-navy)]">
                                            Rp {{ number_format($publishTotal, 0, ',', '.') }}
                                        </p>
                                    @endif
                                    <div class="mt-auto flex flex-wrap gap-1.5 text-[11px] font-semibold text-[var(--wf-muted)]">
                                        @if ($product->pax)
                                            <span class="rounded-full bg-[var(--wf-cream)] px-2.5 py-1">Resepsi {{ number_format($product->pax, 0, ',', '.') }} pax</span>
                                        @endif
                                        @if ($product->pax_akad)
                                            <span class="rounded-full bg-[var(--wf-cream)] px-2.5 py-1">Akad {{ number_format($product->pax_akad, 0, ',', '.') }} pax</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <nav class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Halaman paket">
                            <p class="text-sm text-[var(--wf-muted)]">
                                {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }} paket
                            </p>
                            <div class="grid grid-cols-2 gap-2 sm:flex">
                                @if ($products->onFirstPage())
                                    <span class="inline-flex items-center justify-center rounded-full border border-[var(--wf-line)] px-4 py-2 text-sm font-bold text-[var(--wf-muted)]">Sebelumnya</span>
                                @else
                                    <a href="{{ $products->previousPageUrl() }}" class="wf-btn-ghost inline-flex items-center justify-center px-4 py-2 text-sm">Sebelumnya</a>
                                @endif
                                @if ($products->hasMorePages())
                                    <a href="{{ $products->nextPageUrl() }}" class="wf-btn-navy inline-flex items-center justify-center px-4 py-2 text-sm">Berikutnya</a>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full border border-[var(--wf-line)] px-4 py-2 text-sm font-bold text-[var(--wf-muted)]">Berikutnya</span>
                                @endif
                            </div>
                        </nav>
                    @endif
                @endif
            </div>
        </section>

        @include('front.partials.wf-footer')
    </div>
@endsection
