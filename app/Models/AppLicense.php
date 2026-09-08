<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppLicense extends Model
{
    protected $fillable = [
        'code',
        'company_name',
        'package',
        'domain',
        'starts_at',
        'ends_at',
        'status',
        'activated_at',
        'last_verified_at',
        'last_message',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'activated_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function daysRemaining(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->ends_at->startOfDay(), false);
    }

    public function isWithinPeriod(): bool
    {
        if (! $this->starts_at || ! $this->ends_at) {
            return false;
        }

        $today = now()->startOfDay();

        return $this->starts_at->startOfDay()->lte($today)
            && $this->ends_at->startOfDay()->gte($today);
    }
}
