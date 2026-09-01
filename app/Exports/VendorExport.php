<?php

namespace App\Exports;

use App\Models\Vendor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private Collection $vendors)
    {
    }

    public function title(): string
    {
        return 'Vendors';
    }

    public function collection(): Collection
    {
        return $this->vendors;
    }

    public function headings(): array
    {
        return (new VendorImportTemplateExport)->headings();
    }

    /**
     * @param  Vendor  $vendor
     */
    public function map($vendor): array
    {
        $status = $vendor->status;
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) ($status ?? 'vendor');

        return [
            $vendor->name,
            $vendor->phone,
            $vendor->address,
            $vendor->pic_name,
            $statusValue,
            $vendor->parent?->name,
            $vendor->category?->name,
            $vendor->is_master ? 'ya' : 'tidak',
            $vendor->is_published ? 'ya' : 'tidak',
            $this->descriptionToPlain($vendor->description),
            $vendor->harga_publish,
            $vendor->harga_vendor,
            $vendor->stock,
            $vendor->bank_name,
            $vendor->bank_account,
            $vendor->account_holder,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('J')->getAlignment()->setWrapText(true);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function descriptionToPlain(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $html, $matches) && $matches[1] !== []) {
            $lines = [];
            foreach ($matches[1] as $index => $item) {
                $text = trim(html_entity_decode(strip_tags($item), ENT_QUOTES, 'UTF-8'));
                if ($text !== '') {
                    $lines[] = ($index + 1).'. '.$text;
                }
            }

            return $lines === [] ? null : implode("\n", $lines);
        }

        $text = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace("/[ \t]+/u", ' ', $text) ?? $text);
        $text = trim(preg_replace("/\n{3,}/", "\n\n", $text) ?? $text);

        return $text === '' ? null : $text;
    }
}
