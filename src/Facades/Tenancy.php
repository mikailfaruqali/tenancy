<?php

namespace Snawbar\Tenancy\Facades;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * Tenancy Facade
 *
 * Configuration hooks
 * -------------------
 *
 * @method static void connectUsing(Closure $callback)
 * @method static void migrateUsing(Closure $callback)
 * @method static void ensureMainTenantUsing(Closure $callback)
 * @method static void afterConnectUsing(Closure $callback)
 * @method static void afterUpgradeUsing(Closure $callback)
 * @method static void afterDeleteUsing(Closure $callback)
 * @method static void healthUsing(Closure $callback)
 *
 * Runtime API
 * -----------
 * @method static Collection all()
 * @method static object|null find(string $subdomain)
 * @method static object findOrFail(string $subdomain)
 * @method static bool exists(string $subdomain)
 * @method static array health(object $tenant)
 *
 * Connection & migration
 * ----------------------
 * @method static void connectWithSubdomain(string $subdomain)
 * @method static void connectWithCredentials(object $credentials)
 * @method static void migrate(object $tenant, ?Command $command = null)
 *
 * Tenant lifecycle
 * ----------------
 * @method static object create(string $name, ?string $rootPassword = null)
 * @method static void delete(object $tenant, ?string $rootPassword = null)
 */
class Tenancy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Snawbar\Tenancy\Tenancy::class;
    }
}
