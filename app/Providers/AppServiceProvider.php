<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with([
                'appNameSetting' => \App\Models\Setting::get('company_name', 'WarungPro'),
                'appTaglineSetting' => \App\Models\Setting::get('company_tagline', 'Sistem Kasir & Manajemen Retail Profesional'),
                'appLogoSetting' => \App\Models\Setting::get('company_logo', null),
            ]);
        });
    }
}
