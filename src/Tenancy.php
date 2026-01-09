<?php

namespace Snawbar\Tenancy;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Snawbar\Tenancy\Commands\TenancyDeleteCommand;
use Snawbar\Tenancy\Commands\TenancyUpgradeCommand;
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;
use Snawbar\Tenancy\Middleware\InitializeTenancy;
use Snawbar\Tenancy\Support\TenancyConnection;
use Snawbar\Tenancy\Support\TenancyRepository;

class Tenancy
{
    public function __construct(
        private readonly TenancyRepository $tenancyRepository,
        private readonly TenancyConnection $tenancyConnection
    ) {}

    public static function connectUsing(Closure $callback): void
    {
        TenancyConnection::connectUsing($callback);
    }

    public static function migrateUsing(Closure $callback): void
    {
        TenancyConnection::migrateUsing($callback);
    }

    public static function ensureMainTenantUsing(Closure $callback): void
    {
        EnsureMainTenancy::validateUsing($callback);
    }

    public static function afterConnectUsing(Closure $callback): void
    {
        InitializeTenancy::afterConnectUsing($callback);
    }

    public function all(): Collection
    {
        return $this->tenancyRepository->all();
    }

    public function find(string $subdomain): ?Fluent
    {
        return $this->tenancyRepository->find($subdomain);
    }

    public function findOrFail(string $subdomain): Fluent
    {
        return $this->tenancyRepository->findOrFail($subdomain);
    }

    public function exists(string $subdomain): bool
    {
        return $this->tenancyRepository->exists($subdomain);
    }

    public function connect(string $subdomain): void
    {
        $tenant = $this->tenancyRepository->findOrFail($subdomain);

        $this->tenancyConnection->connect($tenant->database);
    }

    public function migrate(Fluent $fluent, ?Command $command = NULL): void
    {
        $this->tenancyConnection->migrate($fluent, $command);
    }

    public function create(string $name, ?string $rootPassword = NULL): Fluent
    {
        $subdomain = sprintf('%s.%s', $name, config()->string('snawbar-tenancy.domain'));

        $credentials = $this->tenancyConnection->createDatabase($name, $rootPassword);

        return $this->tenancyRepository->add([
            'subdomain' => $subdomain,
            'database' => $credentials,
        ]);
    }

    public function delete(string $subdomain, ?string $rootPassword = NULL): void
    {
        $tenant = $this->tenancyRepository->findOrFail($subdomain);

        $this->tenancyConnection->deleteDatabase($tenant, $rootPassword);
        $this->tenancyRepository->remove($subdomain);
    }

    public static function afterUpgradeUsing(Closure $callback): void
    {
        TenancyUpgradeCommand::afterUpgradeUsing($callback);
    }

    public static function afterDeleteUsing(Closure $callback): void
    {
        TenancyDeleteCommand::afterDeleteUsing($callback);
    }
}
