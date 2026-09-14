<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        // Implicitly grant "super_admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        View::composer('*', function ($view) {
            $view->with([
                'appNameSetting' => Setting::get('company_name', 'WarungPro'),
                'appTaglineSetting' => Setting::get('company_tagline', 'Sistem Kasir & Manajemen Retail Profesional'),
                'appLogoSetting' => Setting::get('company_logo', null),
            ]);
        });
    }
}
