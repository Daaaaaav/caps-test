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
        $this->app->singleton('zoom.service', fn() => new \App\Services\ZoomService());
        $this->app->singleton('googlemeet.service', fn() => new \App\Services\GoogleMeetService());
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
