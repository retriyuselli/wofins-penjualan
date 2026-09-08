@php
    $status = app(\App\Services\AppLicenseService::class)->status(false);
    $license = $status['license'] ?? null;
    $valid = $status['valid'] ?? true;
    $days = $status['days_remaining'] ?? null;
    $onLicensePage = request()->routeIs('filament.admin.pages.license');
    $show = config('wofins.license.enabled')
        && $license
        && $valid
        && $days !== null
        && $days <= 30
        && ! $onLicensePage;
    $storageKey = $show ? 'wofins-license-notice-'.$license->ends_at->toDateString() : null;
    $licenseUrl = \App\Filament\Pages\ActivateLicense::getUrl();
    $contactUrl = config('wofins.license.contact_url');
@endphp

@if ($show)
    <div
        x-data="{
            key: @js($storageKey),
            open: false,
            init() {
                this.open = window.sessionStorage.getItem(this.key) !== '1'
            },
            dismiss() {
                window.sessionStorage.setItem(this.key, '1')
                this.open = false
            }
        }"
        x-cloak
        x-on:keydown.escape.window="open && dismiss()"
    >
        <div
            x-show="open"
            x-transition.opacity
            class="fi-license-notice-overlay fixed inset-0 z-[90] flex items-center justify-center p-4 sm:p-6"
            style="display: none;"
        >
            <div
                class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75"
                x-on:click="dismiss()"
            ></div>

            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="license-notice-title"
                class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-400/10 dark:text-warning-400">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 id="license-notice-title" class="text-base font-semibold text-gray-950 dark:text-white">
                            Masa berlangganan hampir berakhir
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            WOFINS berakhir
                            <strong class="font-semibold text-gray-950 dark:text-white">{{ $license->ends_at?->translatedFormat('d M Y') }}</strong>
                            (sisa {{ $days }} hari). Perpanjang agar admin tetap bisa dipakai.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-filament::button color="gray" x-on:click="dismiss()">
                        Mengerti
                    </x-filament::button>
                    <x-filament::button color="gray" tag="a" :href="$licenseUrl" x-on:click="dismiss()">
                        Lihat lisensi
                    </x-filament::button>
                    <x-filament::button tag="a" :href="$contactUrl" target="_blank" x-on:click="dismiss()">
                        Perpanjang di wofins.id
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
@endif
