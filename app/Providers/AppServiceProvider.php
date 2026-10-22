<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Standarisasi zona waktu WIB dan lokal Bahasa Indonesia
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
        Carbon::setLocale(config('app.locale', 'id'));

        // Register Gate before callback for Super Admin & Admin roles
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
            if ($user->hasRole('admin') && $ability !== 'manage system') {
                return true;
            }
            return null;
        });
    }
}
