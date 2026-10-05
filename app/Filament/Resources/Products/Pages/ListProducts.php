<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Widgets\ProductOverview;
use App\Filament\Resources\SimulasiProduks\SimulasiProdukResource;
use App\Models\Company;
use Filament\Actions\Action;
use Filament\Actions\CreateAction; // Pastikan namespace ini benar jika digunakan
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Product')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembuatan Produk Baru')
                ->modalDescription('Pastikan kembali data yang akan Anda isi sudah benar sebelum melanjutkan.')
                ->modalSubmitActionLabel('Lanjutkan')
                ->modalCancelActionLabel('Batal'),

            Action::make('toko')
                ->label('Toko')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray')
                ->visible(fn (): bool => Company::showsProductsOnWeb())
                ->url(fn (): string => route('product'))
                ->openUrlInNewTab(),

            // Aksi 'penawaran' Anda yang sudah ada
            Action::make('penawaran')
                ->label('Penawaran')
                ->color('success')
                ->icon('heroicon-o-eye')
                ->url(SimulasiProdukResource::getUrl('create')) // Pastikan SimulasiProdukResource di-import atau gunakan FQCN
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'on_web' => Tab::make('Tampil di web')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('show_on_web', true)),
            'off_web' => Tab::make('Tidak tampil')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('show_on_web', false)),
        ];
    }
}
