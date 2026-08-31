<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $host = env('RAILWAY_PUBLIC_DOMAIN');

        if (is_string($host) && $host !== '') {
            URL::forceRootUrl('https://'.$host);
        }
    }
}
