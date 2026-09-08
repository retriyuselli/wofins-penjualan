<?php

return [
    'license' => [
        'enabled' => env('WOFINS_LICENSE_ENABLED', env('APP_ENV') === 'production'),
        'server' => rtrim((string) env('WOFINS_LICENSE_SERVER', 'https://maknafinance.id'), '/'),
        'verify_path' => '/api/item-purchase-codes/verify',
        'grace_days' => (int) env('WOFINS_LICENSE_GRACE_DAYS', 7),
        'reverify_hours' => (int) env('WOFINS_LICENSE_REVERIFY_HOURS', 6),
        'contact_url' => env('WOFINS_LICENSE_CONTACT', 'https://maknafinance.id/kontak'),
        'contact_whatsapp' => env('WOFINS_LICENSE_WHATSAPP', 'https://wa.me/6281373183794?text=Halo,%20saya%20perlu%20perpanjang%20lisensi%20WOFINS.'),
    ],
];
