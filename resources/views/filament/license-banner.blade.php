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
        x-on:keydown.escape.window="open && dismiss()"
    >
        <template x-teleport="body">
            <div
                x-show="open"
                x-cloak
                x-transition.opacity.duration.150ms
                role="presentation"
                style="position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 1rem;"
            >
                <div
                    style="position: absolute; inset: 0; background: rgba(3, 7, 18, 0.5);"
                    x-on:click="dismiss()"
                ></div>

                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="license-notice-title"
                    style="position: relative; z-index: 1; width: 22rem; max-width: calc(100vw - 2rem); border-radius: 0.75rem; background: #fff; padding: 1.25rem; box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);"
                    class="dark:bg-gray-900 dark:ring-1 dark:ring-white/10"
                >
                    <h2 id="license-notice-title" class="text-base font-semibold text-gray-950 dark:text-white">
                        Masa berlangganan hampir berakhir
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        WOFINS berakhir
                        <strong class="font-semibold text-gray-950 dark:text-white">{{ $license->ends_at?->translatedFormat('d M Y') }}</strong>
                        (sisa {{ $days }} hari). Perpanjang agar admin tetap bisa dipakai.
                    </p>

                    <div class="mt-5 grid gap-2">
                        <x-filament::button tag="a" :href="$contactUrl" target="_blank" x-on:click="dismiss()">
                            Perpanjang di wofins.id
                        </x-filament::button>
                        <x-filament::button color="gray" tag="a" :href="$licenseUrl" x-on:click="dismiss()">
                            Lihat lisensi
                        </x-filament::button>
                        <x-filament::button color="gray" x-on:click="dismiss()">
                            Mengerti
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
