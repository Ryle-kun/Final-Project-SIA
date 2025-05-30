<?php

namespace App\Providers;

use App\Services\TodoistService;
use App\Services\WeatherService;
use App\Services\CalendarificService;
use App\Services\TimezoneService;
use App\Services\QuoteService;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TodoistService::class, function ($app) {
            return new TodoistService();
        });

        $this->app->singleton(WeatherService::class, function ($app) {
            return new WeatherService();
        });

        $this->app->singleton(CalendarificService::class, function ($app) {
            return new CalendarificService();
        });

        $this->app->singleton(TimezoneService::class, function ($app) {
            return new TimezoneService();
        });

        $this->app->singleton(QuoteService::class, function ($app) {
            return new QuoteService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
