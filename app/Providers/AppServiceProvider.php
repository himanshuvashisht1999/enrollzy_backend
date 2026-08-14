<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\View;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('superadmin'))) {
                return true;
            }
            if (isset($user->is_admin) && $user->is_admin) {
                return true;
            }
            return null;
        });

        try {
            if (Schema::hasTable('settings')) {
                $site_settings = Setting::first();
                View::share('site_settings', $site_settings);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not connected
        }
    }
}
