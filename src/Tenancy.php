<?php

namespace Snawbar\Tenancy;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Snawbar\Tenancy\Commands\TenancyDeleteCommand;
use Snawbar\Tenancy\Commands\TenancyUpgradeCommand;
use Snawbar\Tenancy\Exceptions\TenancyAlreadyExists;
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;
use Snawbar\Tenancy\Middleware\InitializeTenancy;
use Snawbar\Tenancy\Support\TenancyConnection;
use Snawbar\Tenancy\Support\TenancyHealth;
use Snawbar\Tenancy\Support\TenancyRepository;

class Tenancy
{
    public function __construct(
        private readonly TenancyRepository $tenancyRepository,
        private readonly TenancyConnection $tenancyConnection,
        private readonly TenancyHealth $tenancyHealth,
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

    public static function afterUpgradeUsing(Closure $callback): void
    {
        TenancyUpgradeCommand::afterUpgradeUsing($callback);
    }

    public static function afterDeleteUsing(Closure $callback): void
    {
        TenancyDeleteCommand::afterDeleteUsing($callback);
    }

    public static function healthUsing(Closure $callback): void
    {
        TenancyHealth::using($callback);
    }

    public function health(object $tenant): array
    {
        return $this->tenancyHealth->check($tenant);
    }

    public function withHealth(): Collection
    {
        $tenants = $this->all();

        foreach ($tenants as $tenant) {
            $tenant->health = $this->tenancyHealth->check($tenant);
        }

        return $tenants;
    }

    public function all(): Collection
    {
        return $this->tenancyRepository->all();
    }

    public function find(string $subdomain): ?object
    {
        return $this->tenancyRepository->find($subdomain);
    }

    public function findOrFail(string $subdomain): object
    {
        return $this->tenancyRepository->findOrFail($subdomain);
    }

    public function exists(string $subdomain): bool
    {
        return $this->tenancyRepository->exists($subdomain);
    }

    public function connectWithSubdomain(string $subdomain): void
    {
        $tenant = $this->tenancyRepository->findOrFail($subdomain);

        $this->tenancyConnection->connect($tenant->database);
    }

    public function connectWithCredentials(object $credentials): void
    {
        $this->tenancyConnection->connect($credentials);
    }

    public function migrate(object $tenant, ?Command $command = NULL): void
    {
        $this->tenancyConnection->migrate($tenant->database, $command);
    }

    public function create(string $name, ?string $rootPassword = NULL): object
    {
        $subdomain = sprintf('%s.%s', $name, config()->string('snawbar-tenancy.domain'));

        throw_if($this->exists($subdomain), TenancyAlreadyExists::class, $subdomain);

        $database = $this->tenancyConnection->createDatabase($name, $rootPassword);

        return $this->tenancyRepository->add([
            'subdomain' => $subdomain,
            'database' => $database,
        ]);
    }

    public function delete(object $tenant, ?string $rootPassword = NULL): void
    {
        $this->tenancyConnection->deleteDatabase($tenant->database, $rootPassword);
        $this->tenancyRepository->remove($tenant->subdomain);
    }
}
