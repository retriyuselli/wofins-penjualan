<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Draft Kontrak</title>
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
            $company = $company ?? \App\Models\Company::with('paymentMethod')->first();

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
                $logoSrc = 'data:'.$logoMime.';base64,'.base64_encode(file_get_contents($logoPath));
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

        $kopTopGap = 26;
        $titleGap = 14;
        $textBlockHeight = 48;
        $headerContentHeight = max($textBlockHeight, (int) ($logoHeight ?: 50)) + 8;
        $pageTopMargin = $kopTopGap + $headerContentHeight + $titleGap;
        $headerTopOffset = -($headerContentHeight + $titleGap);
    @endphp
    <style>
        @page {
            margin: {{ $pageTopMargin }}px 45px 30px 65px;
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
            top: {{ $headerTopOffset }}px;
            left: 0;
            right: 0;
            height: {{ $headerContentHeight }}px;
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

        /* Typography — default/global tetap seperti semula */
        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 11px;
            margin-top: 0;
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

        body.contract-spk .title {
            text-decoration: none;
            font-size: 12px;
            margin-top: 0;
            margin-bottom: 2px;
        }

        body.contract-spk .subtitle {
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .pasal-heading {
            text-align: center;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: none;
        }

        .pasal-nomor,
        .pasal-keterangan {
            font-weight: bold;
            font-size: 12px;
            line-height: 1.35;
            text-transform: none;
        }

        .pasal5-intro {
            text-align: justify;
            margin: 4px 0 8px;
            max-width: 100%;
            word-wrap: break-word;
        }

        .pasal5-list {
            list-style-type: lower-alpha;
            margin: 6px 0 10px 22px;
            padding-left: 18px;
        }

        .pasal5-list > li {
            margin-bottom: 8px;
        }

        .pasal5-list ul {
            list-style-type: disc;
            margin: 4px 0 6px 4px;
            padding-left: 18px;
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

        body.contract-spk .party-kedua-table {
            margin-left: 0;
            margin-right: 0;
            width: 100%;
        }

        body.contract-spk .party-kedua-table .label {
            width: 150px;
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

<body class="{{ ! empty($contract['is_spk']) ? 'contract-spk' : 'contract-legacy' }}">
    <div class="watermark"></div>
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
    @if (! empty($contract['is_spk']))
        @include('pdf.partials.kontrak_spk_body')
    @else
        @include('pdf.partials.kontrak_legacy_body')
    @endif

</body>

</html>
