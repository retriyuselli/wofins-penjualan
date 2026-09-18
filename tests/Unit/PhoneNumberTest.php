<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    /**
     * @dataProvider e164Inputs
     */
    public function test_e164_from_mixed_formats(string $raw, string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::e164($raw));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function e164Inputs(): array
    {
        return [
            'leading zero' => ['081234567890', '6281234567890'],
            'national' => ['81234567890', '6281234567890'],
            'country digits' => ['6281234567890', '6281234567890'],
            'plus id' => ['+62 812-3456-7890', '6281234567890'],
            'landline' => ['021 5551234', '62215551234'],
            'singapore plus' => ['+65 9123 4567', '6591234567'],
            'singapore 00' => ['006591234567', '6591234567'],
            'stored singapore' => ['6591234567', '6591234567'],
            'malaysia' => ['+60 12-345 6789', '60123456789'],
            'us' => ['+1 415 555 2671', '14155552671'],
        ];
    }

    public function test_display_uses_actual_country_code(): void
    {
        $this->assertSame('+62 81234567890', PhoneNumber::display('081234567890'));
        $this->assertSame('+62 81234567890', PhoneNumber::display('6281234567890'));
        $this->assertSame('+65 91234567', PhoneNumber::display('+65 9123 4567'));
        $this->assertSame('+1 4155552671', PhoneNumber::display('+1 415 555 2671'));
    }

    public function test_wa_me_and_input_roundtrip(): void
    {
        $this->assertSame('https://wa.me/6281234567890', PhoneNumber::waMe('0812-3456-7890'));
        $this->assertSame('https://wa.me/6591234567', PhoneNumber::waMe('+65 9123 4567'));
        $this->assertSame('081234567890', PhoneNumber::inputDisplay('6281234567890'));
        $this->assertSame('+6591234567', PhoneNumber::inputDisplay('+65 9123 4567'));
    }

    public function test_foreign_without_plus_is_not_forced_to_indonesia_when_already_e164(): void
    {
        $this->assertSame('6591234567', PhoneNumber::e164('6591234567'));
        $this->assertSame('+65 91234567', PhoneNumber::display('6591234567'));
    }
}
