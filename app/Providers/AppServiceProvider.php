<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('zoom.service', fn() => new \App\Services\ZoomService());
        $this->app->singleton('googlemeet.service', fn() => new \App\Services\GoogleMeetService());
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when the app URL is configured with https (e.g. behind ngrok or a reverse proxy)
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            \App\Listeners\LogFailedLogin::class
        );
    }
}
