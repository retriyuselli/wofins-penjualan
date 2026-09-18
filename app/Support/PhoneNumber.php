<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;

/**
 * Simpan E.164 (digit, tanpa +). Default Indonesia (62).
 * Nomor negara lain wajib diawali + atau 00.
 */
final class PhoneNumber
{
    public const DEFAULT_COUNTRY = '62';

    /**
     * Kode negara yang dikenali (prefix terpanjang dulu).
     * Dipakai tampilan dan membedakan nomor asing yang sudah tersimpan E.164.
     *
     * @var list<string>
     */
    private const CALLING_CODES = [
        '971', '966', '886', '852', '84', '82', '81', '66', '65', '63', '61', '60',
        '49', '44', '39', '34', '33', '31', '91', '86', '62', '1',
    ];

    public static function digits(?string $raw): string
    {
        return preg_replace('/\D+/', '', (string) $raw) ?? '';
    }

    public static function isExplicitInternational(?string $raw): bool
    {
        $trimmed = trim((string) $raw);

        return str_starts_with($trimmed, '+') || str_starts_with($trimmed, '00');
    }

    /** E.164 tanpa plus: 62812… / 6591… */
    public static function e164(?string $raw, string $defaultCountry = self::DEFAULT_COUNTRY): ?string
    {
        $trimmed = trim((string) $raw);
        if ($trimmed === '') {
            return null;
        }

        $explicit = self::isExplicitInternational($trimmed);
        $digits = self::digits($trimmed);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if ($explicit) {
            return $digits !== '' ? $digits : null;
        }

        if (str_starts_with($digits, $defaultCountry) && strlen($digits) >= 10) {
            return $digits;
        }

        $foreign = self::storedForeignE164($digits, $defaultCountry);
        if ($foreign) {
            return $foreign;
        }

        if (str_starts_with($digits, '0')) {
            $national = ltrim($digits, '0');

            return $national !== '' ? $defaultCountry.$national : null;
        }

        return $defaultCountry.ltrim($digits, '0');
    }

    /** Subscriber tanpa kode negara (untuk kompatibilitas data lama 812…). */
    public static function national(?string $raw, string $defaultCountry = self::DEFAULT_COUNTRY): ?string
    {
        $e164 = self::e164($raw, $defaultCountry);
        if (! $e164) {
            return null;
        }

        $code = self::callingCode($e164);

        return substr($e164, strlen($code)) ?: null;
    }

    public static function isValid(?string $raw): bool
    {
        $e164 = self::e164($raw);

        return $e164 !== null && (bool) preg_match('/^[1-9][0-9]{7,14}$/', $e164);
    }

    public static function display(?string $raw): string
    {
        $e164 = self::e164($raw);
        if (! $e164) {
            return '-';
        }

        $code = self::callingCode($e164);
        $rest = substr($e164, strlen($code));

        return $rest !== '' ? '+'.$code.' '.$rest : '+'.$e164;
    }

    public static function inputDisplay(?string $raw): ?string
    {
        $e164 = self::e164($raw);
        if (! $e164) {
            return null;
        }

        $code = self::callingCode($e164);
        $rest = substr($e164, strlen($code));
        if ($rest === '') {
            return '+'.$e164;
        }

        if ($code === self::DEFAULT_COUNTRY) {
            return '0'.$rest;
        }

        return '+'.$code.$rest;
    }

    public static function waMe(?string $raw, ?string $text = null): ?string
    {
        $e164 = self::e164($raw);
        if (! $e164) {
            return null;
        }

        $url = 'https://wa.me/'.$e164;
        if (filled($text)) {
            $url .= '?text='.rawurlencode($text);
        }

        return $url;
    }

    public static function callingCode(string $e164): string
    {
        foreach (self::sortedCallingCodes() as $code) {
            if (str_starts_with($e164, $code) && strlen($e164) > strlen($code)) {
                return $code;
            }
        }

        return self::DEFAULT_COUNTRY;
    }

    public static function applyInput(TextInput $input): TextInput
    {
        return $input
            ->tel()
            ->placeholder('0812xxxxxxx atau +65 9123…')
            ->helperText('Indonesia: 08xx / 8xx / +62. Negara lain wajib diawali +kode negara, contoh +65.')
            ->maxLength(24)
            ->formatStateUsing(fn ($state) => self::inputDisplay(is_string($state) ? $state : null))
            ->dehydrateStateUsing(fn ($state) => filled($state) ? self::e164((string) $state) : null)
            ->rule(function () {
                return function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }
                    if (! self::isValid((string) $value)) {
                        $fail('Nomor tidak valid. Indonesia tanpa +; negara lain pakai +kode negara.');
                    }
                };
            });
    }

    private static function storedForeignE164(string $digits, string $defaultCountry): ?string
    {
        // HP Indonesia selalu 8… — jangan dikira Jepang/Korea/China.
        if (str_starts_with($digits, '8') || str_starts_with($digits, $defaultCountry)) {
            return null;
        }

        foreach (self::sortedCallingCodes() as $code) {
            if ($code === $defaultCountry) {
                continue;
            }
            if (! str_starts_with($digits, $code)) {
                continue;
            }
            $subscriber = substr($digits, strlen($code));
            if (strlen($subscriber) >= 6 && strlen($digits) >= 8 && strlen($digits) <= 15) {
                return $digits;
            }
        }

        return null;
    }

    /** @return list<string> */
    private static function sortedCallingCodes(): array
    {
        $codes = self::CALLING_CODES;
        usort($codes, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return $codes;
    }
}
