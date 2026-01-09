<?php

namespace Snawbar\Tenancy\Facades;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void connectUsing(Closure $callback)
 * @method static void migrateUsing(Closure $callback)
 * @method static Collection all()
 * @method static object|null find(string $subdomain)
 * @method static object findOrFail(string $subdomain)
 * @method static bool exists(string $subdomain)
 * @method static void connectWithSubdomain(string $subdomain)
 * @method static void connectWithCredentials(object $credentials)
 * @method static void migrate(object $credentials, ?Command $command = null)
 * @method static object create(string $name, string $subdomain, ?string $rootPassword = null)
 * @method static void delete(string $subdomain, ?string $rootPassword = null)
 */
class Tenancy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Snawbar\Tenancy\Tenancy::class;
    }
}
