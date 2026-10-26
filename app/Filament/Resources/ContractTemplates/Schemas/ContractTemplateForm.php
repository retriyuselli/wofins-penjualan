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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ContractTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        $placeholderHelp = implode(', ', ContractTemplateDefaults::placeholderHelp());
        $spkDefaults = ContractTemplateDefaults::spkTemplateAttributes();

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
                                    ->default($spkDefaults['name']),

                                Select::make('company_id')
                                    ->label('Perusahaan')
                                    ->relationship('company', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Kosongkan untuk template global. Template aktif perusahaan dipakai lebih dulu. Default sistem tetap memakai layout kontrak semula.'),
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
                            ->default($spkDefaults['title']),
                        TextInput::make('package_section_title')
                            ->label('Judul bagian paket')
                            ->required()
                            ->maxLength(255)
                            ->default($spkDefaults['package_section_title']),
                        TextInput::make('package_price_label')
                            ->label('Label harga paket')
                            ->required()
                            ->maxLength(255)
                            ->default($spkDefaults['package_price_label']),
                        TextInput::make('facilities_heading')
                            ->label('Judul bagian fasilitas')
                            ->helperText('Default: DENGAN RINCIAN FASILITAS SEBAGAI BERIKUT :. SPK: Pasal 5 — Hak Pihak Kedua / Fasilitas Jasa Pernikahan')
                            ->required()
                            ->maxLength(255)
                            ->default($spkDefaults['facilities_heading']),
                    ]),

                Section::make('Paragraf pembuka & penutup')
                    ->schema([
                        Placeholder::make('placeholder_help')
                            ->label('Placeholder')
                            ->content(new HtmlString(
                                '<p class="max-w-full min-w-0 break-all text-sm leading-relaxed text-gray-600 dark:text-gray-400">'.
                                e($placeholderHelp).
                                '</p>'
                            ))
                            ->helperText('Tulis persis seperti ini di teks, termasuk kurung kurawal. Nilai diisi otomatis saat PDF dibuat. Lokasi acara diisi dari Pasal 2, bukan dari paragraf ini.'),
                        Textarea::make('intro_pihak_pertama')
                            ->label('Intro pihak pertama')
                            ->rows(3)
                            ->default($spkDefaults['intro_pihak_pertama']),
                        Textarea::make('intro_pihak_kedua')
                            ->label('Intro pihak kedua')
                            ->rows(2)
                            ->default($spkDefaults['intro_pihak_kedua']),
                        RichEditor::make('intro_after_parties')
                            ->label('Paragraf setelah identitas pihak')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                            ->default($spkDefaults['intro_after_parties']),
                        Textarea::make('closing_text')
                            ->label('Kalimat penutup')
                            ->rows(2)
                            ->default($spkDefaults['closing_text']),
                    ]),

                Section::make('Tanda tangan')
                    ->description('Tampil di akhir PDF. Nama dan kota diisi otomatis dari placeholder saat dokumen dibuat.')
                    ->schema([
                        TextInput::make('signature_place')
                            ->label('Baris tempat & tanggal')
                            ->maxLength(255)
                            ->default($spkDefaults['signature_place'])
                            ->helperText('Contoh: {{company_city}}, {{contract_date_short}}')
                            ->extraAttributes(['class' => 'max-w-full min-w-0']),
                        Repeater::make('signatures')
                            ->label('Kolom tanda tangan')
                            ->default($spkDefaults['signatures'])
                            ->minItems(1)
                            ->maxItems(3)
                            ->reorderable()
                            ->addActionLabel('Tambah kolom')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 2])
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Label atas')
                                            ->maxLength(255)
                                            ->placeholder('Direktur')
                                            ->helperText('Kosongkan: pakai Title TTD dari simulasi (kolom klien).'),
                                        TextInput::make('name')
                                            ->label('Nama di garis')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('{{owner_name}}')
                                            ->helperText('{{owner_name}}, {{prospect_cpw}}, atau {{name_ttd}}. Kosong → Name TTD simulasi.'),
                                    ]),
                                Grid::make(['default' => 1, 'md' => 2])
                                    ->schema([
                                        TextInput::make('role')
                                            ->label('Jabatan di bawah garis')
                                            ->maxLength(255)
                                            ->placeholder('Kosongkan: Title TTD simulasi')
                                            ->helperText('Kosongkan untuk memakai Title TTD dari simulasi pada kolom klien.'),
                                        Toggle::make('stamp')
                                            ->label('Stempel TTD owner')
                                            ->helperText('Gambar tanda tangan owner di kolom ini.')
                                            ->inline(false)
                                            ->default(false),
                                    ]),
                            ]),
                    ]),

                Section::make('Klausul')
                    ->schema([
                        Repeater::make('sections')
                            ->relationship('sections')
                            ->label('Bagian kontrak')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => trim(($state['title'] ?? 'Pasal').' '.($state['keterangan'] ?? '')) ?: 'Klausul baru')
                            ->addActionLabel('Tambah klausul')
                            ->default(ContractTemplateDefaults::spkSections())
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Pasal')
                                            ->placeholder('Pasal 1')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                                if (filled($get('key'))) {
                                                    return;
                                                }

                                                $set('key', Str::slug((string) $state, '_') ?: 'klausul_'.Str::lower(Str::random(6)));
                                            }),
                                        TextInput::make('keterangan')
                                            ->label('Keterangan pasal')
                                            ->placeholder('Maksud Kerjasama')
                                            ->maxLength(255)
                                            ->columnSpan(1),
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
