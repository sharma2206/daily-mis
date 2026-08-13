<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MISService;
use App\Services\CsvProcessingService;

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
        $this->app->singleton(MISService::class);
        $this->app->singleton(CsvProcessingService::class);
    }
}
