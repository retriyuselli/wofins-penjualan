@php
    $service = app(\App\Services\AppLicenseService::class);
    $license = $service->current();
    $days = $license?->daysRemaining();
    $enabled = $service->isEnabled();
    $show = $license || $enabled;
@endphp

@if ($show)
    @php
        $url = \App\Filament\Pages\ActivateLicense::getUrl();
        $end = $license?->ends_at?->translatedFormat('d M Y');

        if (! $license) {
            $label = 'Belum aktif';
            $tone = 'neutral';
            $title = 'Aplikasi belum diaktivasi';
        } elseif ($days === null) {
            $label = $end ?: 'Lisensi';
            $tone = 'neutral';
            $title = 'Masa berlaku';
        } elseif ($days < 0) {
            $label = 'Habis';
            $tone = 'danger';
            $title = 'Masa berlangganan telah berakhir pada '.$end;
        } elseif ($days <= 30) {
            $label = 'Sisa '.$days.' hari';
            $tone = 'warning';
            $title = 'Masa berlaku sampai '.$end;
        } else {
            $label = $end;
            $tone = 'neutral';
            $title = 'Masa berlaku sampai '.$end.' (sisa '.$days.' hari)';
        }

        $toneClass = match ($tone) {
            'danger' => 'text-danger-700 bg-danger-50 ring-danger-200 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20',
            'warning' => 'text-warning-700 bg-warning-50 ring-warning-200 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20',
            default => 'text-gray-600 bg-gray-50 ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10',
        };
    @endphp

    <a
        href="{{ $url }}"
        title="{{ $title }}"
        class="fi-license-topbar {{ $toneClass }}"
        style="display: inline-flex; flex-direction: row; flex-wrap: nowrap; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 6px; font-size: 10px; font-weight: 500; line-height: 1; white-space: nowrap; text-decoration: none; width: max-content;"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="width: 11px; height: 11px; flex: 0 0 auto; display: block;">
            <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd" />
        </svg>
        <span class="fi-license-topbar-text">{{ $label }}</span>
    </a>
    <style>
        a.fi-license-topbar {
            display: inline-flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 4px !important;
            white-space: nowrap !important;
        }
        a.fi-license-topbar svg {
            display: block !important;
            flex: 0 0 11px !important;
            width: 11px !important;
            height: 11px !important;
        }
        a.fi-license-topbar .fi-license-topbar-text {
            display: inline-block !important;
            font-size: 10px !important;
            line-height: 11px !important;
            transform: none !important;
            position: static !important;
        }
    </style>
@endif
