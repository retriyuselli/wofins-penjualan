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
            $short = 'Aktifkan';
            $tone = 'neutral';
            $title = 'Aplikasi belum diaktivasi';
        } elseif ($days === null) {
            $label = $end ?: 'Lisensi';
            $short = $label;
            $tone = 'neutral';
            $title = 'Masa berlaku';
        } elseif ($days < 0) {
            $label = 'Habis '.$end;
            $short = 'Habis';
            $tone = 'danger';
            $title = 'Masa berlangganan telah berakhir pada '.$end;
        } elseif ($days <= 30) {
            $label = 'Sisa '.$days.' hari';
            $short = $days.' hr';
            $tone = 'warning';
            $title = 'Masa berlaku sampai '.$end;
        } else {
            $label = $end;
            $short = $license->ends_at?->format('d M');
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
    >
        <x-filament::icon icon="heroicon-m-calendar-days" class="h-3.5 w-3.5 shrink-0" />
        <span class="fi-license-topbar-label-short">{{ $short }}</span>
        <span class="fi-license-topbar-label-long">{{ $label }}</span>
    </a>
@endif
