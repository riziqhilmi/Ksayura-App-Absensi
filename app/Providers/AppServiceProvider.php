<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS untuk semua asset
        // if (app()->environment('production') || app()->environment('local')) {
            // URL::forceScheme('https');
        // }
    }
}