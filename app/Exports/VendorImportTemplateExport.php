<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VendorImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Vendors';
    }

    public function headings(): array
    {
        return [
            'nama',
            'telepon',
            'alamat',
            'pic',
            'status',
            'vendor_induk',
            'kategori',
            'master',
            'publish',
            'deskripsi',
            'harga_publish',
            'harga_vendor',
            'stok',
            'bank',
            'no_rekening',
            'atas_nama',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Catering Contoh',
                '',
                '',
                '',
                'vendor',
                '',
                'Lainnya',
                '',
                '',
                "1. Panggung Pelamina dan Full AC\n2. Ruang VIP dan Ruang Transit\n3. Genset dan Soundsystem standard\n4. Kursi 800 Unit\n5. Free 1 Kamar Hotel 1 Malam\n6. Free Wedding Content Creator\n7. Free Undangan Cetak 200 Pcs",
                10000000,
                8000000,
                '',
                '',
                '',
                '',
            ],
            [
                'Paket Silver',
                '',
                '',
                '',
                'vendor',
                '',
                'Lainnya',
                '',
                '',
                '',
                5000000,
                4000000,
                '',
                '',
                '',
                '',
            ],
        ];
    }
}
