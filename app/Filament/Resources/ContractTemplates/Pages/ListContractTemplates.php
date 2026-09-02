<?php

namespace App\Filament\Resources\ContractTemplates\Pages;

use App\Filament\Resources\ContractTemplates\ContractTemplateResource;
use App\Models\Company;
use App\Models\ContractTemplate;
use App\Services\ContractTemplateService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListContractTemplates extends ListRecords
{
    protected static string $resource = ContractTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copyDefault')
                ->label('Salin dari default')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $source = app(ContractTemplateService::class)->ensureSystemDefault();
                    $company = Company::query()->first();
                    $copy = ContractTemplate::copyFrom($source, [
                        'company_id' => $company?->id,
                        'is_system_default' => false,
                        'is_active' => true,
                        'name' => 'Kontrak '.($company?->company_name ?: 'Perusahaan'),
                    ]);

                    Notification::make()
                        ->title('Template disalin dari default')
                        ->success()
                        ->send();

                    return redirect(ContractTemplateResource::getUrl('edit', ['record' => $copy]));
                }),
            CreateAction::make(),
        ];
    }
}
