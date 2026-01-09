<?php

namespace Snawbar\Tenancy;

use Illuminate\Support\ServiceProvider;
use Snawbar\Tenancy\Support\TenancyConnection;
use Snawbar\Tenancy\Support\TenancyRepository;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenancyRepository::class);
        $this->app->singleton(TenancyConnection::class);
        $this->app->singleton(Tenancy::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->publishConfig();
    }

    private function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'snawbar-tenancy');
    }

    private function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tenancy.php' => config_path('snawbar-tenancy.php'),
            ], 'snawbar-tenancy-config');

            $this->publishes([
                __DIR__ . '/../views' => resource_path('views/vendor/snawbar-tenancy'),
            ], 'snawbar-tenancy-views');
        }
    }
}
