<?php

namespace Snawbar\Tenancy\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class TenancyHealth
{
    private static $using;

    public static function using(callable $callback): void
    {
        self::$using = $callback;
    }

    public static function check(object $tenant): array
    {
        if (blank(self::$using)) {
            return [
                'status' => 'unknown',
            ];
        }

        return call_user_func(self::$using, self::createConnection($tenant));
    }

    private static function createConnection(object $tenant): Connection
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
