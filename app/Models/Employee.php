<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Employee extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'instagram',
        'kontrak',
        'phone',
        'address',
        'position',
        'salary',
        'date_of_birth',
        'date_of_join',
        'date_of_out',
        'no_rek',
        'user_id',
        'bank_name',
        'photo',
        'note',
    ];

    protected static function booted(): void
    {
        static::creating(function (Employee $employee): void {
            $base = filled($employee->slug)
                ? (string) $employee->slug
                : (string) ($employee->name ?: 'karyawan');
            $employee->slug = static::generateUniqueSlug($base);
        });

        static::updating(function (Employee $employee): void {
            if (! $employee->isDirty('slug') && ! $employee->isDirty('name')) {
                return;
            }

            $base = filled($employee->slug)
                ? (string) $employee->slug
                : (string) ($employee->name ?: 'karyawan');
            $employee->slug = static::generateUniqueSlug($base, (int) $employee->id);
        });

        static::deleted(fn () => Cache::forget('nav:employees:active_count'));
        static::restored(fn () => Cache::forget('nav:employees:active_count'));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'position', 'phone', 'date_of_out'])
            ->setDescriptionForEvent(fn (string $eventName) => "{$eventName}")
            ->useLogName('employee');
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dataPribadi(): HasOne
    {
        return $this->hasOne(DataPribadi::class, 'email', 'email');
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'karyawan';
        $slug = $base;
        $counter = 1;

        while (
            static::withTrashed()
                ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Cari karyawan dengan nama sama (case-insensitive) — untuk peringatan, bukan blokir.
     *
     * @return Collection<int, Employee>
     */
    public static function findSameName(string $name, ?int $ignoreId = null): Collection
    {
        $name = trim($name);
        if ($name === '') {
            return collect();
        }

        return static::query()
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->orderBy('id')
            ->limit(5)
            ->get(['id', 'name', 'email', 'position']);
    }

    public function getEmCountAttribute()
    {
        $totEM = Order::where('employee_id', $this->id)->count();

        return $totEM;
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_join' => 'date',
            'date_of_out' => 'date',
            'salary' => 'integer',
        ];
    }
}
