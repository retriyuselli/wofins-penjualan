    <div class="title">{{ $contract['title'] ?? 'KONTRAK KERJASAMA PERNIKAHAN' }}</div>
    <div class="subtitle">Nomor : {{ $nomorSurat }}</div>

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

    <table class="content-table">
        <tr>
            <td style="width: 20px;">II.</td>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $record->name_ttd ?? '{Nama Pihak Kedua}' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="label">No. Telp</td>
            <td class="separator">:</td>
            <td>{{ \App\Support\PhoneNumber::display($prospect->phone ?? null) }}</td>
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

    @if (! empty($prospect->date_lamaran))
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
                    @if (! empty($prospect->time_lamaran))
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

    @if (! empty($prospect->date_akad))
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
                    @if (! empty($prospect->time_akad))
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

    @if (! empty($prospect->date_resepsi))
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
                    @if (! empty($prospect->time_resepsi))
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

    <div class="section-title" style="margin-top: 15px;">{{ $contract['facilities_heading'] ?? 'DENGAN RINCIAN FASILITAS SEBAGAI BERIKUT :' }}</div>
    @include('pdf.partials.kontrak_legacy_fasilitas', [
        'items' => $items,
        'record' => $record,
    ])

    @php
        $contractSections = $contract['sections'] ?? [];
        $termsKeys = \App\Support\ContractTemplateDefaults::legacyTermsKeys();
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
