<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Employee;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Informasi Karyawan')
                    ->tabs([
                        Tab::make('Informasi Dasar')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Detail Personal')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('user_id')
                                                    ->relationship(
                                                        'user',
                                                        'name',
                                                        fn (Builder $query) => $query
                                                            ->where(function (Builder $q): void {
                                                                $q->whereNull('status')
                                                                    ->orWhere('status', 'active');
                                                            })
                                                    )
                                                    ->label('Akun Pengguna')
                                                    ->helperText('Pilih akun yang sudah terdaftar. Nama, email, dan kontak akan terisi otomatis. Akun baru dibuat di menu Pengguna.')
                                                    ->preload()
                                                    ->searchable()
                                                    ->nullable()
                                                    ->unique(
                                                        table: Employee::class,
                                                        column: 'user_id',
                                                        ignoreRecord: true,
                                                    )
                                                    ->validationMessages([
                                                        'unique' => 'Akun ini sudah terhubung ke karyawan lain.',
                                                    ])
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Set $set, string $operation): void {
                                                        if ($operation !== 'create' || blank($state)) {
                                                            return;
                                                        }

                                                        self::fillFromUser((int) $state, $set);
                                                    })
                                                    ->columnSpanFull(),

                                                TextInput::make('name')
                                                    ->required()
                                                    ->placeholder('Nama lengkap (depan dan belakang)')
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Set $set, ?Employee $record) {
                                                        $set('slug', Employee::generateUniqueSlug(
                                                            (string) $state,
                                                            $record?->id
                                                        ));

                                                        $matches = Employee::findSameName(
                                                            (string) $state,
                                                            $record?->id
                                                        );

                                                        if ($matches->isEmpty()) {
                                                            return;
                                                        }

                                                        $list = $matches
                                                            ->map(fn (Employee $e) => $e->name.($e->email ? " ({$e->email})" : ''))
                                                            ->implode(', ');

                                                        Notification::make()
                                                            ->title('Nama sudah dipakai')
                                                            ->body("Ada karyawan dengan nama sama: {$list}. Anda tetap boleh menyimpan — pastikan ini memang orang berbeda.")
                                                            ->warning()
                                                            ->persistent()
                                                            ->send();
                                                    })
                                                    ->helperText(function (Get $get, ?Employee $record): string {
                                                        $matches = Employee::findSameName(
                                                            (string) $get('name'),
                                                            $record?->id
                                                        );

                                                        if ($matches->isEmpty()) {
                                                            return 'Nama boleh sama. Identitas unik memakai email.';
                                                        }

                                                        $list = $matches
                                                            ->take(3)
                                                            ->map(fn (Employee $e) => $e->name.($e->email ? " ({$e->email})" : ''))
                                                            ->implode(', ');

                                                        return "Peringatan: nama sama sudah ada — {$list}. Tetap boleh disimpan.";
                                                    }),

                                                TextInput::make('slug')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->maxLength(255)
                                                    ->helperText('Otomatis unik (nama sama → slug-1, slug-2, dst).'),

                                                DatePicker::make('date_of_birth')
                                                    ->label('Tanggal Lahir')
                                                    ->required()
                                                    ->maxDate(now()->subYears(18))
                                                    ->displayFormat('d M Y'),

                                                FileUpload::make('photo')
                                                    ->label('Foto Profil')
                                                    ->image()
                                                    ->openable()
                                                    ->downloadable()
                                                    ->directory('employee-photos'),
                                            ]),
                                    ]),

                                Section::make('Informasi Kontak')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->unique(
                                                        table: Employee::class,
                                                        column: 'email',
                                                        ignoreRecord: true,
                                                    )
                                                    ->validationMessages([
                                                        'unique' => 'Email ini sudah dipakai karyawan lain.',
                                                    ])
                                                    ->helperText('Wajib unik — dipakai sebagai identitas karyawan.')
                                                    ->maxLength(255),

                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(20)
                                                    ->prefix('+62')
                                                    ->telRegex('/^[0-9]{9,15}$/')
                                                    ->placeholder('8xxxxxxxxx'),

                                                TextInput::make('instagram')
                                                    ->prefix('@')
                                                    ->maxLength(255),

                                                Textarea::make('address')
                                                    ->required()
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Detail Kepegawaian')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Posisi & Peran')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('position')
                                                    ->required()
                                                    ->options([
                                                        'Account Manager' => 'Account Manager',
                                                        'Event Manager' => 'Event Manager',
                                                        'Crew' => 'Crew',
                                                        'Finance' => 'Finance',
                                                        'Founder' => 'Founder',
                                                        'Co Founder' => 'Co Founder',
                                                        'Direktur' => 'Direktur',
                                                        'Wakil Direktur' => 'Wakil Direktur',
                                                        'Other' => 'Other',
                                                    ])
                                                    ->searchable(),

                                                DatePicker::make('date_of_join')
                                                    ->label('Tanggal Bergabung')
                                                    ->required()
                                                    ->displayFormat('d M Y')
                                                    ->default(now()),

                                                DatePicker::make('date_of_out')
                                                    ->label('Tanggal Berhenti')
                                                    ->displayFormat('d M Y')
                                                    ->minDate(fn (Get $get) => $get('date_of_join')),
                                            ]),
                                    ]),

                                Section::make('Kompensasi & Perbankan')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('salary')
                                                    ->label('Gaji Pokok')
                                                    ->required()
                                                    ->prefix('Rp. ')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/[^\d]/', '', (string) $state))
                                                    ->placeholder('0'),

                                                TextInput::make('bank_name')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('no_rek')
                                                    ->label('Nomor Rekening')
                                                    ->required()
                                                    ->numeric()
                                                    ->minLength(10)
                                                    ->maxLength(20),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Dokumen & Catatan')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        FileUpload::make('kontrak')
                                            ->label('Kontrak Kerja')
                                            ->directory('employee-contracts')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->openable()
                                            ->downloadable(),

                                        Textarea::make('note')
                                            ->label('Catatan Tambahan')
                                            ->placeholder('Catatan khusus tentang karyawan ini')
                                            ->rows(3),

                                        TextInput::make('created_at_display')
                                            ->label('Dibuat')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function ($component, $state, ?Employee $record): void {
                                                $component->state($record?->created_at?->diffForHumans());
                                            })
                                            ->hidden(fn (?Employee $record) => $record === null),

                                        TextInput::make('updated_at_display')
                                            ->label('Diperbarui')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function ($component, $state, ?Employee $record): void {
                                                $component->state($record?->updated_at?->diffForHumans());
                                            })
                                            ->hidden(fn (?Employee $record) => $record === null),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function fillFromUser(int $userId, Set $set): void
    {
        $user = User::query()->find($userId);
        if (! $user) {
            return;
        }

        $set('name', $user->name);
        $set('slug', Employee::generateUniqueSlug((string) $user->name));

        if (filled($user->email)) {
            $set('email', $user->email);
        }

        $phone = self::normalizePhone($user->phone_number);
        if ($phone) {
            $set('phone', $phone);
        }

        if (filled($user->address)) {
            $set('address', $user->address);
        }

        if ($user->date_of_birth) {
            $set('date_of_birth', $user->date_of_birth);
        }

        if ($user->hire_date) {
            $set('date_of_join', $user->hire_date);
        }

        if (filled($user->gaji_pokok_base)) {
            $set('salary', $user->gaji_pokok_base);
        }
    }

    private static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        }

        $digits = ltrim($digits, '0');

        return $digits !== '' ? $digits : null;
    }
}
