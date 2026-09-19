<?php

return [
    'license' => [
        'enabled' => env('WOFINS_LICENSE_ENABLED', env('APP_ENV') === 'production'),
        'server' => rtrim((string) env('WOFINS_LICENSE_SERVER', 'https://maknafinance.id'), '/'),
        'verify_path' => '/api/item-purchase-codes/verify',
        'grace_days' => (int) env('WOFINS_LICENSE_GRACE_DAYS', 7),
        'reverify_hours' => (int) env('WOFINS_LICENSE_REVERIFY_HOURS', 6),
        'contact_url' => env('WOFINS_LICENSE_CONTACT', 'https://wofins.id/kontak'),
        'contact_whatsapp' => env('WOFINS_LICENSE_WHATSAPP', 'https://wa.me/6281373183794?text=Halo,%20saya%20perlu%20perpanjang%20lisensi%20WOFINS.'),
    ],

    'provider' => [
        'legal_name' => env('WOFINS_PROVIDER_NAME', 'Makna Kreatif Indonesia'),
        'brand' => env('WOFINS_PROVIDER_BRAND', 'WOFINS'),
        'address' => env(
            'WOFINS_PROVIDER_ADDRESS',
            'Jl. Sintraman Jaya I No. 2148, 20 Ilir D II, Kec. Kemuning, Kota Palembang, Sumatera Selatan 30137'
        ),
        'email' => env('WOFINS_PROVIDER_EMAIL', 'office@wofins.id'),
        'support_email' => env('MAIL_SUPPORT_ADDRESS', 'support@wofins.id'),
        'whatsapp' => env('WOFINS_PROVIDER_WHATSAPP', '+62 813-7318-3794'),
        'website' => env('WOFINS_PUBLIC_URL', 'https://wofins.id'),
        'app_url' => env('WOFINS_APP_URL', 'https://app.wofins.id'),
        'signatory_name' => env('WOFINS_PROVIDER_SIGNATORY', 'Kuasa Pengelola WOFINS'),
        'signatory_title' => env('WOFINS_PROVIDER_SIGNATORY_TITLE', 'Penyedia Layanan'),
    ],

    'agreement' => [
        'version' => env('WOFINS_AGREEMENT_VERSION', '2026-09-19'),
        'skip_super_admin' => (bool) env('WOFINS_AGREEMENT_SKIP_SUPER_ADMIN', false),
    ],
];
