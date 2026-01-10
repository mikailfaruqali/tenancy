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

    private static ?Closure $afterDeleteUsing = NULL;

    public static function afterDeleteUsing(Closure $callback): void
    {
        self::$afterDeleteUsing = $callback;
    }

    public function handle(): void
    {
        $tenants = Tenancy::all();

        $subdomain = search(
            label: 'Select Tenant',
            options: fn (?string $query) => $tenants
                ->when($query, fn ($tenants) => $tenants->filter(fn ($tenant) => str_contains((string) $tenant->subdomain, (string) $query)))
                ->pluck('subdomain')
                ->toArray(),
            validate: fn (?string $value) => when(blank($value), 'Please select a tenant'),
        );

        $rootPassword = password(
            label: 'MySQL Root Password ?',
            required: TRUE,
        );

        $selectedTenant = $tenants->firstWhere('subdomain', $subdomain);

        Tenancy::delete($selectedTenant, $rootPassword);

        if (self::$afterDeleteUsing instanceof Closure) {
            (self::$afterDeleteUsing)($subdomain, $this);
        }

        $this->components->info(sprintf('Tenant deleted: %s', $subdomain));
    }
}
