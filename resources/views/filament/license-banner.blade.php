@php
    $status = app(\App\Services\AppLicenseService::class)->status(false);
    $license = $status['license'] ?? null;
    $valid = $status['valid'] ?? true;
    $days = $status['days_remaining'] ?? null;
    $show = config('wofins.license.enabled') && $license && $valid && $days !== null && $days <= 30;
@endphp

@if ($show)
    <div class="fi-license-banner mx-auto w-full max-w-full px-4 pt-4 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-warning-50 px-4 py-3 text-sm text-warning-800 ring-1 ring-warning-200 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20">
            Masa berlangganan WOFINS berakhir
            <strong>{{ $license->ends_at?->translatedFormat('d M Y') }}</strong>
            (sisa {{ $days }} hari).
            <a href="{{ \App\Filament\Pages\ActivateLicense::getUrl() }}" class="font-semibold underline">Lihat lisensi</a>
            atau
            <a href="{{ config('wofins.license.contact_url') }}" target="_blank" class="font-semibold underline">perpanjang di maknafinance.id</a>.
        </div>
    </div>
@endif
