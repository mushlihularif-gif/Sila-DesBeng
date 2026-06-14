<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Policies\RentalBookingPolicy;
use App\Policies\GasOrderPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for automatic receipt generation
        \App\Models\RentalBooking::observe(\App\Observers\RentalBookingObserver::class);
        \App\Models\GasOrder::observe(\App\Observers\GasOrderObserver::class);

        // =====================================================
        // KEAMANAN: Laravel Authorization (Gates & Policies)
        // Bertindak sebagai "Satpam Logika" di level Controller.
        // =====================================================

        // Model Policies (IDOR Protection - cek kepemilikan data)
        Gate::policy(RentalBooking::class, RentalBookingPolicy::class);
        Gate::policy(GasOrder::class, GasOrderPolicy::class);

        // Gate Definitions (Function-Level Authorization)
        // Hanya admin yang boleh melakukan operasi kritis berikut:
        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-settings', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('view-reports', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('clear-logs', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('verify-transaction', function ($user) {
            return $user->role === 'admin';
        });
    }
}
