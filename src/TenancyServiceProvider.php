<?php

namespace Snawbar\Tenancy;

use Illuminate\Support\ServiceProvider;
use Snawbar\Tenancy\Commands\TenancyCreateCommand;
use Snawbar\Tenancy\Commands\TenancyDeleteCommand;
use Snawbar\Tenancy\Commands\TenancyUpgradeCommand;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerViews();
        $this->publishConfig();
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TenancyCreateCommand::class,
                TenancyDeleteCommand::class,
                TenancyUpgradeCommand::class,
            ]);
        }
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
