<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $base = (string) ($data['slug'] ?? $data['name'] ?? 'karyawan');
        $data['slug'] = Employee::generateUniqueSlug($base);

        $matches = Employee::findSameName((string) ($data['name'] ?? ''));

        if ($matches->isNotEmpty()) {
            $list = $matches
                ->map(fn (Employee $e) => $e->name.($e->email ? " ({$e->email})" : ''))
                ->implode(', ');

            Notification::make()
                ->title('Disimpan dengan nama yang sudah dipakai')
                ->body("Ada karyawan bernama sama: {$list}. Data tetap disimpan. Slug dibuat unik otomatis.")
                ->warning()
                ->send();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        Cache::forget('nav:employees:active_count');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
