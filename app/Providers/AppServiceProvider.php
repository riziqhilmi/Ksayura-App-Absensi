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
        // Force HTTPS jika request dari Cloudflare/proxy
        if (
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
            request()->header('X-Forwarded-Proto') === 'https' ||
            str_contains(request()->header('Host', ''), 'trycloudflare.com') ||
            str_contains(request()->header('Host', ''), 'ngrok')
        ) {
            URL::forceScheme('https');
        }
    }
}