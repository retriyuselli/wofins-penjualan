<?php

namespace App\Filament\Resources\Prospects\Schemas;

use App\Models\Prospect;
use App\Support\PhoneNumber;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class ProspectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Acara')
                            ->description('Jadwal mengikuti Pasal 2. Pengajian dan ngunduh mantu diisi jika ada.')
                            ->schema([
                                TextInput::make('name_event')
                                    ->label('Nama Acara')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Pernikahan Pengantin Pria & Pengantin Wanita')
                                    ->extraAttributes(['class' => 'min-w-0 max-w-full'])
                                    ->columnSpanFull(),

                                ...array_map(
                                    fn (array $event): Fieldset => self::eventScheduleFieldset($event),
                                    Prospect::pasal2EventDefinitions(),
                                ),
                            ]),

                        Section::make('Informasi Klien')
                            ->description('Detail kontak untuk pasangan')
                            ->schema([
                                Grid::make()
                                    ->columns(['default' => 1, 'md' => 2])
                                    ->schema([
                                        TextInput::make('name_cpp')
                                            ->label('Nama Calon Pengantin Pria')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('name_cpw')
                                            ->label('Nama Calon Pengantin Wanita')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                PhoneNumber::applyInput(
                                    TextInput::make('phone')->required()
                                ),

                                TextInput::make('address')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Keuangan & Manajemen')
                            ->description('Detail harga dan manajemen akun')
                            ->schema([
                                TextInput::make('total_penawaran')
                                    ->label('Total Penawaran')
                                    ->required()
                                    ->prefix('Rp. ')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/[^\d]/', '', (string) $state))
                                    ->placeholder('0')
                                    ->helperText('Masukkan total jumlah penawaran'),

                                Select::make('user_id')
                                    ->label('Manajer Akun')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(Auth::user()->id)
                                    ->helperText('Pilih manajer akun yang bertanggung jawab'),
                            ]),

                        Section::make('Catatan Tambahan')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('Masukkan catatan tambahan atau persyaratan khusus')
                                    ->rows(5)
                                    ->default('Tidak ada catatan')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /**
     * @param  array{key: string, label: string, date: string, time: string, venue: string}  $event
     */
    private static function eventScheduleFieldset(array $event): Fieldset
    {
        $locationRequiredWhen = $event['key'] === 'resepsi' ? 'date_resepsi' : null;

        return Fieldset::make($event['label'])
            ->columns(1)
            ->extraAttributes(['class' => 'min-w-0 max-w-full'])
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 3,
                ])
                    ->schema([
                        DatePicker::make($event['date'])
                            ->label('Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->extraAttributes(['class' => 'min-w-0 max-w-full']),
                        TimePicker::make($event['time'])
                            ->label('Jam')
                            ->native(false)
                            ->seconds(false)
                            ->format('H:i:s')
                            ->extraAttributes(['class' => 'min-w-0 max-w-full']),
                        TextInput::make($event['venue'])
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $locationRequiredWhen !== null && filled($get($locationRequiredWhen)))
                            ->placeholder('Nama tempat / alamat')
                            ->extraAttributes(['class' => 'min-w-0 max-w-full']),
                    ]),
            ]);
    }
}
