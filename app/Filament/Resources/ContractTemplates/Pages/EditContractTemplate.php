<?php

namespace App\Filament\Resources\ContractTemplates\Pages;

use App\Filament\Resources\ContractTemplates\ContractTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContractTemplate extends EditRecord
{
    protected static string $resource = ContractTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->record->is_system_default),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->is_system_default) {
            $data['is_system_default'] = true;
        }

        return $data;
    }
}
