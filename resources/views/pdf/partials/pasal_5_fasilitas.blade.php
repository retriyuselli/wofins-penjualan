@php
    $companyLabel = $company->company_name ?? config('app.name');
    $venueLabel = $prospect->venue ?? 'Venue';
    $items = $items ?? collect();

    $isVenueItem = function ($item) use ($prospect): bool {
        $category = strtolower((string) ($item->vendor->category->name ?? ''));
        $vendorName = strtolower((string) ($item->vendor->name ?? ''));
        $venue = strtolower((string) ($prospect->venue ?? ''));
        foreach (['venue', 'gedung', 'lokasi', 'ballroom', 'hall', 'garden'] as $needle) {
            if (str_contains($category, $needle) || str_contains($vendorName, $needle)) {
                return true;
            }
        }

        return $venue !== '' && (str_contains($vendorName, $venue) || str_contains($venue, $vendorName));
    };

    $formatItemHtml = function ($item): string {
        $vendor = $item->vendor;
        $title = $vendor->name ?? ($item->description ?? 'Fasilitas');
        $description = $item->description ?: ($vendor->description ?? '');
        $html = '<span class="facility-title">'.e(\Illuminate\Support\Str::title((string) $title)).'</span>';
        if (trim(strip_tags((string) $description)) !== '') {
            $html .= '<div class="item-desc">'.\App\Support\SafeHtml::fromRichText($description).'</div>';
        }

        return $html;
    };

    $venueItems = $items->filter($isVenueItem);
    $woItems = $items->reject($isVenueItem);
    $penambahanItems = $product?->penambahanHarga ?? collect();
    $penguranganItems = $product?->pengurangans ?? collect();
    $freePenguranganHtml = (string) ($product?->free_pengurangan ?? '');
    $freePenguranganText = trim(str_replace("\xc2\xa0", ' ', strip_tags($freePenguranganHtml)));
@endphp

<ol class="pasal5-list">
    {{-- a. Fasilitas --}}
    <li>
        <span class="facility-title">Fasilitas</span>
        <ul>
            @if ($venueItems->isNotEmpty())
                <li>
                    <span class="facility-title">Fasilitas {{ $venueLabel }}</span>
                    <ul>
                        @foreach ($venueItems as $item)
                            <li>{!! $formatItemHtml($item) !!}</li>
                        @endforeach
                    </ul>
                </li>
            @endif

            <li>
                <span class="facility-title">Fasilitas {{ $companyLabel }}</span>
                <ul>
                    @forelse ($woItems as $item)
                        <li>{!! $formatItemHtml($item) !!}</li>
                    @empty
                        @if ($venueItems->isEmpty())
                            <li>Fasilitas mengikuti rincian paket yang disepakati.</li>
                        @endif
                    @endforelse
                    @if ($freePenguranganText !== '')
                        <li>
                            <span class="facility-title">Lainnya</span>
                            <div class="item-desc">{!! \App\Support\SafeHtml::fromRichText($freePenguranganHtml) !!}</div>
                        </li>
                    @endif
                </ul>
            </li>
        </ul>
    </li>

    {{-- b. Penambahan --}}
    <li>
        <span class="facility-title">Penambahan</span>
        @if ($penambahanItems->isNotEmpty())
            <ul>
                @foreach ($penambahanItems as $item)
                    <li>
                        <div style="overflow: hidden;">
                            {{ strtoupper($item->vendor->name ?? 'Penambahan') }}
                            @if (! is_null($item->harga_publish) && (float) $item->harga_publish > 0)
                                <span class="price-right">
                                    Rp {{ number_format((int) $item->harga_publish, 0, ',', '.') }},-
                                </span>
                            @endif
                        </div>
                        @if (! empty($item->description))
                            <div class="item-desc">{!! \App\Support\SafeHtml::fromRichText($item->description) !!}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="item-desc">Tidak ada penambahan di luar paket.</div>
        @endif
    </li>

    {{-- c. Pengurangan --}}
    <li>
        <span class="facility-title">Pengurangan</span>
        @if ($penguranganItems->isNotEmpty())
            <ul>
                @foreach ($penguranganItems as $item)
                    <li>
                        <div style="overflow: hidden;">
                            {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($item->description ?? 'Pengurangan')) }}
                            @if (! is_null($item->amount) && (float) $item->amount != 0)
                                <span class="price-right">
                                    Rp {{ number_format((int) $item->amount, 0, ',', '.') }},-
                                </span>
                            @endif
                        </div>
                        @if (! empty($item->notes))
                            <div class="item-desc">{!! \App\Support\SafeHtml::fromRichText($item->notes) !!}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="item-desc">Tidak ada pengurangan.</div>
        @endif
    </li>
</ol>
