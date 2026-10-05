<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Paket — {{ $product->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Noto Sans', sans-serif; }
        .notes ul, .notes ol { margin: 0.25rem 0 0; padding-left: 1.1rem; }
        .notes ul { list-style: disc; }
        .notes ol { list-style: decimal; }
        .notes li { margin-top: 0.2rem; }
        .notes li p { margin: 0; }
        .wm {
            position: fixed;
            inset: 0;
            z-index: 30;
            pointer-events: none;
            background-repeat: repeat;
            background-position: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            @page { size: A4; margin: 14mm; }
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .sheet { box-shadow: none !important; max-width: none !important; padding: 0 !important; }
            .wm { position: fixed; }
        }
    </style>
</head>
<body class="bg-gray-100 p-3 sm:p-8">
    @php
        $pricing = $pricing ?? \App\Services\ProductPricingCalculator::calculateForProduct($product);
        $total = (int) ($pricing['final_publish'] ?? 0);
        if ($total <= 0) {
            $total = (int) ($product->price ?? 0);
        }
        if ($total <= 0) {
            $total = (int) ($product->product_price ?? 0);
        }
        $reference = 'PROD-'.str_pad((string) ($product->id ?? 0), 6, '0', STR_PAD_LEFT);
        $wmLabel = trim($companyName.' · PENAWARAN · '.$reference);
        $wmSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="220" viewBox="0 0 640 220">'
            .'<text x="320" y="120" text-anchor="middle" transform="rotate(-28 320 110)" '
            .'fill="rgba(11,31,58,0.16)" font-size="22" font-family="Noto Sans, sans-serif" font-weight="700" letter-spacing="3">'
            .e($wmLabel)
            .'</text></svg>';
        $wmUrl = 'data:image/svg+xml,'.rawurlencode($wmSvg);
        $facilityName = function (?string $name): string {
            $name = trim((string) $name);
            $name = preg_replace('/^\s*\[(?:master|produk)\]\s*/i', '', $name) ?? $name;

            return \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $name)));
        };
    @endphp

    <div class="wm" style="background-image: url('{{ $wmUrl }}');" aria-hidden="true"></div>

    <div class="no-print mx-auto mb-4 flex max-w-4xl flex-col gap-2 sm:flex-row sm:justify-end">
        <a href="{{ route('products.cetakPdf', $product) }}" class="inline-flex w-full items-center justify-center rounded-full border border-slate-900 bg-white px-5 py-2.5 text-center text-sm font-bold text-slate-900 sm:w-auto">Download PDF</a>
        <button type="button" onclick="window.print()" class="w-full rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white sm:w-auto">Cetak</button>
    </div>

    <div class="sheet mx-auto max-w-4xl break-words rounded-lg bg-white p-4 shadow-lg sm:p-10">
        <div class="mb-6 border-b border-gray-200 pb-4 text-center">
            @if (! empty($logoSrc))
                <img src="{{ $logoSrc }}" alt="{{ $companyName }}" class="mx-auto mb-4 max-h-10">
            @endif
            <p class="text-xs text-gray-600 sm:text-sm">{{ $company?->address ?? '' }}</p>
            <p class="text-xs text-gray-600 sm:text-sm">{{ $companyName }}@if ($company?->email) | {{ $company->email }}@endif @if ($company?->phone) | {{ $company->phone }}@endif</p>
        </div>

        <table class="w-full border-collapse text-sm">
            <tr>
                <td class="w-full border border-gray-300 p-3 align-top sm:w-1/2">
                    <strong>Wedding Package Product</strong><br>
                    Product Name : {{ \Illuminate\Support\Str::title(strtolower(str_replace('_', ' ', $product->name))) }}<br>
                    Category : {{ $product->category->name ?? '-' }}<br>
                    Reference : {{ $reference }}<br>
                    Capacity : {{ $product->pax ? number_format($product->pax, 0, ',', '.').' Pax' : '-' }}
                    @if ($product->pax_akad)
                        <br>Akad : {{ number_format($product->pax_akad, 0, ',', '.') }} Pax
                    @endif
                </td>
            </tr>
        </table>

        <div class="mt-8">
            <h2 class="mb-3 text-sm font-bold">Package Components &amp; Services</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-0 border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="w-12 border border-slate-300 px-3 py-2 text-left">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->items ?? [] as $item)
                            <tr class="align-top odd:bg-white even:bg-slate-50">
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $loop->iteration }}</td>
                                <td class="border border-slate-300 px-3 py-2">
                                    <div class="font-bold">{{ $item->vendor ? $facilityName($item->vendor->name) : 'Fasilitas' }}</div>
                                    @if (filled($item->description))
                                        <div class="notes mt-1 text-sm">{!! \App\Support\SafeHtml::fromRichText(\App\Support\ProductNotesFormatter::forPreview($item->description)) !!}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="border border-slate-300 px-3 py-4 text-center text-gray-500">Tidak ada komponen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (($product->penambahanHarga ?? collect())->count() > 0)
            <div class="mt-8">
                <h2 class="mb-3 text-sm font-bold">Penambahan</h2>
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="w-12 border border-slate-300 px-3 py-2 text-left">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product->penambahanHarga as $addition)
                            <tr class="align-top odd:bg-white even:bg-slate-50">
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $loop->iteration }}</td>
                                <td class="border border-slate-300 px-3 py-2">
                                    <div class="font-bold">{{ $addition->vendor ? $facilityName($addition->vendor->name) : 'Penambahan' }}</div>
                                    @if (filled($addition->description))
                                        <div class="notes mt-1 text-sm">{!! \App\Support\SafeHtml::fromRichText(\App\Support\ProductNotesFormatter::forPreview($addition->description)) !!}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (($product->pengurangans ?? collect())->count() > 0)
            <div class="mt-8">
                <h2 class="mb-3 text-sm font-bold">Pengurangan</h2>
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="w-12 border border-slate-300 px-3 py-2 text-left">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product->pengurangans as $discount)
                            <tr class="align-top odd:bg-white even:bg-slate-50">
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $loop->iteration }}</td>
                                <td class="border border-slate-300 px-3 py-2">
                                    <div class="font-bold">{{ \Illuminate\Support\Str::title(strtolower(trim(strip_tags((string) ($discount->description ?? 'Pengurangan'))))) }}</div>
                                    @if (filled($discount->notes ?? null))
                                        <div class="notes mt-1 text-sm">{!! \App\Support\SafeHtml::fromRichText(\App\Support\ProductNotesFormatter::forPreview($discount->notes)) !!}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($total > 0)
            <div class="mt-8">
                <h2 class="mb-3 text-sm font-bold">Total</h2>
                <table class="w-full border-collapse text-sm">
                    <tr>
                        <td class="border border-gray-300 px-3 py-2 font-bold">Total Paket</td>
                        <td class="border border-gray-300 px-3 py-2 text-right font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <div class="mt-8">
                <h2 class="mb-3 text-sm font-bold">Payment Schedule</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">Booking Fee (10%)</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rp {{ number_format($total * 0.1, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2">Saat konfirmasi</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">Down Payment (20%)</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rp {{ number_format($total * 0.2, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2">7 hari setelah booking</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">Second Payment (40%)</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rp {{ number_format($total * 0.4, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2">60 hari sebelum hari H</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">Final Payment (30%)</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rp {{ number_format($total * 0.3, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2">14 hari sebelum hari H</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif

        <p class="mt-8 text-center text-[11px] text-gray-500">
            Berkas penawaran resmi {{ $companyName }} · {{ $reference }} · {{ now()->format('d M Y') }}.
            Tidak untuk digandakan atau digunakan pihak lain.
        </p>
    </div>
</body>
</html>
