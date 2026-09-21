<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileAuthApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'wofins.license.enabled' => false,
            'activitylog.enabled' => false,
        ]);

        $this->createMinimalAuthSchema();
    }

    public function test_mobile_auth_routes_are_registered_and_throttled(): void
    {
        $this->assertTrue(Route::has('api.v1.auth.login'));
        $this->assertTrue(Route::has('api.v1.auth.logout'));
        $this->assertTrue(Route::has('api.v1.me'));
        $this->assertTrue(Route::has('api.v1.finance.dashboard'));

        $login = Route::getRoutes()->getByName('api.v1.auth.login');
        $this->assertNotNull($login);
        $this->assertTrue(collect($login->gatherMiddleware())->contains(
            fn ($middleware) => is_string($middleware) && str_contains($middleware, 'throttle:10,1')
        ));
    }

    public function test_login_issues_mobile_token(): void
    {
        $user = User::withoutEvents(fn () => User::query()->create([
            'name' => 'Mobile User',
            'email' => 'mobile@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]));

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'secret123',
            'device_name' => 'iphone-test',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'mobile@example.com')
            ->assertJsonStructure(['token', 'expires_at', 'user' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'iphone-test',
        ]);
    }

    public function test_login_rejects_invalid_password(): void
    {
        User::withoutEvents(fn () => User::query()->create([
            'name' => 'Mobile User',
            'email' => 'mobile@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]));

        $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'wrong',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_me_requires_mobile_ability_token(): void
    {
        $user = User::withoutEvents(fn () => User::query()->create([
            'name' => 'Mobile User',
            'email' => 'me@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]));

        Sanctum::actingAs($user, ['mobile']);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::withoutEvents(fn () => User::query()->create([
            'name' => 'Mobile User',
            'email' => 'out@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]));

        $token = $user->createToken('iphone-test', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil.');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_dashboard_returns_cash_summary_for_authorized_user(): void
    {
        $user = User::withoutEvents(fn () => User::query()->create([
            'name' => 'Finance User',
            'email' => 'finance@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]));

        $roleId = Schema::hasTable('roles')
            ? \Illuminate\Support\Facades\DB::table('roles')->insertGetId([
                'name' => 'super_admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            : null;

        if ($roleId) {
            \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }

        $token = $user->createToken('iphone-test', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/finance/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'period' => ['from', 'to'],
                    'inflow' => ['wedding_payments', 'other_income', 'total'],
                    'outflow' => ['wedding_expenses', 'operational', 'other_expenses', 'total'],
                    'net_cash',
                    'comparison' => [
                        'period' => ['from', 'to'],
                        'previous_inflow',
                        'previous_outflow',
                        'previous_net_cash',
                    ],
                ],
            ]);
    }

    private function createMinimalAuthSchema(): void
    {
        foreach ([
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'personal_access_tokens',
            'companies',
            'app_licenses',
            'data_pembayarans',
            'pendapatan_lains',
            'expenses',
            'expense_ops',
            'pengeluaran_lains',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->nullable();
            $table->timestamp('expire_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('department')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('last_working_date')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('inisial_wo')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('jabatan_owner')->nullable();
            $table->unsignedInteger('established_year')->nullable();
            $table->timestamps();
        });

        Schema::create('app_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('company_name')->nullable();
            $table->string('package')->nullable();
            $table->string('domain')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('data_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->integer('nominal')->default(0);
            $table->date('tgl_bayar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('pendapatan_lains', function (Blueprint $table) {
            $table->id();
            $table->integer('nominal')->default(0);
            $table->date('tgl_bayar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->default(0);
            $table->date('date_expense')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('expense_ops', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->default(0);
            $table->date('date_expense')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('pengeluaran_lains', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->default(0);
            $table->date('date_expense')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
