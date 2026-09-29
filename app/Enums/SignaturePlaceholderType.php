<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SignaturePlaceholderType: string implements HasLabel
{
    case Produk = 'produk';
    case DraftKontrak = 'draft_kontrak';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Produk => 'Produk',
            self::DraftKontrak => 'Draft Kontrak',
        };
    }
}
