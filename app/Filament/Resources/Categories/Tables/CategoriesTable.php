<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('id')
                    ->label('SKU/ID'),
                TextColumn::make('slug')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('deleted_at')
                    ->label('Status')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Aktif')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state ? 'Dihapus' : 'Aktif'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Filter Status')
                    ->placeholder('Semua Data')
                    ->trueLabel('Hanya yang Dihapus')
                    ->falseLabel('Tanpa yang Dihapus'),
                TernaryFilter::make('is_active')
                    ->label('Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    ViewAction::make(),
                    RestoreAction::make()
                        ->successNotificationTitle('Kategori berhasil dipulihkan'),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Kategori')
                        ->modalDescription('Data akan dipindahkan ke trash dan dapat dipulihkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->successNotificationTitle('Kategori berhasil dihapus'),
                    ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Kategori')
                        ->modalDescription('Data tidak dapat dipulihkan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen')
                        ->successNotificationTitle('Kategori berhasil dihapus permanen'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Kategori terpilih berhasil dipulihkan'),
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Kategori Terpilih')
                        ->modalDescription('Data akan dipindahkan ke trash dan dapat dipulihkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->successNotificationTitle('Kategori terpilih berhasil dihapus'),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Kategori Terpilih')
                        ->modalDescription('Data tidak dapat dipulihkan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen Semua')
                        ->successNotificationTitle('Kategori terpilih berhasil dihapus permanen'),
                ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('Belum ada kategori')
            ->emptyStateDescription('Buat kategori pertama untuk memulai.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Buat Kategori')
                    ->url(CategoryResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
}
