<?php

namespace App\Filament\Resources\DataPribadis\Pages;

use App\Filament\Resources\DataPribadis\DataPribadiResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\URL;

class ListDataPribadis extends ListRecords
{
    protected static string $resource = DataPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('linkToDataPribadi')
                ->label('Link to Data Pribadi')
                ->icon('heroicon-o-link')
                ->url(fn (): string => URL::temporarySignedRoute('data-pribadi.create', now()->addDays(14)))
                ->openUrlInNewTab()
                ->color('primary'),
        ];
    }
}
