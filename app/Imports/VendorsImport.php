<?php

namespace App\Imports;

use App\Enums\StatusVendor;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VendorsImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, string> */
    private array $errors = [];

    private int $imported = 0;

    private int $skipped = 0;

    public function collection(Collection $rows): void
    {
        $prepared = [];

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;
            $data = $this->normalizeRow($row->toArray());

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $prepared[] = ['row' => $excelRow, 'data' => $data];
        }

        usort($prepared, function (array $a, array $b): int {
            return $this->statusPriority($a['data']['status']) <=> $this->statusPriority($b['data']['status']);
        });

        foreach ($prepared as $item) {
            $this->importRow($item['row'], $item['data']);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $nama = $this->cell($row, 'nama', 'name');
        $status = $this->normalizeStatus($this->cell($row, 'status'));

        return [
            'nama' => is_string($nama) ? trim($nama) : (filled($nama) ? trim((string) $nama) : null),
            'telepon' => $this->normalizePhone($this->cell($row, 'telepon', 'phone', 'no_hp', 'hp')),
            'alamat' => $this->nullableString($this->cell($row, 'alamat', 'address')),
            'pic' => $this->nullableString($this->cell($row, 'pic', 'pic_name', 'nama_pic')),
            'status' => $status,
            'vendor_induk' => $this->nullableString($this->cell($row, 'vendor_induk', 'parent', 'parent_name', 'vendor_induk_name')),
            'kategori' => $this->nullableString($this->cell($row, 'kategori', 'category', 'category_name')) ?? 'Lainnya',
            'is_master' => $this->toBool($this->cell($row, 'master', 'is_master'), false),
            'is_published' => $this->toBool($this->cell($row, 'publish', 'published', 'is_published'), true),
            'deskripsi' => $this->formatDescription($this->nullableString($this->cell($row, 'deskripsi', 'description'))),
            'harga_publish' => $this->toMoney($this->cell($row, 'harga_publish')),
            'harga_vendor' => $this->toMoney($this->cell($row, 'harga_vendor')),
            'stok' => $this->toNullableInt($this->cell($row, 'stok', 'stock')),
            'bank' => $this->nullableString($this->cell($row, 'bank', 'bank_name')),
            'no_rekening' => $this->nullableString($this->cell($row, 'no_rekening', 'bank_account', 'rekening')),
            'atas_nama' => $this->nullableString($this->cell($row, 'atas_nama', 'account_holder', 'nama_rekening')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function importRow(int $excelRow, array $data): void
    {
        if (blank($data['nama'])) {
            $this->skipped++;
            $this->errors[] = "Baris {$excelRow}: kolom nama wajib diisi.";

            return;
        }

        if ($data['harga_publish'] === null) {
            $this->skipped++;
            $this->errors[] = "Baris {$excelRow}: kolom harga_publish wajib diisi ({$data['nama']}).";

            return;
        }

        if ($data['harga_vendor'] === null) {
            $this->skipped++;
            $this->errors[] = "Baris {$excelRow}: kolom harga_vendor wajib diisi ({$data['nama']}).";

            return;
        }

        $existing = Vendor::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['nama'])])
            ->first();

        if ($existing) {
            $this->skipped++;
            $suffix = $existing->trashed() ? ' (sudah dihapus)' : '';
            $this->errors[] = "Baris {$excelRow}: vendor \"{$data['nama']}\" sudah ada{$suffix}.";

            return;
        }

        $parentId = null;
        if (filled($data['vendor_induk'])) {
            $parent = Vendor::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['vendor_induk'])])
                ->whereNull('parent_id')
                ->first();

            if ($parent && $data['status'] === StatusVendor::PRODUCT->value) {
                $parentId = $parent->id;
            }
        }

        try {
            Vendor::create([
                'name' => $data['nama'],
                'slug' => Vendor::generateUniqueSlug($data['nama']),
                'phone' => $data['telepon'],
                'address' => $data['alamat'],
                'pic_name' => $data['pic'],
                'status' => $data['status'],
                'parent_id' => $parentId,
                'category_id' => $this->resolveCategoryId($data['kategori']),
                'is_master' => $data['is_master'],
                'is_published' => $data['is_published'],
                'description' => $data['deskripsi'],
                'harga_publish' => $data['harga_publish'],
                'harga_vendor' => $data['harga_vendor'],
                'stock' => $data['stok'],
                'bank_name' => $data['bank'],
                'bank_account' => $data['no_rekening'],
                'account_holder' => $data['atas_nama'],
            ]);

            $this->imported++;
        } catch (\Throwable $e) {
            $this->skipped++;
            $this->errors[] = "Baris {$excelRow}: gagal menyimpan \"{$data['nama']}\" — {$e->getMessage()}";
        }
    }

    private function resolveCategoryId(string $name): int
    {
        $existing = Category::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $category = Category::firstOrCreate(
            ['slug' => Str::slug($name) ?: Str::slug($name.'-'.uniqid())],
            [
                'name' => $name,
                'is_active' => true,
            ]
        );

        return $category->id;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function cell(array $row, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        return blank($data['nama'])
            && $data['harga_publish'] === null
            && $data['harga_vendor'] === null;
    }

    private function normalizeStatus(mixed $value): string
    {
        $raw = strtolower(trim((string) $value));

        return match ($raw) {
            'product' => StatusVendor::PRODUCT->value,
            'master' => StatusVendor::MASTER->value,
            default => StatusVendor::VENDOR->value,
        };
    }

    private function statusPriority(string $status): int
    {
        return $status === StatusVendor::PRODUCT->value ? 1 : 0;
    }

    private function normalizePhone(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $value = number_format((float) $value, 0, '', '');
        }

        $phone = preg_replace('/[^\d+]/', '', (string) $value) ?? '';
        $phone = preg_replace('/^\+?62/', '', $phone) ?? $phone;
        $phone = ltrim($phone, '0');

        return $phone !== '' ? $phone : null;
    }

    private function formatDescription(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (str_contains($value, '<') && str_contains($value, '>')) {
            return $value;
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        $lines = array_values(array_filter(
            array_map(static fn (string $line): string => trim($line), explode("\n", $normalized)),
            static fn (string $line): bool => $line !== ''
        ));

        if ($lines === []) {
            return null;
        }

        if (count($lines) === 1 && preg_match_all('/\d+[\.\)]\s*(.+?)(?=\s*\d+[\.\)]\s*|$)/u', $lines[0], $inlineMatches) && count($inlineMatches[1]) > 1) {
            $lines = array_map(static fn (string $item): string => trim($item), $inlineMatches[1]);
            $lines = array_map(static fn (string $item, int $i): string => ($i + 1).'. '.$item, $lines, array_keys($lines));
        }

        $numbered = [];
        foreach ($lines as $line) {
            if (! preg_match('/^\d+[\.\)]\s*(.+)$/u', $line, $match)) {
                $numbered = null;
                break;
            }

            $numbered[] = $match[1];
        }

        if (is_array($numbered) && $numbered !== []) {
            $items = collect($numbered)
                ->map(static fn (string $item): string => '<li>'.e($item).'</li>')
                ->implode('');

            return '<ol>'.$items.'</ol>';
        }

        if (count($lines) === 1) {
            return '<p>'.e($lines[0]).'</p>';
        }

        return collect($lines)
            ->map(static fn (string $line): string => '<p>'.e($line).'</p>')
            ->implode('');
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function toMoney(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $digits = preg_replace('/[^\d]/', '', (string) $value);

        return $digits === '' || $digits === null ? null : (int) $digits;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $digits = preg_replace('/[^\d]/', '', (string) $value);

        return $digits === '' || $digits === null ? null : (int) $digits;
    }

    private function toBool(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $raw = strtolower(trim((string) $value));

        return in_array($raw, ['1', 'true', 'ya', 'yes', 'y', 'master', 'published', 'publish'], true);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
