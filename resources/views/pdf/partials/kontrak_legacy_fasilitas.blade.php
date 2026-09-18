@php
    $groupedItems = ($items ?? collect())->groupBy(function ($item) {
        return $item->vendor->name ?? 'LAIN-LAIN';
    });
    $product = $record->product ?? null;
    $penambahanItems = $product?->penambahanHarga ?? collect();
    $penguranganItems = $product?->pengurangans ?? collect();
    $labels = ['Tent Options:', 'Fleet Available:', 'Streaming Package:', 'Peralatan:'];
    $replacements = array_map(function ($label) {
        return '<span class="subheading">'.$label.'</span>';
    }, $labels);
@endphp

<div class="facility-list">
    <ol>
        @foreach ($groupedItems as $categoryName => $categoryItems)
            <li style="font-weight: normal; font-size: 11px; margin-top: 1px;">
                <span class="facility-title">{{ \Illuminate\Support\Str::title($categoryName) }}</span>

                @if ($categoryItems->count() === 1)
                    @php
                        $item = $categoryItems->first();
                        $vendor = $item->vendor;
                        $description = $vendor?->description ?? ($item->description ?? ($vendor?->name ?? ''));
                        $plainContent = trim(strip_tags($description));
                        $hideListStyle = preg_match('/^\s*[\da-zA-Z]+[.)]\s*/', $plainContent);
                        $descriptionNormalized = $hideListStyle
                            ? preg_replace('/^\s*[\da-zA-Z]+[.)]\s*/', '', $description)
                            : $description;
                        $descriptionFormatted = str_replace($labels, $replacements, $descriptionNormalized);
                    @endphp
                    <div class="item-desc" style="margin-top: 8px;">
                        {!! $descriptionFormatted !!}
                    </div>
                @else
                    <ol class="list-alpha">
                        @foreach ($categoryItems as $item)
                            @php
                                $vendor = $item->vendor;
                                $description = $vendor?->description ?? ($item->description ?? ($vendor?->name ?? ''));
                                $plainContent = trim(strip_tags($description));
                                $hideListStyle = preg_match('/^\s*[\da-zA-Z]+[.)]\s*/', $plainContent);
                                $descriptionNormalized = $hideListStyle
                                    ? preg_replace('/^\s*[\da-zA-Z]+[.)]\s*/', '', $description)
                                    : $description;
                                $descriptionFormatted = str_replace($labels, $replacements, $descriptionNormalized);
                            @endphp
                            <li style="font-size: 11px; margin-top: 5px; margin-bottom: 5px;">
                                {!! $descriptionFormatted !!}
                            </li>
                        @endforeach
                    </ol>
                @endif
            </li>
        @endforeach
    </ol>
</div>

@if ($penambahanItems->isNotEmpty())
    <div class="section-title">PENAMBAHAN :</div>
    <div class="facility-list">
        <ol>
            @foreach ($penambahanItems as $item)
                <li>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="margin-right: 10px;">
                            <div style="font-weight: normal; overflow: hidden;">
                                {{ strtoupper($item->vendor->name ?? 'Penambahan Tanpa Nama') }}
                                @if (! is_null($item->harga_publish) && (float) $item->harga_publish > 0)
                                    <span class="price-right">
                                        Rp {{ number_format((int) $item->harga_publish, 0, ',', '.') }},-
                                    </span>
                                @endif
                            </div>
                            @if (! empty($item->description))
                                <div class="item-desc">
                                    {!! str_replace($labels, $replacements, $item->description) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
@endif

@if ($penguranganItems->isNotEmpty())
    <div class="section-title">PENGURANGAN :</div>
    <div class="facility-list">
        <ol>
            @foreach ($penguranganItems as $item)
                <li>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="margin-right: 10px;">
                            <div style="font-weight: normal; overflow: hidden;">
                                {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($item->description ?? 'Pengurangan Tanpa Nama')) }}
                                @if (! is_null($item->amount))
                                    <span class="price-right">
                                        Rp {{ number_format((int) $item->amount, 0, ',', '.') }},-
                                    </span>
                                @endif
                            </div>
                            @if (! empty($item->notes))
                                <div class="item-notes">
                                    {!! str_replace($labels, $replacements, $item->notes) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
@endif

@php
    $freePenguranganHtml = (string) ($record->product?->free_pengurangan ?? '');
    $freePenguranganText = trim(str_replace("\xc2\xa0", ' ', strip_tags($freePenguranganHtml)));
@endphp
@if ($freePenguranganText !== '')
    <div class="section-title">FREE</div>
    <div class="facility-list">
        {!! $record->product->free_pengurangan !!}
    </div>
@endif
