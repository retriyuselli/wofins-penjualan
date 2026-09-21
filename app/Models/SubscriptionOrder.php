<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stub agar autoload CompanySubscription::activateFromOrder tidak error.
 * wofins-penjualan memakai AppLicense, bukan SubscriptionOrder SaaS.
 */
class SubscriptionOrder extends Model
{
    protected $table = 'subscription_orders';
}
