<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Isolasi data user:
 * - super_admin: semua
 * - lainnya: semua user dalam company_id yang sama (1 WO = 1 Company)
 * - fallback legacy: root + created_by = root
 */
class UserVisibility
{
    public static function actorIsSuperAdmin(): bool
    {
        return ProFeatures::actorIsSuperAdmin();
    }

    public static function actorId(): ?int
    {
        $user = Auth::user();

        return $user instanceof User ? (int) $user->id : null;
    }

    /**
     * Root pemilik paket: user dengan created_by null dalam company, atau induk created_by.
     */
    public static function teamRootId(?User $user = null): ?int
    {
        $user ??= Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        if (Schema::hasColumn('users', 'company_id') && $user->company_id) {
            $ownerId = User::query()
                ->where('company_id', $user->company_id)
                ->where(function (Builder $q) {
                    $q->whereNull('created_by')->orWhere('created_by', 0);
                })
                ->orderBy('id')
                ->value('id');

            if ($ownerId) {
                return (int) $ownerId;
            }
        }

        if (Schema::hasColumn('users', 'created_by') && $user->created_by) {
            return (int) $user->created_by;
        }

        return (int) $user->id;
    }

    /**
     * Pemilik paket / root tim company (user yang di-Approve pertama).
     */
    public static function teamOwner(?User $actor = null): ?User
    {
        $rootId = static::teamRootId($actor);

        if (! $rootId) {
            return null;
        }

        return User::query()->find($rootId);
    }

    public static function isTeamOwner(?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $rootId = static::teamRootId($user);

        return $rootId !== null && (int) $user->id === $rootId;
    }

    public static function companyId(?User $user = null): ?int
    {
        $user ??= Auth::user();

        if ($user instanceof User && Schema::hasColumn('users', 'company_id') && $user->company_id) {
            return (int) $user->company_id;
        }

        // Single-tenant (wofins-penjualan): pakai company row pertama.
        if (Schema::hasTable('companies')) {
            $id = \App\Models\Company::query()->value('id');
            return $id ? (int) $id : null;
        }

        return null;
    }

    /**
     * Badge kuota: non-SA melihat kuota tim sendiri (bukan agregat platform).
     */
    public static function canViewGlobalUserAggregates(): bool
    {
        return static::actorIsSuperAdmin();
    }

