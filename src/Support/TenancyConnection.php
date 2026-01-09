<?php

namespace Snawbar\Tenancy\Support;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Snawbar\Tenancy\Exceptions\DatabaseCopyFailed;
use Snawbar\Tenancy\Exceptions\TenancyDatabaseException;
use Snawbar\Tenancy\Facades\Tenancy;
use Throwable;

class TenancyConnection
{
    private static ?Closure $connectUsing = NULL;

    private static ?Closure $migrateUsing = NULL;

    public static function connectUsing(Closure $callback): void
    {
        self::$connectUsing = $callback;
    }

    public static function migrateUsing(Closure $callback): void
    {
        self::$migrateUsing = $callback;
    }

    public function connect($credentials): void
    {
        throw_if(blank(self::$connectUsing), TenancyDatabaseException::class, 'No connection handler registered. Use TenancyConnection::connectUsing()');

        (self::$connectUsing)($credentials);
    }

    public function createDatabase(string $name, ?string $rootPassword = NULL): object
    {
        $databaseOwner = config()->string('snawbar-tenancy.main_domain_owner');
        $databaseName = $this->sanitizeDatabaseName($name);
        $user = sprintf('%s_usr', $databaseName);
        $password = str()->random(16);

        $connection = $this->rootConnection($rootPassword);

        try {
            $connection->statement(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', $databaseName));
            $connection->statement(sprintf("CREATE USER IF NOT EXISTS '%s'@'localhost' IDENTIFIED BY '%s'", $user, $password));
            $connection->statement(sprintf("ALTER USER '%s'@'localhost' IDENTIFIED BY '%s'", $user, $password));
            $connection->statement(sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost'", $databaseName, $user));
            $connection->statement(sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost'", $databaseName, $databaseOwner));
            $connection->statement('FLUSH PRIVILEGES');
        } catch (Throwable $throwable) {
            $this->rollbackDatabase($connection, $databaseName, $user);
            throw new TenancyDatabaseException(sprintf('Failed to create database: %s', $throwable->getMessage()), previous: $throwable);
        }

        return (object) [
            'database' => $databaseName,
            'username' => $user,
            'password' => $password,
        ];
    }

    public function copyDatabase(string $from, string $to, ?string $rootPassword = NULL): void
    {
        $fromTenant = Tenancy::firstOrFail($from);
        $toTenant = Tenancy::firstOrFail($to);

        $config = config()->array('snawbar-tenancy.database');
        $mysqldump = config()->string('snawbar-tenancy.mysql_dump_path');
        $mysql = config()->string('snawbar-tenancy.mysql_path');

        $credentials = [
            $config['host'],
            $config['port'],
            $config['username'],
            $rootPassword,
        ];

        $dumpCmd = sprintf('"%s" -h%s -P%s -u%s -p%s --single-transaction --quick %s', $mysqldump, ...$credentials, $fromTenant->database->database);
        $importCmd = sprintf('"%s" -h%s -P%s -u%s -p%s %s', $mysql, ...$credentials, $toTenant->database->database);

        $processResult = Process::pipe([$dumpCmd, $importCmd]);

        if ($processResult->failed()) {
            throw new DatabaseCopyFailed($from, $to, $processResult->errorOutput());
        }
    }

    public function deleteDatabase(object $credentials, ?string $rootPassword = NULL): void
    {
        $connection = $this->rootConnection($rootPassword);

        try {
            $connection->statement(sprintf('DROP DATABASE IF EXISTS `%s`', $credentials->database));
            $connection->statement(sprintf("DROP USER IF EXISTS '%s'@'localhost'", $credentials->username));
            $connection->statement('FLUSH PRIVILEGES');
        } catch (Throwable $throwable) {
            throw new TenancyDatabaseException(sprintf('Failed to delete database: %s', $throwable->getMessage()), previous: $throwable);
        }
    }

    public function migrate(object $credentials, ?Command $command = NULL): void
    {
        throw_if(blank(self::$migrateUsing), TenancyDatabaseException::class, 'No migration handler registered. Use TenancyConnection::migrateUsing()');

        $this->connect($credentials);

        (self::$migrateUsing)($command);
    }

    private function rootConnection(?string $password = NULL): Connection
    {
        $config = config()->array('snawbar-tenancy.database');

        return DB::build([
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => 'mysql',
            'username' => $config['username'],
            'password' => $password ?? $config['password'],
        ]);
    }

    private function rollbackDatabase(Connection $connection, string $database, string $user): void
    {
        try {
            $connection->statement(sprintf('DROP DATABASE IF EXISTS `%s`', $database));
            $connection->statement(sprintf("DROP USER IF EXISTS '%s'@'localhost'", $user));
            $connection->statement('FLUSH PRIVILEGES');
        } catch (Throwable) {
        }
    }

    private function sanitizeDatabaseName(string $name): string
    {
        return str($name)
            ->lower()
            ->trim()
            ->replaceMatches('/[^a-z0-9_]/', '_')
            ->substr(0, 16)
            ->value();
    }
}
