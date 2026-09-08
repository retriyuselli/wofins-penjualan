<?php

namespace App\Filament\Pages;

use App\Services\AppLicenseService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ActivateLicense extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Lisensi Aplikasi';

    protected static ?string $title = 'Lisensi Aplikasi';

    protected static ?string $slug = 'license';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.activate-license';

    public string $code = '';

    public function mount(): void
    {
        $service = app(AppLicenseService::class);
        $this->code = (string) ($service->current()?->code ?? '');
        $service->status(reverify: true, force: true);
    }

    /**
     * @return array{valid: bool, status: string, message: string, license: mixed, days_remaining: ?int}
     */
    public function getLicenseStatusProperty(): array
    {
        return app(AppLicenseService::class)->status(false);
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function activate(): void
    {
        $this->validate([
            'code' => ['required', 'uuid'],
        ], [
            'code.required' => 'Item Purchase Code wajib diisi.',
            'code.uuid' => 'Format kode tidak valid. Gunakan UUID dari maknafinance.id.',
        ]);

        $domain = request()->getHost();
        $result = app(AppLicenseService::class)->activate($this->code, $domain);

        if (! $result['valid']) {
            Notification::make()
                ->title('Aktivasi gagal')
                ->body($result['message'])
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Aplikasi aktif')
            ->body(
                'Berlaku '.$result['license']?->starts_at?->format('d M Y')
                .' sampai '.$result['license']?->ends_at?->format('d M Y').'.'
            )
            ->success()
            ->send();

        $this->redirect(ProjectDashboard::getUrl());
    }
}
