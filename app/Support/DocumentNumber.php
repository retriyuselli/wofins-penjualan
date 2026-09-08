<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentCategory;
use Carbon\Carbon;

class DocumentNumber
{
    public static function woInitial(string $fallback = 'WO'): string
    {
        $company = Company::query()->first(['inisial_wo', 'company_name']);
        $value = strtoupper(trim((string) ($company?->inisial_wo ?: '')));

        if ($value !== '') {
            return $value;
        }

        $fromName = static::initialsFromName((string) ($company?->company_name ?: ''));

        return $fromName !== '' ? $fromName : $fallback;
    }

    public static function initialsFromName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $ignore = ['PT', 'CV', 'UD', 'TBK', 'THE', 'DAN', 'OF', 'AND'];
        $words = preg_split('/\s+/u', strtoupper($name)) ?: [];
        $letters = [];

        foreach ($words as $word) {
            $word = preg_replace('/[^A-Z0-9]/', '', $word) ?? '';
            if ($word === '' || in_array($word, $ignore, true)) {
                continue;
            }

            $letters[] = $word[0];
        }

        $initials = implode('', $letters);
        if ($initials !== '') {
            return $initials;
        }

        $compact = preg_replace('/[^A-Z0-9]/', '', strtoupper($name)) ?: '';

        return $compact !== '' ? substr($compact, 0, 3) : '';
    }

    public static function nextFor(Document $document): string
    {
        $category = $document->category ?? DocumentCategory::query()->find($document->category_id);

        if (! $category) {
            return '';
        }

        $year = now()->year;
        $count = Document::query()
            ->where('category_id', $category->id)
            ->whereYear('created_at', $year)
            ->when($document->exists, fn ($query) => $query->whereKeyNot($document->id))
            ->count();

        return static::format($category, $count + 1);
    }

    public static function format(DocumentCategory $category, int $sequence, ?Carbon $date = null): string
    {
        $date ??= now();
        $format = $category->format_number ?: '{SEQ}/{CAT}/{WO}/{ROMAN_MONTH}/{Y}';

        if (! str_contains($format, '{WO}')) {
            $format = str_replace(['MKI-OUT', '/MKI/'], ['{WO}-OUT', '/{WO}/'], $format);
        }

        return str_replace(
            ['{SEQ}', '{Y}', '{M}', '{ROMAN_MONTH}', '{CAT}', '{DEPT}', '{WO}'],
            [
                str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                (string) $date->year,
                $date->format('m'),
                static::romanMonth((int) $date->month),
                $category->code ?? 'DOC',
                'GEN',
                static::woInitial(),
            ],
            $format
        );
    }

    public static function romanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? '';
    }
}
