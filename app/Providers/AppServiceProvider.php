<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PaystackService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind PaystackService as a singleton so the same instance is used throughout the app
        $this->app->singleton(PaystackService::class, fn() => new PaystackService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // You can place any boot‑strapping logic here if needed
    }
}