    public static function canViewTeamSeatSummary(): bool
    {
        return static::actorId() !== null;
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainUsersQuery(Builder $query): Builder
    {
        if (static::actorIsSuperAdmin()) {
            return $query;
        }

        // Starter / Professional (1 seat): hanya akun sendiri di daftar.
        if (static::isSingleSeatPlan()) {
            $actorId = static::actorId();

            return $actorId
                ? $query->whereKey($actorId)
                : $query->whereRaw('1 = 0');
        }

        $companyId = static::companyId();

        if ($companyId !== null) {
            return $query->where('company_id', $companyId);
        }

        $root = static::teamRootId();

        if ($root === null) {
            return $query->whereRaw('1 = 0');
        }

        if (! Schema::hasColumn('users', 'created_by')) {
            return $query->whereKey($root);
        }

        return $query->where(function (Builder $q) use ($root) {
            $q->whereKey($root)->orWhere('created_by', $root);
        });
    }

    /**
     * ID user dalam Company (atau fallback tim created_by).
     *
     * @return list<int>
     */
    public static function teamUserIds(?User $actor = null): array
    {
        $companyId = static::companyId($actor);

        if ($companyId !== null) {
            return User::query()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        $root = static::teamRootId($actor);

        if ($root === null) {
            return [];
        }

        if (! Schema::hasColumn('users', 'created_by')) {
            return [$root];
        }

        return User::query()
            ->where(function (Builder $q) use ($root) {
                $q->whereKey($root)->orWhere('created_by', $root);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Stempel pemilik tim pada create (created_by / user_id) + company_id.
     * Super admin: biarkan nilai form (boleh null = katalog platform).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function stampTeamOwner(array $data, string $column = 'created_by'): array
    {
        if (static::actorIsSuperAdmin()) {
            return $data;
        }

        $root = static::teamRootId();

        if ($root !== null) {
            $data[$column] = $root;
        }

        $companyId = static::companyId();

        if ($companyId !== null && Schema::hasColumn('users', 'company_id')) {
            $data['company_id'] = $companyId;
        }

        return $data;
    }

    /**
     * Agregat global (seluruh company platform): hanya SA,
     * atau staf Finance / admin_am platform tanpa company_id.
     * User (termasuk Finance) yang punya company_id tetap terisolasi ke tenant-nya.
     */
    public static function actorSeesGlobalAggregates(): bool
    {
        if (static::actorIsSuperAdmin()) {
            return true;
        }

        if (static::companyId() !== null) {
            return false;
        }

        $user = Auth::user();

        return $user instanceof User
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['Finance', 'admin_am']);
    }

    /**
     * Key cache agar widget tidak campur angka antar company / SA / tim.
     * - Super admin: global (seluruh platform)
     * - User bertenant: c{company_id} (Finance/admin_am dalam company tetap terpisah dari SA)
     * - Legacy tanpa company: t{teamRootId}
     */
    public static function cacheScopeKey(): string
    {
        if (static::actorIsSuperAdmin()) {
            return 'global';
        }

        $companyId = static::companyId();

        if ($companyId !== null) {
            return 'c'.$companyId;
        }

        return 't'.(static::teamRootId() ?? 0);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainOrdersQuery(Builder $query): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        return static::constrainOwnedQuery($query, 'user_id');
    }

    /**
     * Expense / DataPembayaran yang terikat order_id ke order tim.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainViaTeamOrders(Builder $query, string $orderIdColumn = 'order_id'): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        $teamIds = static::teamUserIds();

        if ($teamIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($orderIdColumn, function ($q) use ($teamIds) {
            $q->select('id')->from('orders')->whereIn('user_id', $teamIds);
        });
    }

    /**
     * ExpenseOps: filter lewat payment_methods.company_id.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainExpenseOpsQuery(Builder $query): Builder
    {
        return static::constrainViaCompanyPaymentMethods($query);
    }

    /**
     * Expense ops / pendapatan lain / pengeluaran lain / rekening koran:
     * lewat payment_methods.company_id.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainViaCompanyPaymentMethods(Builder $query, string $column = 'payment_method_id'): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        $companyId = static::companyId();
        if ($companyId === null || ! Schema::hasColumn('payment_methods', 'company_id')) {
            return $query;
        }

        $table = $query->getModel()->getTable();
        $qualified = str_contains($column, '.') ? $column : "{$table}.{$column}";

        return $query->whereIn($qualified, function ($q) use ($companyId) {
            $q->select('id')->from('payment_methods')->where('company_id', $companyId);
        });
    }

    /**
     * Data tanpa kolom pemilik tim: hanya SA / staf platform. Non-SA → kosong.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainPlatformOnlyQuery(Builder $query): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope ke company actor (1 WO = 1 Company).
     * Super admin: semua. Tanpa company_id: kosong.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainCompanyQuery(Builder $query, string $column = 'company_id'): Builder
    {
        $table = $query->getModel()->getTable();
        $col = str_contains($column, '.') ? explode('.', $column, 2)[1] : $column;
        if (! Schema::hasColumn($table, $col)) {
            return $query;
        }

        if (static::actorIsSuperAdmin()) {
            return $query;
        }

        $companyId = static::companyId();
        $qualified = str_contains($column, '.') ? $column : "{$table}.{$column}";

        if ($companyId === null) {
            return $query;
        }

        return $query->where($qualified, $companyId);
    }

    /**
     * Stempel company_id pada create (rekening, dll.).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function stampCompanyId(array $data, ?string $fromUserColumn = null): array
    {
        // Single-tenant penjualan: jangan paksa company_id jika kolom tidak ada di orders/users.
        if (! Schema::hasColumn('orders', 'company_id') && ! Schema::hasColumn('users', 'company_id')) {
            unset($data['company_id']);

            return $data;
        }

        if (static::actorIsSuperAdmin()) {
            if (empty($data['company_id']) && $fromUserColumn && ! empty($data[$fromUserColumn]) && Schema::hasColumn('users', 'company_id')) {
                $fromId = (int) $data[$fromUserColumn];
                $fromCompany = User::query()->whereKey($fromId)->value('company_id');
                if ($fromCompany) {
                    $data['company_id'] = (int) $fromCompany;
                }
            }

            return $data;
        }

        $companyId = static::companyId();

        if ($companyId !== null) {
            $data['company_id'] = $companyId;
        }

        return $data;
    }

    /**
     * Stempel company_id dari payment_method terkait (SA: isi jika kosong).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function stampCompanyIdFromPaymentMethod(array $data, string $column = 'payment_method_id'): array
    {
        if (! empty($data['company_id'])) {
            return static::stampCompanyId($data);
        }

        $pmId = isset($data[$column]) ? (int) $data[$column] : 0;
        if ($pmId > 0 && Schema::hasTable('payment_methods') && Schema::hasColumn('payment_methods', 'company_id')) {
            $fromPm = DB::table('payment_methods')->where('id', $pmId)->value('company_id');
            if ($fromPm) {
                $data['company_id'] = (int) $fromPm;
            }
        }

        if (empty($data['company_id']) && ! empty($data['order_id']) && Schema::hasColumn('orders', 'company_id')) {
            $fromOrder = DB::table('orders')->where('id', (int) $data['order_id'])->value('company_id');
            if ($fromOrder) {
                $data['company_id'] = (int) $fromOrder;
            }
        }

        if (empty($data['company_id']) && ! empty($data['piutang_id']) && Schema::hasColumn('piutangs', 'company_id')) {
            $fromPiutang = DB::table('piutangs')->where('id', (int) $data['piutang_id'])->value('company_id');
            if ($fromPiutang) {
                $data['company_id'] = (int) $fromPiutang;
            }
        }

        if (empty($data['company_id']) && ! empty($data['nota_dinas_id']) && Schema::hasColumn('nota_dinas', 'company_id')) {
            $fromNd = DB::table('nota_dinas')->where('id', (int) $data['nota_dinas_id'])->value('company_id');
            if ($fromNd) {
                $data['company_id'] = (int) $fromNd;
            }
        }

        return static::stampCompanyId($data);
    }

    /**
     * Scope record yang punya kolom pemilik (user_id / created_by) ke anggota tim.
     * Record dengan pemilik null hanya terlihat super_admin (katalog lama / platform).
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainOwnedQuery(Builder $query, string $column = 'user_id'): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        $teamIds = static::teamUserIds();

        if ($teamIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $teamIds);
    }

    /**
     * Nota dinas: pengirim / penerima / approver dalam tim.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainNotaDinasQuery(Builder $query): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        $teamIds = static::teamUserIds();

        if ($teamIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($teamIds) {
            $q->whereIn('pengirim_id', $teamIds)
                ->orWhereIn('penerima_id', $teamIds)
                ->orWhereIn('approved_by', $teamIds);
        });
    }

    /**
     * Detail ND: lewat order tim, atau (tanpa order) lewat nota dinas tim.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainNotaDinasDetailsQuery(Builder $query): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        $teamIds = static::teamUserIds();

        if ($teamIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($teamIds) {
            $q->whereIn('order_id', function ($sub) use ($teamIds) {
                $sub->select('id')->from('orders')->whereIn('user_id', $teamIds);
            })->orWhere(function (Builder $q2) use ($teamIds) {
                $q2->whereNull('order_id')
                    ->whereHas('notaDinas', function (Builder $nd) use ($teamIds) {
                        $nd->where(function (Builder $inner) use ($teamIds) {
                            $inner->whereIn('pengirim_id', $teamIds)
                                ->orWhereIn('penerima_id', $teamIds)
                                ->orWhereIn('approved_by', $teamIds);
                        });
                    });
            });
        });
    }

    /**
     * Pembayaran piutang lewat piutang.dibuat_oleh anggota tim.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainViaTeamPiutangs(Builder $query, string $piutangIdColumn = 'piutang_id'): Builder
    {
        if (static::actorSeesGlobalAggregates()) {
            return $query;
        }

        $teamIds = static::teamUserIds();

        if ($teamIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($piutangIdColumn, function ($q) use ($teamIds) {
            $q->select('id')->from('piutangs')->whereIn('dibuat_oleh', $teamIds);
        });
    }

    public static function ownsCompanyId(?int $recordCompanyId): bool
    {
        if (static::actorIsSuperAdmin()) {
            return true;
        }

        $companyId = static::companyId();

        return $companyId !== null
            && $recordCompanyId !== null
            && (int) $recordCompanyId === $companyId;
    }

    public static function canAccessUser(?User $target): bool
    {
        if (static::actorIsSuperAdmin()) {
            return true;
        }

        if (! $target) {
            return false;
        }

        $actor = Auth::user();

        if (! $actor instanceof User) {
            return false;
        }

        // Profil sendiri
        if ((int) $target->id === (int) $actor->id) {
            return true;
        }

        if (method_exists($target, 'hasRole') && $target->hasRole('super_admin')) {
            return false;
        }

        // Paket 1 seat: tidak kelola akun orang lain (meski masih ada data legacy).
        if (static::isSingleSeatPlan($actor)) {
            return false;
        }

        $root = static::teamRootId($actor);

        // Hanya pemilik paket (root tim) yang boleh kelola anggota lain
        if ($root === null || (int) $actor->id !== $root) {
            return false;
        }

        // Anggota company yang sama
        $companyId = static::companyId($actor);
        if (
            $companyId
            && Schema::hasColumn('users', 'company_id')
            && (int) $target->company_id === $companyId
        ) {
            return true;
        }

        // Fallback legacy: created_by = root
        return Schema::hasColumn('users', 'created_by')
            && (int) $target->created_by === $root;
    }

    /**
     * Apakah actor boleh mengedit user target (tombol Edit / update).
     */
    public static function canEditUser(?User $target): bool
    {
        return static::canAccessUser($target);
    }

    /**
     * Paket dengan maksimal 1 seat (Starter / Professional).
     * Tenant hanya kelola akun sendiri; tambah anggota = upgrade.
     */
    public static function isSingleSeatPlan(?User $actor = null): bool
    {
        if (static::actorIsSuperAdmin()) {
            return false;
        }

        $limit = CompanySubscription::seatLimit();

        return $limit !== null && $limit <= 1;
    }

    /**
     * Boleh menambah anggota tim (Create User) — pemilik paket + seat tersedia.
     */
    public static function canCreateTeamUser(): bool
    {
        if (static::actorIsSuperAdmin()) {
            return true;
        }

        if (! static::isTeamOwner()) {
            return false;
        }

        return CompanySubscription::hasSeatAvailable();
    }

    /**
     * Status Jabatan (bukan Role Spatie): SA, atau pemilik paket Business.
     */
    public static function canManageJobStatuses(): bool
    {
        if (static::actorIsSuperAdmin()) {
            return true;
        }

        return static::isTeamOwner()
            && CompanySubscription::planKey() === 'business';
    }

    /**
     * Filter ID status jabatan dari form (hanya ID yang valid di tabel statuses).
     *
     * @param  list<int|string>|null  $statusIds
     * @return list<int>
     */
    public static function sanitizeJobStatusIds(?array $statusIds): array
    {
        $selected = collect($statusIds ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($selected === []) {
            return [];
        }

        return \App\Models\Status::query()
            ->whereIn('id', $selected)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Nama role milik pemilik paket (root tim), tanpa super_admin.
     *
     * @return list<string>
     */
    public static function packageOwnerRoleNames(?User $actor = null): array
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            return ['pengunjung'];
        }

        $rootId = static::teamRootId($actor);
        $owner = $rootId ? User::query()->find($rootId) : $actor;
        $owner ??= $actor;

        $planLike = ['starter', 'professional', 'business', 'enterprise', 'hastana', 'non_hastana', 'lain_lain'];

        $names = $owner->getRoleNames()
            ->reject(function (string $name) use ($planLike): bool {
                $lower = strtolower($name);

                return $lower === 'super_admin' || in_array($lower, $planLike, true);
            })
            ->values()
            ->all();

        return $names !== [] ? $names : ['pengunjung'];
    }

    /**
     * @return list<int>
     */
    public static function packageOwnerRoleIds(?User $actor = null): array
    {
        $names = static::packageOwnerRoleNames($actor);

        return \Spatie\Permission\Models\Role::query()
            ->whereIn('name', $names)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public static function defaultPackageTeamRole(): string
    {
        return static::packageOwnerRoleNames()[0] ?? 'pengunjung';
    }

    /**
     * Nama role yang boleh dipilih actor. null = semua (super_admin).
     * Pemilik paket: hanya role yang sama dengan dirinya.
     *
     * @return list<string>|null
     */
    public static function assignableRoleNames(): ?array
    {
        if (static::actorIsSuperAdmin()) {
            return null;
        }

        return static::packageOwnerRoleNames();
    }

    /**
     * Filter ID role dari form.
     * Non–super_admin: hanya boleh role milik pemilik paket; kosong → pakai default pemilik.
     *
     * @param  list<int|string>|null  $roleIds
     * @return list<int>
     */
    public static function sanitizeAssignableRoleIds(?array $roleIds): array
    {
        if (static::actorIsSuperAdmin()) {
            return collect($roleIds ?? [])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $allowedIds = static::packageOwnerRoleIds();

        if ($allowedIds === []) {
            $default = \Spatie\Permission\Models\Role::findOrCreate('pengunjung', 'web');

            return [(int) $default->id];
        }

        $selected = collect($roleIds ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $filtered = array_values(array_intersect($selected, $allowedIds));

        return $filtered !== [] ? $filtered : $allowedIds;
    }

    /**
     * @return callable(Builder): Builder
     */
    public static function usersRelationshipConstraint(): callable
    {
        return static fn (Builder $query): Builder => static::constrainUsersQuery($query);
    }
}
