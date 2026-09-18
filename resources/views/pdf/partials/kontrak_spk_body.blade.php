    <!-- Title -->
    <div class="title">{{ $contract['title'] ?? 'SURAT PERJANJIAN KERJA' }}</div>
    @if (! empty($contract['package_section_title']))
        <div class="subtitle">{{ $contract['package_section_title'] }}</div>
    @endif
    <div class="subtitle" style="text-transform: none; margin-bottom: 12px;">Nomor : {{ $nomorSurat }}</div>

    <p class="text-justify" style="margin-bottom: 10px;">
        Perjanjian ini dibuat pada {{ $contract['contract_date'] ?? ($record->created_at?->copy()->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') ?? now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y')) }} oleh dan antara :
    </p>

    <table class="content-table party-identity-table">
        <tr>
            <td class="num"></td>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $companyOwnerName }}</td>
        </tr>
        <tr>
            <td class="num"></td>
            <td class="label">Alamat Kantor</td>
            <td class="separator">:</td>
            <td>{{ $companyAddress }}</td>
        </tr>
        <tr>
            <td class="num"></td>
            <td class="label">Status</td>
            <td class="separator">:</td>
            <td>{{ $companyOwnerPosition }}</td>
        </tr>
    </table>
    <div class="text-justify" style="margin-bottom: 10px;">
        {!! $contract['intro_pihak_pertama'] ?? 'Bertindak untuk dan atas nama '.$companyName.' beralamat di '.$companyAddress.', selanjutnya disebut PIHAK PERTAMA.' !!}
    </div>

    <table class="content-table party-identity-table">
        <tr>
            <td class="num">1.</td>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $prospect->name_cpw ?? ($record->name_ttd ?? '-') }}</td>
        </tr>
        <tr>
            <td class="num"></td>
            <td class="label">Alamat</td>
            <td class="separator">:</td>
            <td>{{ $prospect->address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="num"></td>
            <td class="label">Status</td>
            <td class="separator">:</td>
            <td>Calon Pengantin Wanita</td>
        </tr>
        <tr>
            <td class="num">2.</td>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $prospect->name_cpp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="num"></td>
            <td class="label">Alamat</td>
            <td class="separator">:</td>
            <td>{{ $prospect->address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="num"></td>
            <td class="label">Status</td>
            <td class="separator">:</td>
            <td>Calon Pengantin Pria</td>
        </tr>
    </table>
    <div class="text-justify" style="margin-bottom: 15px;">
        {!! $contract['intro_pihak_kedua'] ?? 'Bertindak untuk dan atas nama diri sendiri, selanjutnya disebut PIHAK KEDUA.' !!}
    </div>

    <div class="intro-after-parties">
        {!! $contract['intro_after_parties'] ?? '' !!}
    </div>

    @php
        $contractSections = $contract['sections'] ?? [];
        $preambleKeys = \App\Support\ContractTemplateDefaults::preambleKeys();
        $preambleSections = [];
        $termSections = [];
        foreach ($contractSections as $section) {
            if (in_array($section['key'] ?? '', $preambleKeys, true)) {
                $preambleSections[] = $section;
            } else {
                $termSections[] = $section;
            }
        }
    @endphp
    @foreach ($preambleSections as $section)
        @include('pdf.partials.pasal_heading', [
            'nomor' => $section['title'] ?? '',
            'keterangan' => $section['keterangan'] ?? null,
        ])
        <div class="facility-list">
            {!! $section['html'] !!}
        </div>
    @endforeach

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
            $productPengurangan = abs((float) $product->pengurangan ?? 0);
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

    @include('pdf.partials.pasal_heading', [
        'nomor' => $contract['facilities_heading'] ?? 'Pasal 5',
        'keterangan' => null,
    ])
    <p class="pasal5-intro">
        Berikut adalah Fasilitas dari kesepakatan yang telah dilakukan antara lain :
    </p>
    <div class="facility-list">
        @include('pdf.partials.pasal_5_fasilitas', [
            'items' => $items,
            'product' => $product,
            'prospect' => $prospect,
            'company' => $company ?? null,
        ])
    </div>

    @foreach ($termSections as $section)
        @include('pdf.partials.pasal_heading', [
            'nomor' => $section['title'] ?? '',
            'keterangan' => $section['keterangan'] ?? null,
        ])
        <div class="facility-list">
            {!! $section['html'] !!}
        </div>
    @endforeach

    @if (! empty(trim(strip_tags($contract['closing_text'] ?? ''))))
        <p style="text-align: justify; margin-top: 10px;">
            {!! $contract['closing_text'] !!}
        </p>
    @endif

    <div style="text-align: right; margin-bottom: 10px; margin-top: 20px;">
        {{ $company->city ?? 'Palembang' }},
        {{ $record->created_at
            ? $record->created_at->copy()->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y')
            : \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}
    </div>
    <div class="signature-section" style="margin-top: 0;">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">Direktur</td>
                <td style="width: 50%;">Calon Pengantin</td>
            </tr>
            <tr>
                <td style="vertical-align: bottom; height: 120px;">
                    <div style="text-decoration: underline;">
                        {{ $companyOwnerName }}
                    </div>
                    <b>Direktur</b>
                </td>
                <td style="vertical-align: bottom; height: 120px;">
                    <div style="text-decoration: underline;">
                        {{ $record->name_ttd ?? $prospect->name_cpw ?? '....................' }}
                    </div>
                    <b>Calon Pengantin</b>
                </td>
            </tr>
        </table>
    </div>
