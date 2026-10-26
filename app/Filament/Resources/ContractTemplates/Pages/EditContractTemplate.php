<?php

namespace App\Filament\Resources\ContractTemplates\Pages;

use App\Filament\Resources\ContractTemplates\ContractTemplateResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditContractTemplate extends EditRecord
{
    protected static string $resource = ContractTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make()
                ->label('Pulihkan'),
            DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (): bool => ! $this->record->is_system_default)
                ->modalHeading('Hapus template')
                ->modalDescription('Template dipindah ke terhapus. Bisa dipulihkan lewat filter Terhapus.')
                ->modalSubmitActionLabel('Ya, hapus'),
            ForceDeleteAction::make()
                ->label('Hapus permanen')
                ->visible(fn (): bool => ! $this->record->is_system_default)
                ->modalHeading('Hapus permanen')
                ->modalDescription('Template dan klausulnya hilang permanen.')
                ->modalSubmitActionLabel('Ya, hapus permanen'),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->is_system_default) {
            $data['is_system_default'] = true;
        }

        return $data;
    }
}
