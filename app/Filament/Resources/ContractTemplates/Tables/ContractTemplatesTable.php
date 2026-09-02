<?php

namespace App\Filament\Resources\ContractTemplates\Tables;

use App\Filament\Resources\ContractTemplates\ContractTemplateResource;
use App\Models\ContractTemplate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContractTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('company.company_name')
                    ->label('Perusahaan')
                    ->placeholder('Global')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul kontrak')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('is_system_default')
                    ->label('Default')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('salin')
                    ->label('Salin')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (ContractTemplate $record) {
                        $copy = ContractTemplate::copyFrom($record, [
                            'is_system_default' => false,
                            'is_active' => false,
                        ]);

                        Notification::make()
                            ->title('Template disalin')
                            ->success()
                            ->send();

                        return redirect(ContractTemplateResource::getUrl('edit', ['record' => $copy]));
                    }),
                DeleteAction::make()
                    ->visible(fn (ContractTemplate $record): bool => ! $record->is_system_default)
                    ->requiresConfirmation(),
            ])
            ->defaultSort('id')
            ->emptyStateHeading('Belum ada template kontrak')
            ->emptyStateDescription('Buat template baru atau salin dari default sistem.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Buat template')
                    ->url(fn (): string => ContractTemplateResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
}
