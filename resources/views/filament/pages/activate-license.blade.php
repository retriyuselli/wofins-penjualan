<x-filament-panels::page>
    @php
        $status = $this->licenseStatus;
        $license = $status['license'] ?? null;
        $valid = $status['valid'] ?? false;
        $days = $status['days_remaining'] ?? null;
    @endphp

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Item Purchase Code</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Tempelkan kode dari maknafinance.id setelah pembayaran. Tanggal mulai dan selesai diisi otomatis dari server, tidak bisa diubah di sini.
            </p>

            <form wire:submit="activate" class="mt-6 space-y-4">
                <div>
                    <label for="code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Item Purchase Code</label>
                    <input
                        id="code"
                        type="text"
                        wire:model="code"
                        placeholder="ac15dc7f-6067-4f4a-b294-4344e44fcf59"
                        class="w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        autocomplete="off"
                    />
                    @error('code')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-filament::button type="submit" icon="heroicon-o-key">
                    Aktivasi / Perpanjang
                </x-filament::button>
            </form>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Masa berlaku</h2>

            @if ($license)
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-medium {{ $valid ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $valid ? 'Aktif' : ($status['message'] ?? 'Tidak aktif') }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Perusahaan</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $license->company_name ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Tanggal mulai</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $license->starts_at?->translatedFormat('d M Y') ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Tanggal selesai</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $license->ends_at?->translatedFormat('d M Y') ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Sisa waktu</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">
                            @if ($days === null)
                                -
                            @elseif ($days < 0)
                                Habis {{ abs($days) }} hari yang lalu
                            @else
                                {{ $days }} hari
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Domain</dt>
                        <dd class="font-medium text-gray-950 dark:text-white">{{ $license->domain ?: request()->getHost() }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada lisensi. Setelah pembayaran di maknafinance.id, tempel Item Purchase Code di formulir sebelah.
                </p>
            @endif

            <p class="mt-6 text-sm text-gray-500">
                Perpanjang di
                <a href="{{ config('wofins.license.contact_url') }}" target="_blank" class="text-primary-600 hover:underline">maknafinance.id</a>
                atau
                <a href="{{ config('wofins.license.contact_whatsapp') }}" target="_blank" class="text-primary-600 hover:underline">WhatsApp</a>.
            </p>
        </div>
    </div>
</x-filament-panels::page>
