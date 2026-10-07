<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductCreatedNotifier;
use App\Services\ProjectActivityNotifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileNotificationApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'wofins.license.enabled' => false,
            'activitylog.enabled' => false,
            'services.apns.key_id' => '',
        ]);

        $this->createSchema();
    }

    public function test_notification_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('api.v1.notifications.index'));
        $this->assertTrue(Route::has('api.v1.notifications.read'));
        $this->assertTrue(Route::has('api.v1.me.push-token.store'));
        $this->assertTrue(Route::has('api.v1.me.push-token.destroy'));
    }

    public function test_product_and_project_notifications_reach_the_ios_api(): void
    {
        $user = User::withoutEvents(fn () => User::query()->create([
            'name' => 'Staf Aktif',
            'email' => 'aktif@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]));
        User::withoutEvents(fn () => User::query()->create([
            'name' => 'Staf Nonaktif',
            'email' => 'nonaktif@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'inactive',
        ]));

        $product = new Product(['name' => 'Paket Gold']);
        $product->id = 9;
        $product->exists = true;
        app(ProductCreatedNotifier::class)->notify($product, 'created');

        $product->syncOriginal();
        $product->last_edited_by_id = $user->id;
        $product->syncChanges();
        app(ProductCreatedNotifier::class)->notify($product, 'updated');

        $product->name = 'Paket Gold Baru';
        $product->syncChanges();
        app(ProductCreatedNotifier::class)->notify($product, 'updated');

        $order = new Order(['name' => 'Pernikahan Adi']);
        $order->id = 4;
        $order->exists = true;
        app(ProjectActivityNotifier::class)->notify($order, 'created');

        Sanctum::actingAs($user, ['mobile']);

        $this->putJson('/api/v1/me/push-token', [
            'token' => str_repeat('ab', 32),
            'environment' => 'sandbox',
        ])->assertOk()
            ->assertJsonPath('message', 'Token push tersimpan.');

        $list = $this->getJson('/api/v1/notifications');
        $list->assertOk();
        $types = collect($list->json('data'))->pluck('type')->all();
        $this->assertSame(['project_created', 'product_updated', 'product_created'], $types);
        $this->assertSame(3, $list->json('unread_count'));
        $this->assertSame(3, \App\Models\AppNotification::query()->count());

        $updated = collect($list->json('data'))->firstWhere('type', 'product_updated');
        $this->assertMatchesRegularExpression('/^\d{2} \p{L}+ \d{4}, \d{2}\.\d{2}$/u', $updated['created_label']);
        $this->assertStringNotContainsString('OktoberOktober', $updated['created_label']);
        $this->assertSame(9, $updated['product_id']);
        $this->assertSame('Paket Gold Baru berhasil diperbarui.', $updated['body']);

        $project = collect($list->json('data'))->firstWhere('type', 'project_created');
        $this->assertSame(4, $project['order_id']);

        $this->postJson('/api/v1/notifications/'.$updated['id'].'/read')
            ->assertOk()
            ->assertJsonPath('unread_count', 2);

        $this->getJson('/api/v1/notifications?count=1')
            ->assertOk()
            ->assertJsonPath('unread_count', 2)
            ->assertJsonPath('data', []);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('device_push_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->nullable();
            $table->timestamp('expire_date')->nullable();
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

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type', 40);
            $table->string('title');
            $table->string('body', 500);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('device_push_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 200)->unique();
            $table->string('environment', 16);
            $table->string('purpose', 16)->default('alert');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
        });
    }
}
