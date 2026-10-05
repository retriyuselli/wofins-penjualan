<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penawaran — {{ $product->name }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        @page { margin: 14mm 12mm 18mm 12mm; }
        body { font-size: 9.5pt; color: #1f2937; line-height: 1.35; margin: 0; }
        .wm {
            position: fixed;
            top: -14mm;
            left: -12mm;
            width: 210mm;
            height: 297mm;
            z-index: 50;
        }
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -11mm;
            text-align: center;
            font-size: 8pt;
            line-height: 1.35;
            color: #6b7280;
        }
        h2 { font-size: 11pt; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 7px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; }
        .center { text-align: center; }
        .right { text-align: right; }
        .head { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 12px; }
        .head p { margin: 2px 0; font-size: 8.5pt; color: #4b5563; }
        .notes ul, .notes ol { margin: 2px 0 0; padding-left: 14px; }
        .notes li { margin: 0; }
        .notes p { margin: 0; }
        .name { font-weight: bold; }
    </style>
</head>
<body>
    @php
        $pricing = $pricing ?? \App\Services\ProductPricingCalculator::calculateForProduct($product);
        $total = (int) ($pricing['final_publish'] ?? 0);
        if ($total <= 0) {
            $total = (int) ($product->price ?? 0);
        }
        if ($total <= 0) {
            $total = (int) ($product->product_price ?? 0);
        }
        $reference = $reference ?? ('PROD-'.str_pad((string) ($product->id ?? 0), 6, '0', STR_PAD_LEFT));
        $facilityName = function (?string $name): string {
            $name = trim((string) $name);
            $name = preg_replace('/^\s*\[(?:master|produk)\]\s*/i', '', $name) ?? $name;

            return \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $name)));
        };
        $productTitle = \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $product->name)));
    @endphp

    @if (! empty($watermarkSrc))
        <img class="wm" src="{{ $watermarkSrc }}" alt="">
    @endif

    <div class="footer">
        Berkas penawaran resmi {{ $companyName }} · {{ $reference }} · {{ now()->format('d M Y') }}<br>
        Tidak untuk digandakan atau digunakan pihak lain.
    </div>

    <div class="head">
        @if (! empty($logoSrc))
            <img src="{{ $logoSrc }}" alt="{{ $companyName }}" style="max-height: 42px; margin-bottom: 6px;">
        @endif
        <p>{{ $company?->address ?? '' }}</p>
        <p>{{ $companyName }}@if ($company?->email) | {{ $company->email }}@endif @if ($company?->phone) | {{ $company->phone }}@endif</p>
    </div>

    <table>
        <tr>
            <td>
                <strong>Wedding Package Product</strong><br>
                Product Name : {{ $productTitle }}<br>
                Category : {{ $product->category->name ?? '-' }}<br>
                Reference : {{ $reference }}<br>
                Capacity : {{ $product->pax ? number_format($product->pax, 0, ',', '.').' Pax' : '-' }}
                @if ($product->pax_akad)
                    <br>Akad : {{ number_format($product->pax_akad, 0, ',', '.') }} Pax
                @endif
            </td>
        </tr>
    </table>

    <h2>Package Components &amp; Services</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 36px;" class="center">No</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($product->items ?? [] as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        <div class="name">{{ $item->vendor ? $facilityName($item->vendor->name) : 'Fasilitas' }}</div>
                        @if (filled($item->description))
                            <div class="notes">{!! \App\Support\SafeHtml::fromRichText(\App\Support\ProductNotesFormatter::forPreview($item->description)) !!}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="center">Tidak ada komponen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if (($product->penambahanHarga ?? collect())->count() > 0)
        <h2>Penambahan</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 36px;" class="center">No</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->penambahanHarga as $addition)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="name">{{ $addition->vendor ? $facilityName($addition->vendor->name) : 'Penambahan' }}</div>
                            @if (filled($addition->description))
                                <div class="notes">{!! \App\Support\SafeHtml::fromRichText(\App\Support\ProductNotesFormatter::forPreview($addition->description)) !!}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (($product->pengurangans ?? collect())->count() > 0)
        <h2>Pengurangan</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 36px;" class="center">No</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->pengurangans as $discount)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="name">{{ \Illuminate\Support\Str::title(strtolower(trim(strip_tags((string) ($discount->description ?? 'Pengurangan'))))) }}</div>
                            @if (filled($discount->notes ?? null))
                                <div class="notes">{!! \App\Support\SafeHtml::fromRichText(\App\Support\ProductNotesFormatter::forPreview($discount->notes)) !!}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($total > 0)
        <h2>Total</h2>
        <table>
            <tr>
                <td class="name">Total Paket</td>
                <td class="right name">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <h2>Payment Schedule</h2>
        <table>
            <tr>
                <td>Booking Fee (10%)</td>
                <td class="right">Rp {{ number_format($total * 0.1, 0, ',', '.') }}</td>
                <td>Saat konfirmasi</td>
            </tr>
            <tr>
                <td>Down Payment (20%)</td>
                <td class="right">Rp {{ number_format($total * 0.2, 0, ',', '.') }}</td>
                <td>7 hari setelah booking</td>
            </tr>
            <tr>
                <td>Second Payment (40%)</td>
                <td class="right">Rp {{ number_format($total * 0.4, 0, ',', '.') }}</td>
                <td>60 hari sebelum hari H</td>
            </tr>
            <tr>
                <td>Final Payment (30%)</td>
                <td class="right">Rp {{ number_format($total * 0.3, 0, ',', '.') }}</td>
                <td>14 hari sebelum hari H</td>
            </tr>
        </table>
    @endif
</body>
</html>
