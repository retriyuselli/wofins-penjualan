<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProFeatures
{
    /**
     * Force-unlock semua fitur (lokal/dev) lewat env.
     */
    public static function forceUnlocked(): bool
    {
        return (bool) config('wofins.pro_features_enabled', false);
    }

    /**
     * Super admin platform: tidak dibatasi paket perusahaan.
     */
    public static function actorIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && method_exists($user, 'hasRole')
            && $user->hasRole('super_admin');
    }

    /**
     * Apakah fitur diizinkan paket perusahaan saat ini.
     * Default feature = payroll.
     */
    public static function allows(string $feature = PricingPlans::FEATURE_PAYROLL): bool
    {
        if (static::forceUnlocked() || static::actorIsSuperAdmin()) {
            return true;
        }

        return CompanySubscription::allows($feature);
    }

    /**
     * Tenant WO: fitur paket aktif + punya company_id.
     * Dipakai agar menu tetap tampil setelah SA ganti paket tanpa sync Shield.
     */
    public static function tenantAllows(string $feature): bool
    {
        if (static::actorIsSuperAdmin()) {
            return true;
        }

        return static::allows($feature) && UserVisibility::companyId() !== null;
    }

    /**
     * @deprecated Gunakan allows() — dipertahankan untuk kompatibilitas singkat.
     */
    public static function enabled(): bool
    {
        return static::allows(PricingPlans::FEATURE_PAYROLL);
    }

    public static function locked(string $feature = PricingPlans::FEATURE_PAYROLL): bool
    {
        return ! static::allows($feature);
    }
}
