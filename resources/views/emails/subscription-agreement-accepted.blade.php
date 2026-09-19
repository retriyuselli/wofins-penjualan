<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salinan Perjanjian Berlangganan — {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1a2332; max-width: 640px; margin: 0 auto; padding: 20px; background-color: #f7f4ee; }
        .container { background: #fff; border-radius: 12px; padding: 28px; border: 1px solid #e6e2d9; }
        .header { background: linear-gradient(145deg, #0b1f3a 0%, #14335a 100%); color: #fff; padding: 22px; border-radius: 10px; margin-bottom: 22px; text-align: center; }
        .header p { margin: 0; font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; color: #e8d48b; }
        .header h1 { margin: 8px 0 0; font-size: 20px; }
        .lead { font-size: 15px; color: #5c6675; margin: 0 0 20px; }
        .box { background: #f7f4ee; border: 1px solid #e6e2d9; border-radius: 10px; padding: 16px 18px; margin-bottom: 18px; }
        .box h2 { margin: 0 0 10px; font-size: 14px; color: #0b1f3a; }
        .summary-line { padding: 8px 0; border-bottom: 1px solid #e6e2d9; font-size: 14px; color: #0b1f3a; }
        .summary-line:last-child { border-bottom: none; }
        .label { color: #5c6675; }
        .value { font-weight: 600; }
        .footer { margin-top: 24px; font-size: 12px; color: #5c6675; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>WOFINS</p>
            <h1>Salinan perjanjian berlangganan</h1>
        </div>

        <p class="lead">
            Halo <strong>{{ $user->name }}</strong>, Anda baru saja menyetujui Perjanjian Berlangganan WOFINS.
            PDF lengkap terlampir pada email ini.
        </p>

        <div class="box">
            <h2>Bukti persetujuan</h2>
            <div class="summary-line"><span class="label">Perusahaan</span> : <span class="value">{{ $data['legal_name'] }}</span></div>
            <div class="summary-line"><span class="label">Pemilik / kuasa</span> : <span class="value">{{ $data['owner_name'] }}</span></div>
            <div class="summary-line"><span class="label">Paket</span> : <span class="value">{{ $data['plan_name'] }}</span></div>
            <div class="summary-line"><span class="label">Versi</span> : <span class="value">{{ $acceptance->version }}</span></div>
            <div class="summary-line"><span class="label">Waktu</span> : <span class="value">{{ $acceptance->created_at?->timezone(config('app.timezone'))->translatedFormat('d F Y H:i') }}</span></div>
            <div class="summary-line"><span class="label">Nomor</span> : <span class="value">{{ $data['contract_number'] }}</span></div>
        </div>

        <p class="lead">
            Simpan lampiran ini sebagai arsip. Jika ada pertanyaan, hubungi
            {{ $data['provider']['support_email'] }}.
        </p>

        <p class="footer">Email otomatis dari {{ config('app.name') }}. Jangan membalas ke alamat ini.</p>
    </div>
</body>
</html>
