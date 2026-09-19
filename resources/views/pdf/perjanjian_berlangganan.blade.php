<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Perjanjian Berlangganan WOFINS — {{ $legal_name }}</title>
    <style>
        @page { size: a4 portrait; margin: 22mm 16mm 20mm 16mm; }
        body {
            color: #111;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            margin: 0;
        }
        h1 { font-size: 14px; margin: 0 0 4px 0; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10px; margin: 11px 0 5px 0; }
        p { margin: 0 0 6px 0; text-align: justify; }
        ol, ul { margin: 0 0 7px 18px; padding: 0 0 0 4px; }
        ol { list-style-type: decimal; }
        ul { list-style-type: disc; }
        li { margin-bottom: 3px; text-align: justify; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 8px 0; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; text-align: left; }
        th { background: #f3f1ea; width: 34%; font-weight: bold; }
        .muted { color: #444; font-size: 8.5px; text-align: center; margin-bottom: 12px; }
        .intro { margin-top: 8px; }
        .sign { width: 100%; border: none; margin-top: 24px; }
        .sign td { border: none; width: 50%; padding: 8px 12px 0 0; }
        .sign-line { border-bottom: 1px solid #333; height: 48px; margin: 8px 16px 6px 0; }
        .stamp { font-size: 8px; color: #555; }
        .article table th { width: auto; }
        .plan-wrap { margin: 4px 0 8px 0; }
        table.plan-table { table-layout: fixed; font-size: 7.5px; line-height: 1.3; margin: 0; }
        table.plan-table th,
        table.plan-table td { padding: 3px 4px; }
        table.plan-table .col-plan { width: 16%; }
        table.plan-table .col-price { width: 16%; white-space: nowrap; }
        table.plan-table .col-seat { width: 14%; }
        table.plan-table .col-scope { width: 54%; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Perjanjian Berlangganan Perangkat Lunak<br>WOFINS</h1>
    <p class="muted">
        Nomor: {{ $contract_number }} · {{ $place }},
        {{ $signed_on->locale('id')->translatedFormat('d F Y') }}
    </p>

    <p class="intro">
        Perjanjian ini dibuat pada hari ini antara:
    </p>

    <table>
        <tr>
            <th>Pihak Pertama (Penyedia)</th>
            <td>
                <strong>{{ $provider['legal_name'] }}</strong>, pengelola merek
                <strong>{{ $provider['brand'] }}</strong><br>
                {{ $provider['address'] }}<br>
                Email: {{ $provider['email'] }} / {{ $provider['support_email'] }}<br>
                WhatsApp: {{ $provider['whatsapp'] }}<br>
                Situs: {{ $provider['website'] }} · {{ $provider['app_url'] }}
            </td>
        </tr>
        <tr>
            <th>Pihak Kedua (Pelanggan / Pemilik Company)</th>
            <td>
                <strong>{{ $legal_name }}</strong><br>
                Diwakili oleh: {{ $owner_name }}, {{ $owner_title }}<br>
                Alamat: {{ $address }}<br>
                Email: {{ $email }} · Telepon: {{ $phone }}<br>
                Website/domain: {{ $website }}<br>
                NIB: {{ $nib }} · NPWP: {{ $npwp }} · Izin usaha: {{ $business_license }}
            </td>
        </tr>
    </table>

    <p>
        Pihak Kedua adalah end user dan pemilik perusahaan yang memakai Layanan untuk kepentingan
        usaha Wedding Organizer-nya. Para pihak sepakat terikat pada syarat berikut.
    </p>

    <h2>Identitas langganan</h2>
    <table>
        <tr>
            <th>Paket</th>
            <td>{{ $plan_name }}</td>
        </tr>
        <tr>
            <th>Durasi / billing</th>
            <td>{{ $billing_label }}</td>
        </tr>
        <tr>
            <th>Nilai berlangganan</th>
            <td>{{ $amount_label }}</td>
        </tr>
        <tr>
            <th>Tanggal mulai</th>
            <td>{{ $starts_at ? $starts_at->locale('id')->translatedFormat('d F Y') : '—' }}</td>
        </tr>
        <tr>
            <th>Tanggal berakhir</th>
            <td>{{ $ends_at ? $ends_at->locale('id')->translatedFormat('d F Y') : 'Mengikuti masa aktif paket / perpanjangan' }}</td>
        </tr>
        <tr>
            <th>Nomor pesanan</th>
            <td>{{ $order_code }}</td>
        </tr>
        <tr>
            <th>Ringkasan paket</th>
            <td>{{ $plan_scope }}</td>
        </tr>
    </table>

    @foreach ($articles as $article)
        <h2>{{ $article['title'] }}</h2>
        <div class="article">{!! $article['body'] !!}</div>
    @endforeach

    <h2>Pasal 17 — Kontak resmi</h2>
    <ul>
        <li>Email: {{ $provider['support_email'] }} · {{ $provider['email'] }}</li>
        <li>WhatsApp: {{ $provider['whatsapp'] }}</li>
        <li>Situs: {{ $provider['website'] }}</li>
    </ul>

    <p>
        Demikian Perjanjian ini dibuat dalam rangkap yang sama kekuatannya, ditandatangani
        Pihak Pertama dan Pihak Kedua (pemilik company / kuasa yang berwenang).
        Materai dipakai jika diwajibkan peraturan yang berlaku.
    </p>

    <table class="sign">
        <tr>
            <td>
                <strong>Pihak Pertama</strong><br>
                {{ $provider['legal_name'] }}<br>
                {{ $provider['signatory_title'] }}
                <div class="sign-line"></div>
                {{ $provider['signatory_name'] }}<br>
                <span class="stamp">Tanda tangan &amp; nama terang</span>
            </td>
            <td>
                <strong>Pihak Kedua</strong><br>
                {{ $legal_name }}<br>
                {{ $owner_title }}
                <div class="sign-line"></div>
                {{ $owner_name }}<br>
                <span class="stamp">Tanda tangan &amp; nama terang</span>
            </td>
        </tr>
    </table>
</body>
</html>
