<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class FixedAsset extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'asset_code',
        'asset_name',
        'category',
        'purchase_date',
        'purchase_price',
        'accumulated_depreciation',
        'depreciation_method',
        'useful_life_years',
        'useful_life_months',
        'salvage_value',
        'current_book_value',
        'location',
        'condition',
        'supplier',
        'invoice_number',
        'warranty_expiry',
        'notes',
        'chart_of_account_id',
        'depreciation_account_id',
        'is_active',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_price' => 'integer',
        'accumulated_depreciation' => 'integer',
        'current_book_value' => 'integer',
        'salvage_value' => 'integer',
        'useful_life_years' => 'integer',
        'useful_life_months' => 'integer',
        'is_active' => 'boolean',
    ];

    // Asset Categories
    const CATEGORIES = [
        'BUILDING' => 'Bangunan',
        'EQUIPMENT' => 'Peralatan',
        'FURNITURE' => 'Furniture & Fixtures',
        'VEHICLE' => 'Kendaraan',
        'COMPUTER' => 'Komputer & IT Equipment',
        'OTHER' => 'Lainnya',
    ];

    // Depreciation Methods
    const DEPRECIATION_METHODS = [
        'STRAIGHT_LINE' => 'Garis Lurus',
        'DECLINING_BALANCE' => 'Saldo Menurun',
        'UNITS_OF_PRODUCTION' => 'Unit Produksi',
    ];

    // Asset Conditions
    const CONDITIONS = [
        'EXCELLENT' => 'Sangat Baik',
        'GOOD' => 'Baik',
        'FAIR' => 'Cukup',
        'POOR' => 'Buruk',
        'DAMAGED' => 'Rusak',
    ];

    // Relationships

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'purchase_price', 'current_value', 'status'])
            ->setDescriptionForEvent(fn (string $eventName) => "{$eventName}")
            ->useLogName('fixed_asset');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'depreciation_account_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    // Methods
    public function calculateMonthlyDepreciation(): float
    {
        if ($this->depreciation_method === 'STRAIGHT_LINE') {
            $depreciableAmount = $this->purchase_price - $this->salvage_value;
            $totalMonths = ($this->useful_life_years * 12) + $this->useful_life_months;

            return $totalMonths > 0 ? $depreciableAmount / $totalMonths : 0;
        }

        // Add other depreciation methods as needed
        return 0;
    }

    public function updateBookValue(): void
    {
        $this->current_book_value = $this->purchase_price - $this->accumulated_depreciation;
        $this->save();
    }

    public function isFullyDepreciated(): bool
    {
        return $this->current_book_value <= $this->salvage_value;
    }

    public function getRemainingLifeAttribute(): array
    {
        $totalMonths = ($this->useful_life_years * 12) + $this->useful_life_months;
        $monthsSincePurchase = $this->purchase_date->diffInMonths(now());
        $remainingMonths = max(0, $totalMonths - $monthsSincePurchase);

        return [
            'years' => intval($remainingMonths / 12),
            'months' => $remainingMonths % 12,
            'total_months' => $remainingMonths,
        ];
    }

    // Static methods
    public static function generateAssetCode($category = null): string
    {
        $prefix = match ($category) {
            'BUILDING' => 'BLD',
            'EQUIPMENT' => 'EQP',
            'FURNITURE' => 'FRN',
            'VEHICLE' => 'VHC',
            'COMPUTER' => 'CMP',
            default => 'AST'
        };

        $year = date('Y');
        $lastAsset = self::where('asset_code', 'like', "{$prefix}/{$year}/%")
            ->orderBy('asset_code', 'desc')
            ->first();

        if ($lastAsset) {
            $lastNumber = (int) substr($lastAsset->asset_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s/%s/%04d', $prefix, $year, $newNumber);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->whereIn('condition', ['FAIR', 'POOR']);
    }
}
