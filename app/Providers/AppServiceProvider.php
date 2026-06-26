<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
        // =====================================================
        // MACRO: Global Smart Search
        // =====================================================
        Builder::macro('searchWhereLike', function ($attributes, string $searchTerm) {
            $noiseWords = ['desa', 'kelurahan', 'kecamatan', 'kabupaten', 'provinsi', 'rt', 'rw', 'dusun'];
            $cleanSearch = strtolower($searchTerm);
            
            // Remove noise words
            foreach ($noiseWords as $noise) {
                // Remove word if it's a distinct word boundary
                $cleanSearch = trim(preg_replace('/\b' . preg_quote($noise, '/') . '\b/u', '', $cleanSearch));
            }

            // Fallback: If user ONLY typed noise words (e.g. exactly "desa"), 
            // then search for that exact word instead of empty string.
            if (empty($cleanSearch)) {
                $cleanSearch = trim(strtolower($searchTerm));
            }

            $terms = array_filter(explode(' ', $cleanSearch));

            $this->where(function ($query) use ($attributes, $terms) {
                foreach ($terms as $term) {
                    $query->where(function ($subQuery) use ($attributes, $term) {
                        foreach (Arr::wrap($attributes) as $attribute) {
                            $subQuery->orWhere($attribute, 'LIKE', "%{$term}%");
                        }
                    });
                }
            });

            return $this;
        });

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
