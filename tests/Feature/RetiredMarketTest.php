<?php

namespace Tests\Feature;

use App\Models\{Notification, Region, Service};
use App\Services\NotificationDeliveryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Http, Route, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RetiredMarketTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_market_catalog_seller_and_admin_actions_are_no_longer_registered(): void
    {
        foreach (Route::getRoutes() as $route) {
            $this->assertDoesNotMatchRegularExpression('~(^|/)(pasar-daerah|toko-warga)(/|$)~', $route->uri());
            $this->assertDoesNotMatchRegularExpression('/PasarDaerah|MarketStore|MarketShipping/', $route->getActionName());
        }

        foreach (['user', 'admin_rt', 'admin_rw', 'admin_desa', 'super_admin'] as $role) {
            $user = $this->user(1);
            $user->role = $role;
            $this->actingAs($user, 'sanctum');
            $this->getJson('/pasar-daerah')->assertNotFound();
            $this->getJson('/api/pasar-daerah/products')->assertNotFound();
            $this->postJson('/api/pasar-daerah/checkout')->assertNotFound();
            $this->postJson('/admin/toko-warga/1', ['status' => 'approved'])->assertNotFound();
        }
    }

    public function test_market_cannot_be_reactivated_and_existing_data_survives_retirement(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug');
        });
        Schema::create('region_services', function (Blueprint $table) {
            $table->id(); $table->integer('region_id'); $table->integer('service_id');
            $table->boolean('is_active'); $table->boolean('is_exclusive')->default(false); $table->timestamps();
        });
        Schema::create('pasar_orders', function (Blueprint $table) {
            $table->id(); $table->string('status');
        });
        DB::table('services')->insert([
            ['id' => 1, 'name' => 'Pasar Daerah', 'slug' => 'pasar-daerah'],
            ['id' => 2, 'name' => 'Penjualan Gas', 'slug' => 'penjualan-gas'],
        ]);
        foreach ([1, 2] as $id) {
            DB::table('region_services')->insert(['region_id' => 1, 'service_id' => $id, 'is_active' => true]);
        }
        DB::table('pasar_orders')->insert(['id' => 1, 'status' => 'in_delivery']);

        // Even before migrating, a stale active pivot cannot advertise the retired service.
        $this->assertSame(['penjualan-gas'], Region::findOrFail(1)->services->pluck('slug')->all());
        $migration = require database_path('migrations/2026_11_11_000001_retire_pasar_daerah.php');
        $migration->up();
        $migration->up();
        $this->assertSame([2], DB::table('region_services')->where('is_active', true)->pluck('service_id')->all());
        $this->assertSame(['penjualan-gas'], Service::available()->pluck('slug')->all());
        $this->artisan('pasar:auto-complete')->assertSuccessful();
        $this->assertSame('in_delivery', DB::table('pasar_orders')->value('status'));

        $this->actingAs($this->user(4));
        foreach ([\App\Http\Controllers\Admin\RegionSettingController::class, \App\Http\Controllers\Admin\SystemSettingController::class] as $controller) {
            try {
                app($controller)->update(Request::create('/', 'POST', ['services' => [1]]));
                $this->fail('A retired market service was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('services.0', $exception->errors());
            }
        }
    }

    public function test_old_market_notifications_are_not_delivered_or_opened(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('aktif'); $table->string('fcm_token')->nullable();
        });
        (require database_path('migrations/2026_10_28_130000_add_notification_delivery_channels.php'))->up();
        config(['notification_channels.enabled' => true]);
        DB::table('users')->where('id', 1)->update(['phone' => '08123456789']);
        $user = $this->user(1);
        $service = app(NotificationDeliveryService::class);
        DB::table('notification_settings')->insert([
            'user_id' => $user->id, 'consented_at' => now(), 'push_enabled' => false,
            'verified_phone_hash' => $service->phoneHash($user->phone),
            'categories' => json_encode(['market' => 'instant', 'orders' => 'instant']),
        ]);

        foreach (['/pasar-daerah/1', '/aktivitas?tab=pasar&order=1'] as $path) {
            $notice = Notification::create(['user_id' => $user->id, 'type' => 'produk_baru', 'title' => 'Produk lama', 'link' => url($path)]);
            $this->assertSame(0, DB::table('notification_deliveries')->count());
            $delivery = (object) ['source_kind' => 'user', 'source_id' => $notice->id, 'category' => 'market'];
            $this->assertNull($service->source($delivery, $user));
            $this->assertSame(route('user.notifications'), $service->destination($notice));
        }
        Http::assertNothingSent();
    }
}
