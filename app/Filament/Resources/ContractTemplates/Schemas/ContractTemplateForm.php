<?php

namespace App\Filament\Resources\ContractTemplates\Schemas;

use App\Models\ContractTemplate;
use App\Support\ContractTemplateDefaults;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContractTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        $placeholderHelp = implode(', ', ContractTemplateDefaults::placeholderHelp());

        return $schema
            ->components([
                Section::make('Identitas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama template')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('Kontrak Pernikahan'),

                                Select::make('company_id')
                                    ->label('Perusahaan')
                                    ->relationship('company', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Kosongkan untuk template global. Template aktif perusahaan dipakai lebih dulu daripada default sistem.'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('Hanya satu template aktif per perusahaan. Template aktif ini yang dipakai di PDF draft kontrak.'),

                                Toggle::make('is_system_default')
                                    ->label('Default sistem')
                                    ->disabled()
                                    ->dehydrated()
                                    ->visible(fn (?ContractTemplate $record): bool => (bool) $record?->is_system_default)
                                    ->helperText('Template bawaan aplikasi. Salin untuk kustomisasi, jangan dihapus.'),
                            ]),
                    ]),

                Section::make('Judul & paket')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul kontrak')
                            ->required()
                            ->maxLength(255)
                            ->default(ContractTemplateDefaults::templateAttributes()['title']),
                        TextInput::make('package_section_title')
                            ->label('Judul bagian paket')
                            ->required()
                            ->maxLength(255)
                            ->default(ContractTemplateDefaults::templateAttributes()['package_section_title']),
                        TextInput::make('package_price_label')
                            ->label('Label harga paket')
                            ->required()
                            ->maxLength(255)
                            ->default(ContractTemplateDefaults::templateAttributes()['package_price_label']),
                        TextInput::make('facilities_heading')
                            ->label('Judul rincian fasilitas')
                            ->required()
                            ->maxLength(255)
                            ->default(ContractTemplateDefaults::templateAttributes()['facilities_heading']),
                    ]),

                Section::make('Paragraf pembuka & penutup')
                    ->schema([
                        Placeholder::make('placeholder_help')
                            ->label('Placeholder')
                            ->content($placeholderHelp)
                            ->helperText('Tulis persis seperti ini di teks, termasuk kurung kurawal. Nilai diisi otomatis saat PDF dibuat.'),
                        Textarea::make('intro_pihak_pertama')
                            ->label('Intro pihak pertama')
                            ->rows(3)
                            ->default(ContractTemplateDefaults::templateAttributes()['intro_pihak_pertama']),
                        Textarea::make('intro_pihak_kedua')
                            ->label('Intro pihak kedua')
                            ->rows(2)
                            ->default(ContractTemplateDefaults::templateAttributes()['intro_pihak_kedua']),
                        RichEditor::make('intro_after_parties')
                            ->label('Paragraf setelah identitas pihak')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                            ->default(ContractTemplateDefaults::templateAttributes()['intro_after_parties']),
                        Textarea::make('closing_text')
                            ->label('Kalimat penutup')
                            ->rows(2)
                            ->default(ContractTemplateDefaults::templateAttributes()['closing_text']),
                    ]),

                Section::make('Klausul')
                    ->schema([
                        Repeater::make('sections')
                            ->relationship('sections')
                            ->label('Bagian kontrak')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Klausul baru')
                            ->addActionLabel('Tambah klausul')
                            ->default(ContractTemplateDefaults::sections())
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                                if (filled($get('key'))) {
                                                    return;
                                                }

                                                $set('key', Str::slug((string) $state, '_') ?: 'klausul_'.Str::lower(Str::random(6)));
                                            })
                                            ->columnSpan(2),
                                        Toggle::make('is_enabled')
                                            ->label('Tampil di PDF')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                                TextInput::make('key')
                                    ->label('Kunci')
                                    ->required()
                                    ->maxLength(80)
                                    ->distinct()
                                    ->helperText('Identitas unik klausul. Jangan diubah jika sudah dipakai.')
                                    ->default(fn (): string => 'klausul_'.Str::lower(Str::random(8))),
                                RichEditor::make('body')
                                    ->label('Isi')
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
