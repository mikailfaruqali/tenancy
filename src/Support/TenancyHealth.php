<?php

namespace Snawbar\Tenancy\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class TenancyHealth
{
    protected static $using;

    public static function using(callable $callback): void
    {
        static::$using = $callback;
    }

    public static function check(object $tenant): array
    {
        if (blank(static::$using)) {
            return [
                'status' => 'unknown',
            ];
        }

        return call_user_func(static::$using, static::createConnection($tenant));
    }

    protected static function createConnection(object $tenant): Connection
    {
        return DB::build([
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => $tenant->database->database,
            'username' => $tenant->database->username,
            'password' => $tenant->database->password,
        ]);
    }
}
