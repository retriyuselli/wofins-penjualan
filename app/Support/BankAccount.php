<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;

final class BankAccount
{
    public static function digits(mixed $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $raw) ?? '';

        return $digits !== '' ? $digits : null;
    }

    public static function applyInput(TextInput $input): TextInput
    {
        return $input
            ->inputMode('numeric')
            ->maxLength(32)
            ->rule('regex:/^[0-9\-\s]+$/')
            ->dehydrateStateUsing(fn ($state) => self::digits($state));
    }
}
