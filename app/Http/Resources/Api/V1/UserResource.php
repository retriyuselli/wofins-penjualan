<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Company;
use App\Models\AppLicense;
use App\Services\AppLicenseService;
use App\Support\PricingPlans;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $licenseStatus = app(AppLicenseService::class)->status(reverify: false);
        /** @var AppLicense|null $license */
        $license = $licenseStatus['license'] ?? null;
        $licenseValid = ($licenseStatus['valid'] ?? false) === true
            || ($licenseStatus['status'] ?? null) === 'disabled';

        return [
            'id' => $this->id,
            'employee_id' => null,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'date_of_birth' => optional($this->date_of_birth)?->toDateString(),
            'gender' => $this->gender,
            'department' => $this->department,
            'hire_date' => optional($this->hire_date)?->toDateString(),
            'emergency_contact' => $this->emergency_contact,
            'notes' => $this->notes,
            'status' => $this->status,
            'last_working_date' => optional($this->last_working_date)?->toDateString(),
            'avatar_url' => $this->publicMediaUrl($this->avatar_url),
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()->values()->all()),
            'expire_date' => optional($this->expire_date)?->toIso8601String(),
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'days_until_expiration' => $this->getDaysUntilExpiration(),
            'is_subscription_expired' => ! $licenseValid,
            // Perpanjang lisensi klien lewat web/maknafinance — bukan IAP di app.
            'can_manage_subscription' => false,
            'subscription_expires_label' => $this->subscriptionExpiresLabel($license),
            'company' => $this->companyPayload($license),
            'entitlements' => $this->entitlementsPayload($license, $licenseValid),
        ];
    }

    private function subscriptionExpiresLabel(?AppLicense $license): ?string
    {
        if (! $license?->ends_at) {
            return null;
        }

        return 'Berlaku hingga '.$license->ends_at->translatedFormat('d M Y');
    }

    /**
     * @return array{plan: string, plan_label: string, features: list<string>, seat_limit: int|null}
     */
    private function entitlementsPayload(?AppLicense $license, bool $licenseValid): array
    {
        $unlocked = method_exists($this->resource, 'hasRole') && $this->resource->hasRole('super_admin');
        $planKey = PricingPlans::normalizeKey($license?->package) ?? 'starter';
        $plan = PricingPlans::find($planKey) ?? PricingPlans::find('starter');
        $features = $unlocked || $licenseValid
            ? array_values($plan['feature_keys'] ?? [])
            : [];

        return [
            'plan' => $planKey,
            'plan_label' => PricingPlans::shortLabel($planKey),
            'features' => $features,
            'seat_limit' => $unlocked ? null : ($plan['seat_limit'] ?? null),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function companyPayload(?AppLicense $license): ?array
    {
        $company = Company::query()->first();

        if (! $company) {
            return null;
        }

        return [
            'id' => (int) $company->id,
            'name' => $company->company_name,
            'inisial' => $company->inisial_wo,
            'logo_url' => $this->publicMediaUrl($company->logo_url),
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'city' => $company->city,
            'province' => $company->province,
            'website' => $company->website,
            'description' => $company->description,
            'owner_name' => $company->owner_name,
            'jabatan_owner' => $company->jabatan_owner,
            'established_year' => $company->established_year,
            'is_active' => true,
            'subscription_plan' => PricingPlans::normalizeKey($license?->package),
            'subscription_label' => PricingPlans::shortLabel(
                PricingPlans::normalizeKey($license?->package)
            ),
            'subscription_expires_at' => optional($license?->ends_at)?->endOfDay()->toIso8601String(),
        ];
    }

    private function publicMediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url(Storage::disk('public')->url(ltrim($path, '/')));
    }
}
