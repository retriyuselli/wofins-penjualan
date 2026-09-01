<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Exports\VendorExport;
use App\Exports\VendorImportTemplateExport;
use App\Filament\Resources\Vendors\VendorResource;
use App\Filament\Resources\Vendors\Widgets\VendorOverview;
use App\Imports\VendorsImport;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('downloadTemplate')
                    ->label('Template Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(fn () => Excel::download(
                        new VendorImportTemplateExport,
                        'template-import-vendor.xlsx'
                    )),
                Action::make('importExcel')
                    ->label('Upload Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->visible(fn (): bool => Gate::allows('create', Vendor::class))
                    ->schema([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                                'text/plain',
                                'application/octet-stream',
                            ])
                            ->disk('local')
                            ->directory('vendor-imports')
                            ->maxSize(10240)
                            ->required()
                            ->helperText('Unggah .xlsx / .xls / .csv. Kolom wajib: nama, harga_publish, harga_vendor. Kolom lain boleh kosong (status default vendor, kategori default Lainnya).'),
                    ])
                    ->modalHeading('Import Vendor dari Excel')
                    ->modalSubmitActionLabel('Import')
                    ->action(function (array $data): void {
                        $relativePath = null;

                        try {
                            $path = $this->resolveImportPath($data['file'] ?? null, $relativePath);
                            $import = new VendorsImport;
                            Excel::import($import, $path);

                            Cache::forget('nav:vendors:count');

                            $body = "Berhasil import {$import->getImportedCount()} vendor.";
                            if ($import->getSkippedCount() > 0) {
                                $body .= " Dilewati {$import->getSkippedCount()} baris.";
                            }
                            if ($import->getErrors() !== []) {
                                $preview = implode("\n", array_slice($import->getErrors(), 0, 8));
                                if (count($import->getErrors()) > 8) {
                                    $preview .= "\n…";
                                }
                                $body .= "\n\n".$preview;
                            }

                            $notification = Notification::make()
                                ->title('Import Excel selesai')
                                ->body($body);

                            if ($import->getImportedCount() === 0) {
                                $notification->danger()->persistent();
                            } elseif ($import->getSkippedCount() > 0) {
                                $notification->warning()->persistent();
                            } else {
                                $notification->success();
                            }

                            $notification->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Import Excel gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        } finally {
                            if ($relativePath && Storage::disk('local')->exists($relativePath)) {
                                Storage::disk('local')->delete($relativePath);
                            }
                        }
                    }),
                Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (): bool => Gate::allows('viewAny', Vendor::class))
                    ->action(function () {
                        $vendors = $this->getFilteredTableQuery()
                            ->with(['category', 'parent'])
                            ->orderBy('name')
                            ->get();

                        if ($vendors->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak ada data')
                                ->body('Tidak ada vendor yang cocok dengan filter saat ini.')
                                ->send();

                            return;
                        }

                        return Excel::download(
                            new VendorExport($vendors),
                            'vendors-'.now()->format('YmdHis').'.xlsx'
                        );
                    }),
            ])
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->button()
                ->color('gray'),
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('New Vendor'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            VendorOverview::class,
        ];
    }

    private function resolveImportPath(mixed $file, ?string &$relativePath): string
    {
        if (is_array($file)) {
            $file = reset($file);
        }

        if (is_object($file) && method_exists($file, 'getRealPath')) {
            $realPath = $file->getRealPath();
            if ($realPath === false || $realPath === '') {
                throw new RuntimeException('File upload tidak valid. Silakan unggah ulang.');
            }

            return $realPath;
        }

        $relativePath = ltrim((string) $file, '/');
        $candidates = array_values(array_unique(array_filter([
            $relativePath,
            'vendor-imports/'.basename($relativePath),
        ])));

        foreach ($candidates as $candidate) {
            if (Storage::disk('local')->exists($candidate)) {
                $relativePath = $candidate;

                return Storage::disk('local')->path($candidate);
            }
        }

        throw new RuntimeException('File upload tidak ditemukan. Silakan unggah ulang.');
    }
}
