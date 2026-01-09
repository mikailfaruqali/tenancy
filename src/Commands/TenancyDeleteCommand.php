<?php

namespace Snawbar\Tenancy\Commands;

use Closure;
use Illuminate\Console\Command;
use Snawbar\Tenancy\Facades\Tenancy;

use function Laravel\Prompts\password;
use function Laravel\Prompts\search;

class TenancyDeleteCommand extends Command
{
    protected $signature = 'tenancy:delete';

        private static ?Closure $afterDeleteUsing = null;

    public static function afterDeleteUsing(Closure $callback): void
    {
        static::$afterDeleteUsing = $callback;
    }

    public function handle(): void
    {
        $subdomain = search(
            label: 'Select Tenant',
            options: fn(string $query) => Tenancy::all()
                ->filter(fn($tenant) => str_contains($tenant->subdomain, $query))
                ->pluck('subdomain', 'subdomain')
                ->toArray(),
            required: true,
        );

        $mysqlRootPassword = password(
            label: 'MySQL Root Password?',
        );

        Tenancy::delete($subdomain, $mysqlRootPassword);

        if (static::$afterDeleteUsing) {
            (static::$afterDeleteUsing)($subdomain, $this);
        }

        $this->components->info(sprintf('Tenant deleted: %s', $subdomain));
    }
}