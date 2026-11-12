<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'Halaman tidak ditemukan' }} — WOFINS</title>
    <style>
        :root {
            --wf-navy: #0b1f3a;
            --wf-gold: #c9a227;
            --wf-cream: #f7f4ee;
            --wf-ink: #1a2332;
            --wf-muted: #5c6675;
            --wf-line: #e6e2d9;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', system-ui, sans-serif;
            color: var(--wf-ink);
            background:
                radial-gradient(ellipse 80% 50% at 10% 20%, rgba(201, 162, 39, 0.12), transparent 55%),
                linear-gradient(180deg, #fff 0%, var(--wf-cream) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: max(1.25rem, env(safe-area-inset-top)) max(1rem, env(safe-area-inset-right)) max(1.25rem, env(safe-area-inset-bottom)) max(1rem, env(safe-area-inset-left));
        }
        .card {
            width: 100%;
            max-width: 28rem;
            min-width: 0;
            background: #fff;
            border: 1px solid var(--wf-line);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 24px 60px -28px rgba(11, 31, 58, 0.35);
        }
        h1 {
            margin: 0;
            font-size: 1.35rem;
            color: var(--wf-navy);
            word-break: break-word;
        }
        p {
            margin: 0.6rem 0 0;
            font-size: 0.9rem;
            color: var(--wf-muted);
            line-height: 1.5;
        }
        a.btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 1.25rem;
            padding: 0.85rem 1rem;
            border-radius: 999px;
            background: var(--wf-navy);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
        }
        a.btn:hover { filter: brightness(1.08); }
    </style>
</head>
<body>
    <main class="card">
        <h1>{{ $title ?? 'Halaman tidak ditemukan' }}</h1>
        <p>{{ $message ?? 'Alamat di admin tidak valid atau datanya sudah tidak ada. Anda masih masuk; silakan kembali ke dashboard.' }}</p>
        <a class="btn" href="{{ url('/admin') }}">Kembali ke dashboard</a>
    </main>
</body>
</html>
