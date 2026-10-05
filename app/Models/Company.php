<?php

namespace App\Models;

use App\Enums\SignaturePlaceholderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Company extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_name',
        'business_license',
        'owner_name',
        'owner_user_id',
        'jabatan_owner',
        'inisial_wo',
        'inisial_kontak',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'website',
        'description',
        'show_products_on_web',
        'logo_url',
        'favicon_url',
        'image_login',
        'established_year',
        'employee_count',
        'legal_entity_type',
        'deed_of_establishment',
        'deed_date',
        'notary_name',
        'notary_license_number',
        'nib_number',
        'nib_issued_date',
        'nib_valid_until',
        'npwp_number',
        'npwp_issued_date',
        'tax_office',
        'legal_documents',
        'legal_document_status',
        'signature_placeholder_types',
        'payment_method_id',
    ];

    protected $casts = [
        'established_year' => 'integer',
        'employee_count' => 'integer',
        'deed_date' => 'date',
        'nib_issued_date' => 'date',
        'nib_valid_until' => 'date',
        'npwp_issued_date' => 'date',
        'legal_documents' => 'array',
        'signature_placeholder_types' => 'array',
        'show_products_on_web' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Company $company): void {
            if (! $company->owner_user_id) {
                return;
            }

            $name = User::query()->whereKey($company->owner_user_id)->value('name');
            if (is_string($name) && $name !== '') {
                $company->owner_name = $name;
            }
        });

        static::saved(function (): void {
            Cache::forget('company_show_products_on_web');
        });
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['company_name', 'owner_name', 'email', 'phone', 'show_products_on_web'])
            ->setDescriptionForEvent(fn (string $eventName) => "{$eventName}")
            ->useLogName('company');
    }

    /**
     * @return list<string>
     */
    public static function brandCacheKeys(): array
    {
        return [
            'company_name',
            'company_address',
            'company_email',
            'company_phone',
            'company_logo_url',
            'company_favicon_url',
            'company_brand_version',
            'company_logo_path',
            'company_favicon_path',
            'company_login_image_path',
            'company_show_products_on_web',
        ];
    }

    public static function forgetBrandCache(): void
    {
        foreach (self::brandCacheKeys() as $key) {
            Cache::forget($key);
        }
    }

    public static function showsProductsOnWeb(): bool
    {
        return Cache::remember('company_show_products_on_web', 3600, function () {
            if (! Schema::hasTable('companies') || ! Schema::hasColumn('companies', 'show_products_on_web')) {
                return true;
            }

            $value = static::query()->value('show_products_on_web');

            return $value === null ? true : (bool) $value;
        });
    }

    public function getFaviconPathAttribute(): string
    {
        if ($this->favicon_url && Storage::disk('public')->exists($this->favicon_url)) {
            return Storage::disk('public')->path($this->favicon_url);
        }

        return public_path('images/favicon_makna.png');
    }

    public function getFaviconPublicUrlAttribute(): string
    {
        if ($this->favicon_url && Storage::disk('public')->exists($this->favicon_url)) {
            return Storage::disk('public')->url($this->favicon_url);
        }

        return asset('images/favicon_makna.png');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function showsOwnerSignatureOn(SignaturePlaceholderType $type): bool
    {
        return in_array($type->value, $this->signature_placeholder_types ?? [], true);
    }

    public function ownerSignatureDataUri(): ?string
    {
        $this->loadMissing('owner');

        $path = $this->owner?->signature_url;
        if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($path);
        $binary = @file_get_contents($fullPath);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($fullPath) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    public function ownerSignatureDataUriFor(SignaturePlaceholderType $type): ?string
    {
        if (! $this->showsOwnerSignatureOn($type)) {
            return null;
        }

        return $this->ownerSignatureDataUri();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function contractTemplates(): HasMany
    {
        return $this->hasMany(ContractTemplate::class);
    }
}
