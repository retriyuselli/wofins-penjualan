<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Draft Kontrak</title>
    <style>
        @page {
            margin: 122px 45px 30px 65px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1;
            color: #000;
            margin: 0;
        }

        /* Fixed Header */
        header {
            position: fixed;
            top: -112px;
            left: 0;
            right: 0;
            height: 105px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-img {
            max-width: 180px;
            max-height: 85px;
            width: auto;
            height: auto;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -10px;
            right: 0px;
            text-align: right;
            font-size: 11px;
            color: #000000;
        }

        .pagenum:before {
            content: counter(page);
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.1);
            /* Transparent gray */
            z-index: -1000;
            text-align: center;
            white-space: nowrap;
        }

        /* Typography */
        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 11px;
            margin-top: 2px;
            margin-bottom: 5px;
            padding-top: 0;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 0px;
            margin-bottom: 1px !important;
            text-transform: uppercase;
            font-size: 11px;
            text-decoration: underline;
        }

        .facility-list {
            /* margin-top: 5px; */
            margin-bottom: 5px;
            line-height: 1;
        }

        .facility-list>ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 20px;
        }

        .facility-list>ol>li {
            margin-top: 5px;
            margin-bottom: 0px;
            padding-left: 0px;
        }

        .facility-list>ol>li>ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 32px;
        }

        .facility-list p {
            /* margin-top: 5px; */
            margin-bottom: 5px;
            margin-left: 0;
            padding-left: 0;
            text-align: left;
            display: block;
        }

        .facility-list li ol {
            margin-top: 5px;
            margin-bottom: 0px;
            padding-left: 32px;
            list-style-type: lower-alpha;
        }

        .facility-list li ul {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 28px;
            list-style-type: circle;
        }

        .facility-list li ul li::marker {
            font-size: 12px;
        }

        .facility-title {
            font-weight: bold;
            font-size: 11px;
            text-transform: capitalize;
        }

        .subheading {
            display: block;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .price-right {
            float: right;
            margin-right: 100px;
            white-space: nowrap;
        }
        .list-alpha {
            list-style-type: lower-alpha;
            margin-top: 5px;
            margin-left: 28px;
            padding-left: 32px;
        }
        .list-decimal {
            list-style-type: decimal;
            margin-top: 5px;
            margin-left: 20px;
        }

        .penambahan-list {
            /* margin-top: 5px; */
            margin-bottom: 5px;
            line-height: 1;
        }

        .penambahan-list>ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 20px;
        }

        .penambahan-list>ol>li {
            margin-top: 5px;
            margin-bottom: 0px;
            padding-left: 0px;
        }

        .penambahan-list>ol>li>ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 32px;
        }

        .penambahan-list p {
            /* margin-top: 5px; */
            margin-bottom: 5px;
            margin-left: 0;
            padding-left: 0;
            text-align: left;
            display: block;
        }

        .penambahan-list li ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 32px;
            list-style-type: lower-alpha;
        }

        .penambahan-list li ul {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 28px;
            list-style-type: circle;
        }

        .penambahan-list li ul li::marker {
            font-size: 12px;
        }
        
        .penambahan-title {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .pengurangan-list {
            /* margin-top: 5px; */
            margin-bottom: 5px;
            line-height: 1;
        }

        .pengurangan-list>ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 20px;
        }

        .pengurangan-list>ol>li {
            margin-top: 5px;
            margin-bottom: 0px;
            padding-left: 0px;
        }

        .pengurangan-list>ol>li>ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 32px;
        }

        .pengurangan-list p {
            margin-top: 5px;
            margin-bottom: 5px;
            margin-left: 0;
            padding-left: 0;
            text-align: left;
            display: block;
        }

        .pengurangan-list li ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 32px;
            list-style-type: lower-alpha;
        }

        .pengurangan-list li ul {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 28px;
            list-style-type: circle;
        }

        .pengurangan-list li ul li::marker {
            font-size: 12px;
        }
        
        .pengurangan-title {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .termin-list {
            margin-top: 5px;
            margin-left: 10px;
        }

        .konfirmasi-list {
            margin-top: 5px;
            margin-bottom: 10px;
            padding-left: 20px;
        }

        .konfirmasi-list>li {
            margin-top: 5px;
            margin-bottom: 5px;
        }

        /* Content Tables */
        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        table.content-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            width: 130px;
            font-weight: normal;
        }

        .separator {
            width: 10px;
            text-align: center;
        }

        .amount {
            text-align: right;
        }

        /* Lists */
        ol,
        ul {
            margin: 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 5px;
            text-align: justify;
        }

        li p {
            margin-bottom: 5px;
            margin: 0;
            display: inline;
        }

        /* Utilities */
        .text-justify {
            text-align: justify;
        }

        .indent {
            margin-left: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #000;
            padding: 10px;
        }

        .sign-space {
            height: 70px;
        }

        /* Invoice Style Header Text */
        .company-name {
            font-size: 11px;
            font-weight: bold;
        }

        .company-info {
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="watermark"></div>
    @php
        $companyName = '{Isi dengan nama perusahaan}';
        $companyAddress = '{Isi dengan alamat perusahaan}';
        $companyPhone = '{Isi dengan nomor telepon perusahaan}';
        $companyEmail = '{Isi dengan email perusahaan}';
        $companyOwnerName = '{Isi dengan nama pemilik perusahaan}';
        $companyOwnerPosition = '{Isi dengan jabatan pemilik perusahaan}';
        $companyBankName = '{Isi dengan nama bank perusahaan}';
        $companyBankAccount = '{Isi dengan nomor rekening bank perusahaan}';
        $companyBankHolder = '{Isi dengan nama pemegang rekening bank perusahaan}';

        if (\Illuminate\Support\Facades\Schema::hasTable('companies')) {
            $company = \App\Models\Company::with('paymentMethod')->first();

            if ($company?->company_name) {
                $companyName = $company->company_name;
            }

            if ($company?->address) {
                $companyAddress = $company->address;
            }

            if ($company?->phone) {
                $companyPhone = $company->phone;
            }

            if ($company?->email) {
                $companyEmail = $company->email;
            }

            if ($company?->owner_name) {
                $companyOwnerName = $company->owner_name;
            }

            if ($company?->jabatan_owner) {
                $companyOwnerPosition = $company->jabatan_owner;
            }

            if ($company?->paymentMethod) {
                if ($company->paymentMethod->bank_name) {
                    $companyBankName = $company->paymentMethod->bank_name;
                }
                if ($company->paymentMethod->no_rekening) {
                    $companyBankAccount = $company->paymentMethod->no_rekening;
                }
                if ($company->paymentMethod->name) {
                    $companyBankHolder = $company->paymentMethod->name;
                }
            }
        }
    @endphp
    <!-- HEADER (Fixed on every page) -->
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 65%;">
                    <div class="company-name">{{ $companyName }}</div>
                    <div class="company-info">
                        Alamat : {{ $companyAddress }}<br>
                        No. Tlp : {{ $companyPhone }}<br>
                        Email : {{ $companyEmail }}
                    </div>
                </td>
                <td style="text-align: right;">
                    @php
                        $logoPath = null;
                        $logoSrc = '';
                        $logoWidth = null;
                        $logoHeight = null;
                        $logoMaxWidth = 180;
                        $logoMaxHeight = 85;

                        if (
                            isset($company) &&
                            $company?->logo_url &&
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo_url)
                        ) {
                            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($company->logo_url);
                        } else {
                            $logoPath = public_path('images/logomki.png');
                        }

                        if ($logoPath && file_exists($logoPath)) {
                            $logoMime = mime_content_type($logoPath);
                            if ($logoMime) {
                                $logoSrc =
                                    'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
                            }

                            $logoInfo = @getimagesize($logoPath);
                            if (is_array($logoInfo) && ($logoInfo[0] ?? 0) > 0 && ($logoInfo[1] ?? 0) > 0) {
                                $scale = min(
                                    $logoMaxWidth / $logoInfo[0],
                                    $logoMaxHeight / $logoInfo[1],
                                    1
                                );
                                $logoWidth = (int) round($logoInfo[0] * $scale);
                                $logoHeight = (int) round($logoInfo[1] * $scale);
                            }
                        }
                    @endphp
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo Perusahaan" class="logo-img"
                            @if ($logoWidth && $logoHeight) width="{{ $logoWidth }}" height="{{ $logoHeight }}" @endif>
                    @else
                        <b>{{ $companyName }}</b>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <!-- FOOTER -->
    <div class="footer">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="text-align: right; vertical-align: bottom; padding-right: 0px; font-size: 9px;">
                    @php
                        $printedAt = \Carbon\Carbon::now()->setTimezone('Asia/Jakarta');
                    @endphp
                    <span>
                        Dokumen ini dicetak secara otomatis pada
                        {{ $printedAt->translatedFormat('d F Y') }} pukul {{ $printedAt->translatedFormat('H:i') }} |
                        Hal <span class="pagenum"></span> |
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- MAIN CONTENT -->

    <!-- Title -->
    <div class="title">{{ $contract['title'] ?? 'KONTRAK KERJASAMA PERNIKAHAN' }}</div>
    <div class="subtitle">Nomor : {{ $nomorSurat }}</div>

    <!-- Pihak Pertama -->
    <table class="content-table">
        <tr>
            <td style="width: 20px;">I.</td>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $companyOwnerName }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="label">Jabatan</td>
            <td class="separator">:</td>
            <td>{{ $companyOwnerPosition }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="label">No. Telp</td>
            <td class="separator">:</td>
            <td>{{ $companyPhone }}</td>
        </tr>
    </table>
    <div class="text-justify indent" style="margin-bottom: 10px;">
        {!! $contract['intro_pihak_pertama'] ?? 'Bertindak untuk dan atas nama '.$companyName.' beralamat di '.$companyAddress.', selanjutnya disebut PIHAK PERTAMA.' !!}
    </div>

    <!-- Pihak Kedua -->
    <table class="content-table">
        <tr>
            <td style="width: 20px;">II.</td>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $record->name_ttd ?? '{Nama Pihak Kedua}' }}
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="label">No. Telp</td>
            <td class="separator">:</td>
            <td>+62{{ $prospect->phone ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="label">Alamat</td>
            <td class="separator">:</td>
            <td>{{ $prospect->address ?? '{Alamat Sesuai KTP}' }}</td>
        </tr>
    </table>
    <div class="text-justify indent" style="margin-bottom: 15px;">
        {!! $contract['intro_pihak_kedua'] ?? 'Bertindak untuk dan atas nama diri sendiri, selanjutnya disebut PIHAK KEDUA.' !!}
    </div>

    <div class="text-justify" style="margin-bottom: 15px;">
        {!! $contract['intro_after_parties'] ?? '' !!}
    </div>

    <!-- Event Details -->
    <div class="section-title">{{ $contract['package_section_title'] ?? 'Dream Wedding Packages' }}</div>
    <table class="content-table">
        <tr>
            <td class="label">Nama Acara</td>
            <td class="separator">:</td>
            <td>{{ $prospect->name_event ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Paket WO</td>
            <td class="separator">:</td>
            <td>
                @if ($record->product?->name)
                    {{ \Illuminate\Support\Str::title($record->product->name) }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Lokasi Acara</td>
            <td class="separator">:</td>
            <td>{{ $prospect->venue ?? '-' }}</td>
        </tr>
    </table>

    @if (!empty($prospect->date_lamaran))
        <div class="section-title">Lamaran / Pengajian / Siraman</div>
        <table class="content-table">
            <tr>
                <td class="label">Hari / Tanggal</td>
                <td class="separator">:</td>
                <td>{{ \Carbon\Carbon::parse($prospect->date_lamaran)->locale('id')->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="separator">:</td>
                <td>
                    @if (!empty($prospect->time_lamaran))
                        Pukul {{ \Carbon\Carbon::parse($prospect->time_lamaran)->format('H:i') }} wib s.d Selesai
                    @else
                        Pukul 07:00 / 07:30 wib s.d Selesai
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Jumlah Undangan</td>
                <td class="separator">:</td>
                <td>
                    {{ $record->product->pax_akad ?? 500 }} Pax atau
                    {{ ($record->product->pax_akad ?? 500) / 2 }} Undangan (Asumsi)
                </td>
            </tr>
        </table>
    @endif

    @if (!empty($prospect->date_akad))
        <div class="section-title">Akad Nikah</div>
        <table class="content-table">
            <tr>
                <td class="label">Hari / Tanggal</td>
                <td class="separator">:</td>
                <td>{{ \Carbon\Carbon::parse($prospect->date_akad)->locale('id')->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="separator">:</td>
                <td>
                    @if (!empty($prospect->time_akad))
                        Pukul {{ \Carbon\Carbon::parse($prospect->time_akad)->format('H:i') }} wib s.d Selesai
                    @else
                        Pukul 07:00 / 07:30 wib s.d Selesai
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Jumlah Undangan</td>
                <td class="separator">:</td>
                <td>
                    {{ $record->product->pax_akad ?? 500 }} Pax atau
                    {{ ($record->product->pax_akad ?? 500) / 2 }} Undangan (Asumsi)
                </td>
            </tr>
        </table>
    @endif

    @if (!empty($prospect->date_resepsi))
        <div class="section-title">Resepsi</div>
        <table class="content-table">
            <tr>
                <td class="label">Hari / Tanggal</td>
                <td class="separator">:</td>
                <td>{{ \Carbon\Carbon::parse($prospect->date_resepsi)->locale('id')->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="separator">:</td>
                <td>
                    @if (!empty($prospect->time_resepsi))
                        Pukul {{ \Carbon\Carbon::parse($prospect->time_resepsi)->format('H:i') }} wib s.d Selesai
                    @else
                        Pukul 10:00 wib s.d Selesai
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Jumlah Undangan</td>
                <td class="separator">:</td>
                <td>{{ $record->product->pax ?? 500 }} Pax atau {{ ($record->product->pax ?? 500) / 2 }} Undangan (Asumsi)</td>
            </tr>
        </table>
    @endif

    @php
        $product = $record->product;
        $baseTotalPrice = 0.0;
        $productPenambahan = (float) ($record->penambahan ?? 0);
        $productPengurangan = abs((float) ($record->pengurangan ?? 0));
        $promo = abs((float) ($record->promo ?? 0));

        if ($product) {
            $baseTotalPrice = (float) ($product->product_price ?? 0);
            if ($baseTotalPrice <= 0 && isset($items)) {
                $baseTotalPrice = (float) $items->sum('price_public');
            }
        } else {
            $baseTotalPrice = (float) ($record->total_price ?? 0);
        }

        if ($baseTotalPrice <= 0) {
            $baseTotalPrice = (float) ($record->total_price ?? 0);
        }

        if ($product && $productPenambahan <= 0) {
            $productPenambahan = (float) ($product->penambahan_publish ?? 0);
            if ($productPenambahan <= 0 && $product?->penambahanHarga) {
                $productPenambahan = (float) $product->penambahanHarga->sum('harga_publish');
            }
        }

        if ($product && $productPengurangan <= 0) {
            $productPengurangan = abs((float) ($product->pengurangan ?? 0));
            if ($productPengurangan <= 0 && $product?->pengurangans) {
                $productPengurangan = abs((float) $product->pengurangans->sum('amount'));
            }
        }
        $computedGrandTotal = \App\Services\OrderFinance::computeGrandTotalFromValues(
            (float) $baseTotalPrice,
            (float) $productPenambahan,
            (float) $promo,
            (float) $productPengurangan,
        );
    @endphp

    <div class="section-title" style="margin-top: 15px;">PERINCIAN BIAYA</div>
    <table class="content-table" style="width: 100%;">
        <tr>
            <td style="padding: 5px 0;"><b>{{ $contract['package_price_label'] ?? 'DREAM WEDDING PACKAGE' }}</b></td>
            <td style="width: 1%; white-space: nowrap; padding: 5px 0;"><b>: Rp. </b></td>
            <td style="width: 60%; padding: 5px 0; text-align: left;">
                <b>&nbsp;{{ number_format($baseTotalPrice, 0, ',', '.') }},-</b>
            </td>
        </tr>
        @if ($productPenambahan > 0)
            <tr>
                <td style="padding: 5px 0;">PENAMBAHAN</td>
                <td style="width: 1%; white-space: nowrap; padding: 5px 0;">: Rp. </td>
                <td style="text-align: left; padding: 5px 0;">
                    &nbsp;{{ number_format($productPenambahan, 0, ',', '.') }},-</td>
            </tr>
        @endif
        @if ($productPengurangan > 0)
            <tr>
                <td style="padding: 5px 0;">PENGURANGAN</td>
                <td style="width: 1%; white-space: nowrap; padding: 5px 0;">: Rp. </td>
                <td style="text-align: left; padding: 5px 0;">
                    &nbsp;({{ number_format($productPengurangan, 0, ',', '.') }},-)</td>
            </tr>
        @endif
        @if ($promo > 0)
            <tr>
                <td style="padding: 5px 0;">PROMO</td>
                <td style="width: 1%; white-space: nowrap; padding: 5px 0;">: Rp. </td>
                <td style="text-align: left; padding: 5px 0;">
                    &nbsp;({{ number_format($promo, 0, ',', '.') }},-)</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 5px 0;"><b>TOTAL PEMBAYARAN</b></td>
            <td style="padding: 5px 0; width: 1%; white-space: nowrap;"><b>: Rp.</b></td>
            <td style="padding: 5px 0; text-align: left;">
                <b>&nbsp;{{ number_format($computedGrandTotal, 0, ',', '.') }},-</b>
            </td>
        </tr>
    </table>

    <!-- Facilities -->
    <div class="section-title" style="margin-top: 15px;">{{ $contract['facilities_heading'] ?? 'DENGAN RINCIAN FASILITAS SEBAGAI BERIKUT :' }}</div>
    @php
        $groupedItems = $items->groupBy(function ($item) {
            return $item->vendor->name ?? 'LAIN-LAIN';
        });
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
                            $labels = ['Tent Options:', 'Fleet Available:', 'Streaming Package:', 'Peralatan:'];
                            $replacements = array_map(function ($l) {
                                return '<span class="subheading">' . $l . '</span>';
                            }, $labels);
                            $descriptionFormatted = str_replace($labels, $replacements, $descriptionNormalized);
                        @endphp
                        <div class="item-desc" style="margin-top: 8px;">
                            {!! \App\Support\SafeHtml::fromRichText($descriptionFormatted) !!}
                        </div>
                    @else
                        <ol class="list-alpha">
                            @foreach ($categoryItems as $item)
                                @php
                                    $vendor = $item->vendor;
                                    $description =
                                        $vendor?->description ?? ($item->description ?? ($vendor?->name ?? ''));
                                    $plainContent = trim(strip_tags($description));
                                    $hideListStyle = preg_match('/^\s*[\da-zA-Z]+[.)]\s*/', $plainContent);
                                    $descriptionNormalized = $hideListStyle
                                        ? preg_replace('/^\s*[\da-zA-Z]+[.)]\s*/', '', $description)
                                        : $description;
                                    $labels = ['Tent Options:', 'Fleet Available:', 'Streaming Package:', 'Peralatan:'];
                                    $replacements = array_map(function ($l) {
                                        return '<span class="subheading">' . $l . '</span>';
                                    }, $labels);
                                    $descriptionFormatted = str_replace($labels, $replacements, $descriptionNormalized);
                                @endphp
                                <li style="font-size: 11px; margin-top: 5px; margin-bottom: 5px;">
                                    {!! \App\Support\SafeHtml::fromRichText($descriptionFormatted) !!}
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>

    <!-- Penambahan -->
    @php
        $product = $record->product;
        $penambahanItems = $product?->penambahanHarga ?? collect();
        $penguranganItems = $product?->pengurangans ?? collect();
    @endphp

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
                                    @if (!is_null($item->harga_publish) && (float) $item->harga_publish > 0)
                                        <span class="price-right">
                                            Rp {{ number_format((int) $item->harga_publish, 0, ',', '.') }},-
                                        </span>
                                    @endif
                                </div>
                                @if (!empty($item->description))
                                    @php
                                        $labels = ['Tent Options:', 'Fleet Available:', 'Streaming Package:', 'Peralatan:'];
                                        $replacements = array_map(function ($l) {
                                            return '<span class="subheading">' . $l . '</span>';
                                        }, $labels);
                                        $descFormatted = str_replace($labels, $replacements, $item->description);
                                    @endphp
                                    <div class="item-desc">
                                        {!! \App\Support\SafeHtml::fromRichText($descFormatted) !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    <!-- Pengurangan -->
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
                                    @if (!is_null($item->amount))
                                        <span class="price-right">
                                            Rp {{ number_format((int) $item->amount, 0, ',', '.') }},-
                                        </span>
                                    @endif
                                </div>
                                @if (!empty($item->notes))
                                    @php
                                        $labels = ['Tent Options:', 'Fleet Available:', 'Streaming Package:', 'Peralatan:'];
                                        $replacements = array_map(function ($l) {
                                            return '<span class="subheading">' . $l . '</span>';
                                        }, $labels);
                                        $notesFormatted = str_replace($labels, $replacements, $item->notes);
                                    @endphp
                                    <div class="item-notes">
                                        {!! \App\Support\SafeHtml::fromRichText($notesFormatted) !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    <!-- Free -->
    @php
        $freePenguranganHtml = (string) ($record->product?->free_pengurangan ?? '');
        $freePenguranganText = trim(str_replace("\xc2\xa0", ' ', strip_tags($freePenguranganHtml)));
    @endphp
    @if ($freePenguranganText !== '')
        <div class="section-title">FREE</div>
        <div class="facility-list">
            {!! \App\Support\SafeHtml::fromRichText($record->product->free_pengurangan) !!}
        </div>
    @endif

    @php
        $contractSections = $contract['sections'] ?? [];
        $termsKeys = ['konfirmasi', 'pembayaran', 'vendor', 'pembatalan', 'force_majeure'];
        $shownTermsHeading = false;
    @endphp
    @foreach ($contractSections as $section)
        @if (! $shownTermsHeading && in_array($section['key'] ?? '', $termsKeys, true))
            <div class="section-title">KETENTUAN TAMBAHAN</div>
            @php $shownTermsHeading = true; @endphp
        @endif
        <div class="section-title">{{ $section['title'] }}</div>
        <div class="facility-list">
            {!! $section['html'] !!}
        </div>
    @endforeach

    <p style="text-align: justify; margin-top: 10px;">
        {!! $contract['closing_text'] ?? 'Demikianlah Kontrak Kerjasama Paket Pernikahan ini dibuat dalam 2 (dua) rangkap dan ditandatangani oleh kedua belah pihak.' !!}
    </p>

    <!-- Signatures -->
    <div style="text-align: right; margin-bottom: 10px; margin-top: 20px;">
        {{ $company->city ?? 'Palembang' }},
        {{ $record->created_at
            ? $record->created_at->copy()->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y')
            : \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}
    </div>
    <div class="signature-section" style="margin-top: 0;">
        <table class="signature-table">
            <tr>
                <td style="width: 35%;">
                    Menyetujui,<br>
                </td>
                <td colspan="2" style="width: 65%;">
                    Mengetahui,<br>
                    {{ $companyName }}
                </td>
            </tr>
            <tr>
                <td style="vertical-align: bottom; height: 120px;">
                    <div style="text-decoration: underline;">
                        {{ $record->name_ttd ?? '....................' }}
                    </div>
                    <b>{{ $record->title_ttd ?? 'Calon Pengantin' }}</b>
                </td>
                <td style="vertical-align: bottom; height: 100px;">
                    <div style="text-decoration: underline;">
                        {{ $companyOwnerName }}
                    </div>
                    <b>{{ $companyOwnerPosition }}</b>
                </td>
                <td style="vertical-align: bottom; height: 100px;">
                    <div style="text-decoration: underline;">
                        {{ $record->user?->name ?? 'Account Manager' }}
                    </div>
                    <b>Account Manager</b>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
