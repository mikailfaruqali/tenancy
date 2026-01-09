<?php

namespace Snawbar\Tenancy\Commands;

use Illuminate\Console\Command;
use Snawbar\Tenancy\Facades\Tenancy;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class TenancyCreateCommand extends Command
{
    protected $signature = 'tenancy:create';

    public function handle(): void
    {
        $tenantName = text(
            label: 'Tenant Name?',
            required: TRUE,
            validate: ['name' => 'required|regex:/^[a-z0-9-]+$/'],
        );

        $mysqlRootPassword = password(
            label: 'MySQL Root Password?',
        );

        $fluent = Tenancy::create($tenantName, $mysqlRootPassword);

        Tenancy::migrate($fluent->database, $this);

        $this->components->info(sprintf('Tenant created: %s', $fluent->subdomain));
    }
}
