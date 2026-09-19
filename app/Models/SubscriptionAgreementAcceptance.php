<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionAgreementAcceptance extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'version',
        'ip_address',
        'user_agent',
        'emailed_at',
    ];

    protected $casts = [
        'emailed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
