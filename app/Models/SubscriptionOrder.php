<?php

namespace App\Models;

use App\Support\PricingPlans;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Stub agar autoload CompanySubscription::activateFromOrder tidak error.
 * wofins-penjualan memakai AppLicense, bukan alur pesanan SaaS penuh.
 */
class SubscriptionOrder extends Model
{
    protected $table = 'subscription_orders';

    public function getBillingLabelAttribute(): string
    {
        return PricingPlans::billingLabel((string) ($this->billing ?? ''));
    }

    /**
     * Pesanan yang masih menunggu tinjauan untuk user (satu per waktu).
     */
    public static function pendingForUser(?User $user): ?self
    {
        if (! $user) {
            return null;
        }

        if (! Schema::hasTable('subscription_orders')) {
            return null;
        }

        return static::query()
            ->where('status', 'pending_review')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->latest('submitted_at')
            ->latest('id')
            ->first();
    }
}
