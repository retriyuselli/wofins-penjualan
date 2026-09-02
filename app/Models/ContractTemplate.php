<?php

namespace App\Models;

use App\Support\ContractTemplateDefaults;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'title',
        'package_section_title',
        'package_price_label',
        'facilities_heading',
        'intro_pihak_pertama',
        'intro_pihak_kedua',
        'intro_after_parties',
        'closing_text',
        'is_system_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ContractTemplate $template): void {
            if ($template->is_system_default) {
                static::query()
                    ->whereKeyNot($template->getKey() ?: 0)
                    ->where('is_system_default', true)
                    ->update(['is_system_default' => false]);
            }

            if ($template->is_active && $template->company_id) {
                static::query()
                    ->whereKeyNot($template->getKey() ?: 0)
                    ->where('company_id', $template->company_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ContractTemplateSection::class)->orderBy('sort_order');
    }

    public function enabledSections(): HasMany
    {
        return $this->sections()->where('is_enabled', true);
    }

    public static function copyFrom(ContractTemplate $source, array $overrides = []): self
    {
        $source->loadMissing('sections');

        $copy = $source->replicate(['is_system_default']);
        $copy->fill(array_merge([
            'is_system_default' => false,
            'is_active' => true,
            'name' => ($source->name ?: 'Template').' (salinan)',
        ], $overrides));
        $copy->save();

        foreach ($source->sections as $section) {
            $copy->sections()->create($section->only([
                'key',
                'title',
                'body',
                'sort_order',
                'is_enabled',
            ]));
        }

        return $copy;
    }

    public static function makeFromDefaults(array $overrides = []): self
    {
        $template = static::query()->create(array_merge(
            ContractTemplateDefaults::templateAttributes(),
            $overrides,
        ));

        foreach (ContractTemplateDefaults::sections() as $index => $section) {
            $template->sections()->create([
                ...$section,
                'sort_order' => $index + 1,
            ]);
        }

        return $template->load('sections');
    }
}
