<?php

namespace App\Filament\Resources\ContractTemplates\Tables;

use App\Filament\Resources\ContractTemplates\ContractTemplateResource;
use App\Models\ContractTemplate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
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
            ->filters([
                TrashedFilter::make()
                    ->label('Terhapus')
                    ->placeholder('Tanpa terhapus')
                    ->trueLabel('Termasuk terhapus')
                    ->falseLabel('Hanya terhapus'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->visible(fn (ContractTemplate $record): bool => ! $record->trashed()),
                Action::make('salin')
                    ->label('Salin')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->visible(fn (ContractTemplate $record): bool => ! $record->trashed())
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
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->successNotificationTitle('Template dipulihkan'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (ContractTemplate $record): bool => ! $record->is_system_default && ! $record->trashed())
                    ->requiresConfirmation()
                    ->modalHeading('Hapus template')
                    ->modalDescription('Template dipindah ke terhapus. Bisa dipulihkan lewat filter Terhapus.')
                    ->modalSubmitActionLabel('Ya, hapus')
                    ->successNotificationTitle('Template dipindah ke terhapus'),
                ForceDeleteAction::make()
                    ->label('Hapus permanen')
                    ->visible(fn (ContractTemplate $record): bool => ! $record->is_system_default && $record->trashed())
                    ->requiresConfirmation()
                    ->modalHeading('Hapus permanen')
                    ->modalDescription('Template dan klausulnya hilang permanen.')
                    ->modalSubmitActionLabel('Ya, hapus permanen')
                    ->successNotificationTitle('Template dihapus permanen'),
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
