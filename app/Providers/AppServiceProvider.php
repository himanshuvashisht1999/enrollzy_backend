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

        // Ranking / accreditation records store 'organisation' | 'campus' | 'department' | 'course', not class names.
        \App\Support\Quality\Levels::register();

        // Public counselling API: generous for reads, tight for anything that sends mail / WhatsApp.
        // The student website calls the API from its own server (one IP for every visitor): it sends the shared
        // CNS_SITE_KEY and is not limited. Everyone else: 120 reads / minute / IP.
        \Illuminate\Support\Facades\RateLimiter::for('cns-public', function ($request) {
            $key = (string) config('cns.site_key');
            if ($key !== '' && hash_equals($key, (string) $request->header('X-Site-Key'))) {
                return \Illuminate\Cache\RateLimiting\Limit::none();
            }
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(120)->by($request->ip());
        });
        \Illuminate\Support\Facades\RateLimiter::for('cns-follow', fn ($request) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by($request->ip()),
            \Illuminate\Cache\RateLimiting\Limit::perHour(5)->by(strtolower((string) ($request->input('email') ?: $request->input('phone') ?: $request->ip()))),
        ]);

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
