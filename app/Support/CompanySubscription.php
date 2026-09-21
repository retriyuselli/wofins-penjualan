<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Company;
use App\Models\DataPembayaran;
use App\Models\Expense;
use App\Models\ExpenseOps;
use App\Models\FixedAsset;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PembayaranPiutang;
use App\Models\PendapatanLain;
use App\Models\PengeluaranLain;
use App\Models\Piutang;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\SimulasiProduk;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Models\Vendor;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CompanySubscription
{
    public const DEFAULT_PLAN = 'starter';

    public const RESOURCE_USERS = 'users';

    public const RESOURCE_VENDORS = 'vendors';

    public const RESOURCE_PRODUCTS = 'products';

    public const RESOURCE_ORDERS = 'orders';

    public const RESOURCE_PROSPECTS = 'prospects';

    public const RESOURCE_SIMULASI = 'simulasi';

    public const RESOURCE_PAYMENT_METHODS = 'payment_methods';

    public const RESOURCE_FIXED_ASSETS = 'fixed_assets';

    public const RESOURCE_PIUTANGS = 'piutangs';

    public const RESOURCE_PEMBAYARAN_PIUTANGS = 'pembayaran_piutangs';

    public const RESOURCE_CATEGORIES = 'categories';

    public const RESOURCE_DATA_PEMBAYARANS = 'data_pembayarans';

    public const RESOURCE_EXPENSES = 'expenses';

    public const RESOURCE_EXPENSE_OPS = 'expense_ops';

    public const RESOURCE_PENDAPATAN_LAINS = 'pendapatan_lains';

    public const RESOURCE_PENGELUARAN_LAINS = 'pengeluaran_lains';

    /**
     * @return list<string>
     */
    public static function quotaResources(): array
    {
        return [
            self::RESOURCE_USERS,
            self::RESOURCE_VENDORS,
            self::RESOURCE_PRODUCTS,
            self::RESOURCE_ORDERS,
            self::RESOURCE_PROSPECTS,
            self::RESOURCE_SIMULASI,
            self::RESOURCE_PAYMENT_METHODS,
            self::RESOURCE_FIXED_ASSETS,
            self::RESOURCE_PIUTANGS,
            self::RESOURCE_PEMBAYARAN_PIUTANGS,
            self::RESOURCE_DATA_PEMBAYARANS,
            self::RESOURCE_EXPENSES,
            self::RESOURCE_EXPENSE_OPS,
            self::RESOURCE_PENDAPATAN_LAINS,
            self::RESOURCE_PENGELUARAN_LAINS,
        ];
    }

    /**
     * Company aktif untuk kuota/fitur: milik user login (1 WO = 1 Company).
     * Super admin tanpa company_id → null (bypass via ProFeatures).
     */
    public static function company(?User $actor = null): ?Company
    {
        if (! Schema::hasTable('companies')) {
            return null;
        }

        $actor ??= Auth::user();

        if ($actor instanceof User && Schema::hasColumn('users', 'company_id') && $actor->company_id) {
            $companyId = (int) $actor->company_id;

            return Cache::remember('wofins.company.subscription.'.$companyId, 60, function () use ($companyId) {
                return Company::query()->find($companyId);
            });
        }

        return Cache::remember('wofins.company.subscription.single', 60, function () {
            return Company::query()->first();
        });
    }

    public static function forgetCache(?int $companyId = null): void
    {
        if ($companyId) {
            Cache::forget('wofins.company.subscription.'.$companyId);
        }

        Cache::forget('wofins.company.subscription');
    }

    /**
     * Apakah perusahaan memakai paket yang dikenali, termasuk paket legacy Enterprise.
     */
    public static function hasConfiguredPlan(): bool
    {
        $key = static::company()?->subscription_plan;

        return PricingPlans::find((string) $key) !== null;
    }

    public static function planKey(): string
    {
        $company = static::company();
        $key = $company?->subscription_plan ?? null;

        if (! $key && class_exists(\App\Services\AppLicenseService::class)) {
            $license = app(\App\Services\AppLicenseService::class)->current();
            $key = $license?->package;
        }

        $normalized = PricingPlans::normalizeKey(is_string($key) ? $key : null);
        if ($normalized && PricingPlans::find($normalized) !== null) {
            return $normalized;
        }

        if (PricingPlans::find((string) $key) !== null) {
            return (string) $key;
        }

        return (string) config('wofins.default_subscription_plan', self::DEFAULT_PLAN);
    }

    public static function plan(): array
    {
        return PricingPlans::find(static::planKey()) ?? PricingPlans::find(self::DEFAULT_PLAN);
    }

    public static function planLabel(): string
    {
        if (! static::hasConfiguredPlan()) {
            return 'Paket belum diatur';
        }

        return PricingPlans::shortLabel(static::planKey());
    }

    /**
     * Batas efektif untuk resource (override perusahaan menang).
     * null = tak terbatas.
     *
     * Paket kosong/tidak dikenal memakai batas Starter (fail-safe).
     */
    public static function limit(string $resource): ?int
    {
        $company = static::company();
        $override = match ($resource) {
            self::RESOURCE_USERS, 'seats' => $company?->seat_limit_override,
            self::RESOURCE_VENDORS => $company?->vendor_limit_override,
            self::RESOURCE_PRODUCTS => $company?->product_limit_override,
            self::RESOURCE_ORDERS => $company?->order_limit_override,
            self::RESOURCE_PROSPECTS => $company?->prospect_limit_override,
            self::RESOURCE_SIMULASI, 'simulations' => $company?->simulasi_limit_override,
            self::RESOURCE_PAYMENT_METHODS, 'rekening' => $company?->payment_method_limit_override,
            self::RESOURCE_FIXED_ASSETS, 'aset' => $company?->fixed_asset_limit_override,
            self::RESOURCE_PIUTANGS, 'piutang' => $company?->piutang_limit_override,
            self::RESOURCE_PEMBAYARAN_PIUTANGS, 'pembayaran_piutang' => $company?->pembayaran_piutang_limit_override,
            self::RESOURCE_CATEGORIES, 'kategori' => $company?->category_limit_override,
            self::RESOURCE_DATA_PEMBAYARANS, 'pendapatan_wedding' => $company?->data_pembayaran_limit_override,
            self::RESOURCE_EXPENSES, 'pengeluaran_wedding' => $company?->expense_limit_override,
            self::RESOURCE_EXPENSE_OPS, 'pengeluaran_ops' => $company?->expense_ops_limit_override,
            self::RESOURCE_PENDAPATAN_LAINS, 'pendapatan_lain' => $company?->pendapatan_lain_limit_override,
            self::RESOURCE_PENGELUARAN_LAINS, 'pengeluaran_lain' => $company?->pengeluaran_lain_limit_override,
            default => null,
        };

        if ($override !== null) {
            return max(0, (int) $override);
        }

        $normalized = match ($resource) {
            'seats' => self::RESOURCE_USERS,
            'simulations' => self::RESOURCE_SIMULASI,
            'rekening' => self::RESOURCE_PAYMENT_METHODS,
            'aset' => self::RESOURCE_FIXED_ASSETS,
            'piutang' => self::RESOURCE_PIUTANGS,
            'pembayaran_piutang' => self::RESOURCE_PEMBAYARAN_PIUTANGS,
            'kategori' => self::RESOURCE_CATEGORIES,
            'pendapatan_wedding' => self::RESOURCE_DATA_PEMBAYARANS,
            'pengeluaran_wedding' => self::RESOURCE_EXPENSES,
            'pengeluaran_ops' => self::RESOURCE_EXPENSE_OPS,
            'pendapatan_lain' => self::RESOURCE_PENDAPATAN_LAINS,
            'pengeluaran_lain' => self::RESOURCE_PENGELUARAN_LAINS,
            default => $resource,
        };

        return PricingPlans::limit(static::planKey(), $normalized);
    }

    public static function used(string $resource): int
    {
        return match ($resource) {
            self::RESOURCE_USERS, 'seats' => static::seatsUsed(),
            self::RESOURCE_VENDORS => static::vendorsUsed(),
            self::RESOURCE_PRODUCTS => static::productsUsed(),
            self::RESOURCE_ORDERS => static::ordersUsed(),
            self::RESOURCE_PROSPECTS => static::prospectsUsed(),
            self::RESOURCE_SIMULASI, 'simulations' => static::simulasiUsed(),
            self::RESOURCE_PAYMENT_METHODS, 'rekening' => static::paymentMethodsUsed(),
            self::RESOURCE_FIXED_ASSETS, 'aset' => static::fixedAssetsUsed(),
            self::RESOURCE_PIUTANGS, 'piutang' => static::piutangsUsed(),
            self::RESOURCE_PEMBAYARAN_PIUTANGS, 'pembayaran_piutang' => static::pembayaranPiutangsUsed(),
            self::RESOURCE_CATEGORIES, 'kategori' => static::categoriesUsed(),
            self::RESOURCE_DATA_PEMBAYARANS, 'pendapatan_wedding' => static::dataPembayaransUsed(),
            self::RESOURCE_EXPENSES, 'pengeluaran_wedding' => static::expensesUsed(),
            self::RESOURCE_EXPENSE_OPS, 'pengeluaran_ops' => static::expenseOpsUsed(),
            self::RESOURCE_PENDAPATAN_LAINS, 'pendapatan_lain' => static::pendapatanLainsUsed(),
            self::RESOURCE_PENGELUARAN_LAINS, 'pengeluaran_lain' => static::pengeluaranLainsUsed(),
            default => 0,
        };
    }

    public static function remaining(string $resource): ?int
    {
        $limit = static::limit($resource);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - static::used($resource));
    }

    public static function canCreate(string $resource): bool
    {
        // Super admin platform boleh melebihi kuota paket (override operasional).
        if (ProFeatures::actorIsSuperAdmin()) {
            return true;
        }

        $remaining = static::remaining($resource);

        return $remaining === null || $remaining > 0;
    }

    public static function resourceLabel(string $resource): string
    {
        return match ($resource) {
            self::RESOURCE_USERS, 'seats' => 'pengguna',
            self::RESOURCE_VENDORS => 'vendor',
            self::RESOURCE_PRODUCTS => 'produk',
            self::RESOURCE_ORDERS => 'proyek wedding',
            self::RESOURCE_PROSPECTS => 'prospek',
            self::RESOURCE_SIMULASI, 'simulations' => 'simulasi',
            self::RESOURCE_PAYMENT_METHODS, 'rekening' => 'rekening',
            self::RESOURCE_FIXED_ASSETS, 'aset' => 'aset tetap',
            self::RESOURCE_PIUTANGS, 'piutang' => 'piutang',
            self::RESOURCE_PEMBAYARAN_PIUTANGS, 'pembayaran_piutang' => 'pembayaran piutang',
            self::RESOURCE_CATEGORIES, 'kategori' => 'kategori',
            self::RESOURCE_DATA_PEMBAYARANS, 'pendapatan_wedding' => 'pendapatan wedding',
            self::RESOURCE_EXPENSES, 'pengeluaran_wedding' => 'pengeluaran wedding',
            self::RESOURCE_EXPENSE_OPS, 'pengeluaran_ops' => 'pengeluaran operasional',
            self::RESOURCE_PENDAPATAN_LAINS, 'pendapatan_lain' => 'pendapatan lain',
            self::RESOURCE_PENGELUARAN_LAINS, 'pengeluaran_lain' => 'pengeluaran lain',
            default => $resource,
        };
    }

    public static function summary(string $resource): string
    {
        $used = static::used($resource);
        $limit = static::limit($resource);
        $label = static::resourceLabel($resource);

        if ($limit === null) {
            return "{$used} {$label} (tak terbatas)";
        }

        return "{$used} / {$limit} {$label}";
    }

    public static function fullMessage(string $resource): string
    {
        $plan = static::planLabel();
        $limit = static::limit($resource);
        $label = static::resourceLabel($resource);

        if ($limit === null) {
            return "Kuota {$label} penuh.";
        }

        return "Kuota {$plan} ({$limit} {$label}) sudah penuh. Upgrade paket atau hapus data yang tidak dipakai.";
    }

    public static function quotasOverview(): string
    {
        return implode(' · ', array_map(
            fn (string $resource) => static::summary($resource),
            static::quotaResources()
        ));
    }

    /**
     * Matriks kuota untuk dashboard / profil.
     *
     * @return list<array{key: string, label: string, used: int, limit: int|null, remaining: int|null, percent: int, summary: string, full: bool}>
     */
    public static function quotaMatrix(): array
    {
        $labels = [
            self::RESOURCE_USERS => 'Pengguna',
            self::RESOURCE_VENDORS => 'Vendor',
            self::RESOURCE_PRODUCTS => 'Produk',
            self::RESOURCE_ORDERS => 'Proyek Wedding',
            self::RESOURCE_PROSPECTS => 'Prospek',
            self::RESOURCE_SIMULASI => 'Simulasi',
            self::RESOURCE_PAYMENT_METHODS => 'Rekening',
            self::RESOURCE_FIXED_ASSETS => 'Aset Tetap',
            self::RESOURCE_PIUTANGS => 'Piutang',
            self::RESOURCE_PEMBAYARAN_PIUTANGS => 'Pembayaran Piutang',
            self::RESOURCE_DATA_PEMBAYARANS => 'Pendapatan Wedding',
            self::RESOURCE_EXPENSES => 'Pengeluaran Wedding',
            self::RESOURCE_EXPENSE_OPS => 'Pengeluaran Operasional',
            self::RESOURCE_PENDAPATAN_LAINS => 'Pendapatan Lain',
            self::RESOURCE_PENGELUARAN_LAINS => 'Pengeluaran Lain',
        ];

        $rows = [];

        foreach ($labels as $key => $label) {
            $used = static::used($key);
            $limit = static::limit($key);
            $remaining = static::remaining($key);
            $percent = $limit === null
                ? 0
                : (int) min(100, round(($used / max(1, $limit)) * 100));

            $rows[] = [
                'key' => $key,
                'label' => $label,
                'used' => $used,
                'limit' => $limit,
                'remaining' => $remaining,
                'percent' => $percent,
                'summary' => static::summary($key),
                'full' => ! static::canCreate($key),
            ];
        }

        return $rows;
    }

    public static function seatLimit(): ?int
    {
        return static::limit(self::RESOURCE_USERS);
    }

    /**
     * Kursi terpakai dalam Company actor (exclude super_admin & terminated).
     * Super admin tanpa konteks company: 0 (kuota dicek per-tenant saat provision).
     */
    public static function seatsUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('users')) {
            return 0;
        }

        $company ??= static::company();

        $query = User::query()
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'terminated');
            })
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'super_admin');
            });

        if ($company && Schema::hasColumn('users', 'company_id')) {
            $query->where('company_id', $company->id);
        } elseif (! ProFeatures::actorIsSuperAdmin()) {
            UserVisibility::constrainUsersQuery($query);
        } else {
            return 0;
        }

        return $query->count();
    }

    public static function vendorsUsed(): int
    {
        if (! Schema::hasTable('vendors')) {
            return 0;
        }

        $query = Vendor::query();
        static::applyTeamOwnerScope($query, 'created_by');

        return $query->count();
    }

    public static function productsUsed(): int
    {
        if (! Schema::hasTable('products')) {
            return 0;
        }

        $query = Product::query();
        static::applyTeamOwnerScope($query, 'created_by');

        return $query->count();
    }

    public static function ordersUsed(): int
    {
        if (! Schema::hasTable('orders')) {
            return 0;
        }

        $query = Order::query();
        static::applyTeamOwnerScope($query, 'user_id');

        return $query->count();
    }

    public static function prospectsUsed(): int
    {
        if (! Schema::hasTable('prospects')) {
            return 0;
        }

        $query = Prospect::query();
        static::applyTeamOwnerScope($query, 'user_id');

        return $query->count();
    }

    public static function simulasiUsed(): int
    {
        if (! Schema::hasTable('simulasi_produks')) {
            return 0;
        }

        $query = SimulasiProduk::query();
        static::applyTeamOwnerScope($query, 'user_id');

        return $query->count();
    }

    public static function paymentMethodsUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('payment_methods')) {
            return 0;
        }

        $company ??= static::company();
        $query = PaymentMethod::query()->withoutGlobalScopes();

        if ($company && Schema::hasColumn('payment_methods', 'company_id')) {
            $query->where('company_id', $company->id);
        } elseif (! ProFeatures::actorIsSuperAdmin()) {
            UserVisibility::constrainCompanyQuery($query);
        } else {
            return 0;
        }

        return $query->count();
    }

    public static function fixedAssetsUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('fixed_assets')) {
            return 0;
        }

        $company ??= static::company();
        $query = FixedAsset::query()->withoutGlobalScopes();

        if ($company && Schema::hasColumn('fixed_assets', 'company_id')) {
            $query->where('company_id', $company->id);
        } elseif (! ProFeatures::actorIsSuperAdmin()) {
            UserVisibility::constrainCompanyQuery($query);
        } else {
            return 0;
        }

        return $query->count();
    }

    public static function categoriesUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('categories')) {
            return 0;
        }

        $company ??= static::company();
        $query = Category::query()->withoutGlobalScopes();

        if ($company && Schema::hasColumn('categories', 'company_id')) {
            $query->where('company_id', $company->id);
        } elseif (! ProFeatures::actorIsSuperAdmin()) {
            UserVisibility::constrainCompanyQuery($query);
        } else {
            return 0;
        }

        return $query->count();
    }

    public static function dataPembayaransUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('data_pembayarans')) {
            return 0;
        }

        return static::countViaCompanyOrders(DataPembayaran::query(), $company);
    }

    public static function expensesUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('expenses')) {
            return 0;
        }

        return static::countViaCompanyOrders(Expense::query(), $company);
    }

    public static function expenseOpsUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('expense_ops')) {
            return 0;
        }

        return static::countViaCompanyPaymentMethods(ExpenseOps::query(), $company);
    }

    public static function pendapatanLainsUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('pendapatan_lains')) {
            return 0;
        }

        return static::countViaCompanyPaymentMethods(PendapatanLain::query(), $company);
    }

    public static function pengeluaranLainsUsed(?Company $company = null): int
    {
        if (! Schema::hasTable('pengeluaran_lains')) {
            return 0;
        }

        return static::countViaCompanyPaymentMethods(PengeluaranLain::query(), $company);
    }

    /**
     * @param  Builder<Model>  $query
     */
    private static function countViaCompanyOrders(Builder $query, ?Company $company = null): int
    {
        $company ??= static::company();
        $userIds = [];

        if ($company && Schema::hasColumn('users', 'company_id')) {
            $userIds = User::query()->where('company_id', $company->id)->pluck('id')->all();
        } elseif (! ProFeatures::actorIsSuperAdmin()) {
            $userIds = UserVisibility::teamUserIds();
        } else {
            return 0;
        }

        if ($userIds === []) {
            return 0;
        }

        return $query->whereIn('order_id', function ($q) use ($userIds) {
            $q->select('id')->from('orders')->whereIn('user_id', $userIds);
        })->count();
    }

    /**
     * @param  Builder<Model>  $query
     */
    private static function countViaCompanyPaymentMethods(Builder $query, ?Company $company = null): int
    {
        $company ??= static::company();

        if ($company && Schema::hasColumn('payment_methods', 'company_id')) {
            return $query->whereIn('payment_method_id', function ($q) use ($company) {
                $q->select('id')->from('payment_methods')->where('company_id', $company->id);
            })->count();
        }

        if (ProFeatures::actorIsSuperAdmin()) {
            return 0;
        }

        $companyId = UserVisibility::companyId();

        if ($companyId === null) {
            return 0;
        }

        return $query->whereIn('payment_method_id', function ($q) use ($companyId) {
            $q->select('id')->from('payment_methods')->where('company_id', $companyId);
        })->count();
    }

    public static function piutangsUsed(): int
    {
        if (! Schema::hasTable('piutangs')) {
            return 0;
        }

        $query = Piutang::query();
        static::applyTeamOwnerScope($query, 'dibuat_oleh');

        return $query->count();
    }

    public static function pembayaranPiutangsUsed(): int
    {
        if (! Schema::hasTable('pembayaran_piutangs')) {
            return 0;
        }

        $query = PembayaranPiutang::query();

        if (! ProFeatures::actorIsSuperAdmin()) {
            $teamIds = UserVisibility::teamUserIds();

            if ($teamIds === []) {
                return 0;
            }

            $query->whereIn('piutang_id', function ($q) use ($teamIds) {
                $q->select('id')->from('piutangs')->whereIn('dibuat_oleh', $teamIds);
            });
        }

        return $query->count();
    }

    /**
     * Badge navigasi: "used/limit" atau hanya used jika tak terbatas.
     */
    public static function navigationBadge(string $resource): string
    {
        $used = static::used($resource);
        $limit = static::limit($resource);

        if ($limit === null) {
            return (string) $used;
        }

        return "{$used}/{$limit}";
    }

    /**
     * Kuota selalu dihitung per tim untuk non–super_admin
     * (bukan lewat actorSeesGlobalAggregates / finance).
     *
     * @param  Builder<Model>  $query
     */
    private static function applyTeamOwnerScope(Builder $query, string $column): void
    {
        if (ProFeatures::actorIsSuperAdmin()) {
            return;
        }

        $teamIds = UserVisibility::teamUserIds();

        if ($teamIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($column, $teamIds);
    }

    public static function seatsRemaining(): ?int
    {
        return static::remaining(self::RESOURCE_USERS);
    }

    public static function hasSeatAvailable(): bool
    {
        return static::canCreate(self::RESOURCE_USERS);
    }

    public static function canSendTeamInvitation(?User $target): bool
    {
        if (! $target?->company_id) {
            return false;
        }

        $company = $target->relationLoaded('company')
            ? $target->getRelation('company')
            : $target->company()->first();

        if (! $company instanceof Company || $company->isDeactivated()) {
            return false;
        }

        if (PricingPlans::normalizeKey($company->subscription_plan) !== 'business') {
            return false;
        }

        $expiresAt = $company->subscription_expires_at;

        return ! $expiresAt || now()->lessThanOrEqualTo($expiresAt->copy()->endOfDay());
    }

    public static function seatSummary(): string
    {
        return static::summary(self::RESOURCE_USERS);
    }

    public static function allows(string $feature): bool
    {
        // Belum pilih paket: perlakukan sebagai Starter — jangan buka fitur Business/Pro.
        // (Dulu return true = semua fitur terbuka, termasuk Crew Freelance.)
        if (! static::hasConfiguredPlan()) {
            return PricingPlans::allows(self::DEFAULT_PLAN, $feature);
        }

        return PricingPlans::allows(static::planKey(), $feature);
    }

    /**
     * Map minat ProspectApp.service → subscription_plan (untuk onboarding).
     */
    public static function syncPlanFromService(?string $service, bool $overwrite = false): void
    {
        $plan = PricingPlans::normalizeKey($service);
        $company = static::company();

        if (! $plan || ! $company) {
            return;
        }

        if (! $overwrite && PricingPlans::find($company->subscription_plan)) {
            return;
        }

        $company->forceFill(['subscription_plan' => $plan])->save();
        static::forgetCache($company->id);
    }

    public static function upgradeMessage(string $feature = PricingPlans::FEATURE_PAYROLL): string
    {
        $plan = static::planLabel();

        return "Fitur ini tidak termasuk {$plan}.";
    }

    public static function seatFullMessage(): string
    {
        return static::fullMessage(self::RESOURCE_USERS);
    }

    /**
     * Pesan kuota penuh + petunjuk upgrade sesuai paket.
     */
    public static function seatUpgradeHint(): string
    {
        $planKey = static::planKey();
        $plan = static::planLabel();
        $limit = static::seatLimit();
        $used = static::seatsUsed();

        if ($limit === null) {
            return static::seatFullMessage();
        }

        $base = "Kuota {$plan} sudah penuh ({$used}/{$limit} pengguna).";

        if (in_array($planKey, ['starter', 'professional'], true)) {
            return $base.' Paket ini hanya 1 seat (owner).';
        }

        if ($planKey === 'business') {
            return $base.' Nonaktifkan pengguna yang tidak dipakai.';
        }

        return $base.' Hapus pengguna yang tidak dipakai untuk menambah seat.';
    }

    /**
     * Tanggal akhir masa aktif paket perusahaan (null = tidak dibatasi).
     */
    public static function expiresAt(?User $actor = null): ?CarbonInterface
    {
        $company = static::company($actor);
        $expires = $company?->subscription_expires_at ?? null;

        if ($expires instanceof CarbonInterface) {
            return $expires;
        }

        if (class_exists(\App\Services\AppLicenseService::class)) {
            $license = app(\App\Services\AppLicenseService::class)->current();
            if ($license?->ends_at) {
                return $license->ends_at->endOfDay();
            }
        }

        return null;
    }

    public static function hasExpiry(?User $actor = null): bool
    {
        return static::expiresAt($actor) !== null;
    }

    /**
     * Paket sudah lewat tanggal berlaku.
     * Super admin / force unlock tidak dianggap expired di sini.
     */
    public static function isExpired(?User $actor = null): bool
    {
        $expires = static::expiresAt($actor);

        if (! $expires) {
            return false;
        }

        return now()->greaterThan($expires->copy()->endOfDay());
    }

    public static function isExpiringSoon(?User $actor = null, int $withinDays = 14): bool
    {
        $expires = static::expiresAt($actor);

        if (! $expires || static::isExpired($actor)) {
            return false;
        }

        return $expires->lessThanOrEqualTo(now()->addDays($withinDays)->endOfDay());
    }

    public static function daysUntilExpiry(?User $actor = null): ?int
    {
        $expires = static::expiresAt($actor);

        if (! $expires) {
            return null;
        }

        if (static::isExpired($actor)) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($expires->copy()->startOfDay());
    }

    public static function expiresAtLabel(?User $actor = null): ?string
    {
        $expires = static::expiresAt($actor);

        return $expires?->timezone(config('app.timezone'))->translatedFormat('d F Y');
    }

    /**
     * Siapa yang boleh memicu perpanjang paket (owner / root tim).
     * Super admin platform selalu boleh.
     */
    public static function canManageSubscription(?User $actor = null): bool
    {
        // wofins-penjualan: perpanjang lewat maknafinance / web, bukan IAP di app.
        return false;
    }

    /**
     * Kontak admin WO untuk staf (nama + email owner bila ada).
     */
    public static function subscriptionAdminContact(?User $actor = null): array
    {
        $owner = UserVisibility::teamOwner($actor);
        $company = static::company($actor);

        $name = $owner?->name
            ?: ($company?->owner_name ?: null)
            ?: 'admin perusahaan Anda';

        $email = $owner?->email
            ?: ($company?->email ?: null);

        return [
            'name' => $name,
            'email' => $email,
            'label' => $email ? "{$name} ({$email})" : $name,
        ];
    }

    /**
     * Aktifkan / perpanjang paket perusahaan dari pesanan yang disetujui.
     * Durasi mengikuti billing: 1 / 12 / 24 / 48 bulan.
     */
    public static function activateFromOrder(SubscriptionOrder $order): ?Company
    {
        $planKey = PricingPlans::normalizeKey($order->plan_key);

        if (! $planKey) {
            $found = PricingPlans::find($order->plan_key);
            $planKey = $found['key'] ?? null;
        }

        if (! $planKey || ! PricingPlans::find($planKey)) {
            return null;
        }

        $user = $order->user;
        $company = null;

        if ($user instanceof User && $user->company_id) {
            $company = Company::query()->find($user->company_id);
        }

        if (! $company && filled($order->company_name)) {
            $company = Company::query()
                ->where('company_name', $order->company_name)
                ->first();
        }

        if (! $company) {
            return null;
        }

        $pricing = PricingPlans::resolveBillingPrice(
            PricingPlans::find($planKey),
            (string) $order->billing
        );
        $months = max(1, (int) ($pricing['months'] ?? 1));

        $currentExpiry = $company->subscription_expires_at;
        $base = now();

        if ($currentExpiry instanceof CarbonInterface && $currentExpiry->greaterThan($base)) {
            $base = $currentExpiry->copy();
        }

        $company->forceFill([
            'subscription_plan' => $planKey,
            'subscription_expires_at' => $base->copy()->addMonthsNoOverflow($months)->endOfDay(),
        ])->save();

        static::forgetCache($company->id);

        if ($user instanceof User && ! $user->company_id) {
            $user->forceFill(['company_id' => $company->id])->save();
        }

        return $company->fresh();
    }

    /**
     * Aktifkan / perpanjang paket dari pembelian In-App Purchase Apple.
     */
    public static function activateFromAppleIap(
        Company $company,
        string $planKey,
        string $billing,
        ?\Carbon\CarbonInterface $appleExpiresAt = null,
    ): Company {
        $planKey = PricingPlans::normalizeKey($planKey) ?: $planKey;
        if (! PricingPlans::find($planKey)) {
            return $company;
        }

        if ($appleExpiresAt instanceof \Carbon\CarbonInterface) {
            $expires = $appleExpiresAt->copy()->endOfDay();
        } else {
            $pricing = PricingPlans::resolveBillingPrice(
                PricingPlans::find($planKey),
                $billing === 'annual' ? 'annual' : 'monthly'
            );
            $months = max(1, (int) ($pricing['months'] ?? ($billing === 'annual' ? 12 : 1)));

            $currentExpiry = $company->subscription_expires_at;
            $base = now();
            if ($currentExpiry instanceof \Carbon\CarbonInterface && $currentExpiry->greaterThan($base)) {
                $base = $currentExpiry->copy();
            }
            $expires = $base->copy()->addMonthsNoOverflow($months)->endOfDay();
        }

        $company->forceFill([
            'subscription_plan' => $planKey,
            'subscription_expires_at' => $expires,
        ])->save();

        static::forgetCache($company->id);

        return $company->fresh();
    }

    /**
     * Perkiraan / tanggal berakhir dari pesanan (sama rumus activateFromOrder).
     * - Belum approved: dari sekarang (atau sisa masa aktif company jika masih jalan)
     * - Sudah approved + company punya expires_at: tampilkan expires_at company
     */
    public static function projectedExpiryFromOrder(SubscriptionOrder $order): ?CarbonInterface
    {
        $planKey = PricingPlans::normalizeKey($order->plan_key);
        if (! $planKey) {
            $found = PricingPlans::find($order->plan_key);
            $planKey = $found['key'] ?? null;
        }

        $plan = $planKey ? PricingPlans::find($planKey) : null;
        if (! $plan) {
            return null;
        }

        $pricing = PricingPlans::resolveBillingPrice($plan, (string) $order->billing);
        $months = max(1, (int) ($pricing['months'] ?? 1));

        $company = null;
        $user = $order->user;
        if ($user instanceof User && $user->company_id) {
            $company = Company::query()->find($user->company_id);
        }
        if (! $company && filled($order->company_name)) {
            $company = Company::query()
                ->where('company_name', $order->company_name)
                ->first();
        }

        if ($order->status === 'approved' && $company?->subscription_expires_at) {
            return $company->subscription_expires_at;
        }

        $base = now();
        $currentExpiry = $company?->subscription_expires_at;
        if ($currentExpiry instanceof CarbonInterface && $currentExpiry->greaterThan($base)) {
            $base = $currentExpiry->copy();
        }

        return $base->copy()->addMonthsNoOverflow($months)->endOfDay();
    }

    public static function projectedExpiryLabelFromOrder(SubscriptionOrder $order): ?string
    {
        return static::projectedExpiryFromOrder($order)
            ?->timezone(config('app.timezone'))
            ->translatedFormat('d F Y');
    }
}
