<?php

namespace App\Filament\Resources\ContractTemplates\Pages;

use App\Filament\Resources\ContractTemplates\ContractTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContractTemplate extends CreateRecord
{
    protected static string $resource = ContractTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_system_default'] = false;

        return $data;
    }
}
