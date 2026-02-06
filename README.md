# Snawbar Tenancy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)
[![Total Downloads](https://img.shields.io/packagist/dt/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)
[![License](https://img.shields.io/packagist/l/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)

A simple **database-per-tenant** multi-tenancy package for Laravel. Tenants are identified by subdomain, each gets its own MySQL database and user, and the tenant registry is stored in a fast JSON file — no extra database table required.

---

## Features

- **Database-per-tenant** — every tenant gets a dedicated MySQL database and user
- **Subdomain identification** — tenants are resolved automatically from the request host
- **File-based registry** — tenant list is kept in a JSON file for speed (no extra DB table)
- **Artisan commands** — `tenancy:create`, `tenancy:delete`, `tenancy:upgrade`
- **Web management UI** — built-in pages to list, search, sort, and create tenants
- **Health monitoring** — plug in your own health-check callback and see results in the UI
- **Middleware** — automatic tenant connection switching + main-domain guard
- **Database cloning** — copy one tenant's database to another
- **Lifecycle hooks** — callbacks for connect, migrate, upgrade, delete, and health
- **Custom 404 page** — rendered automatically when a tenant subdomain is not found

---

## Requirements

- PHP >= 8.2
- Laravel >= 11.0
- MySQL (the DB user needs `CREATE DATABASE`, `CREATE USER`, and `GRANT` privileges)

---

## Installation

### 1. Require the package

```bash
composer require mikailfaruqali/tenancy
```

The service provider is auto-discovered — no manual registration needed.

### 2. Publish the config

```bash
php artisan vendor:publish --tag=snawbar-tenancy-config
```

Creates `config/snawbar-tenancy.php`.

### 3. (Optional) Publish the views

```bash
php artisan vendor:publish --tag=snawbar-tenancy-views
```

Publishes Blade views to `resources/views/vendor/snawbar-tenancy/`.

### 4. Configure your `.env`

```env
TENANCY_ENABLED=true
TENANCY_DOMAIN=yourdomain.com
TENANCY_MAIN_DOMAIN=yourdomain.com
TENANCY_MAIN_DOMAIN_OWNER=root

TENANCY_DB_HOST=127.0.0.1
TENANCY_DB_PORT=3306
TENANCY_DB_USERNAME=root
TENANCY_DB_PASSWORD=secret
```

### 5. Create the storage directory

```bash
mkdir storage/tenancy
```

The package stores every tenant's credentials in `storage/tenancy/tenants.json` (created automatically on first use).

---

## Setup

Register two required callbacks in your `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\DB;
use Snawbar\Tenancy\Facades\Tenancy;

public function boot(): void
{
    // 1. Tell the package HOW to connect to a tenant database
    Tenancy::connectUsing(function (object $credentials) {
        config([
            'database.connections.tenant' => [
                'driver'    => 'mysql',
                'host'      => config('database.connections.mysql.host'),
                'port'      => config('database.connections.mysql.port'),
                'database'  => $credentials->database,
                'username'  => $credentials->username,
                'password'  => $credentials->password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        ]);

        DB::setDefaultConnection('tenant');
        DB::reconnect('tenant');
    });

    // 2. Tell the package HOW to run migrations on a tenant database
    Tenancy::migrateUsing(function (?Illuminate\Console\Command $command = null) {
        $command?->call('migrate', [
            '--database' => 'tenant',
            '--force'    => true,
        ]);
    });
}
```

Then register the middleware. In **Laravel 11** (`bootstrap/app.php`):

```php
use Snawbar\Tenancy\Middleware\InitializeTenancy;
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;

->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        InitializeTenancy::class,
    ]);

    $middleware->alias([
        'main-tenancy' => EnsureMainTenancy::class,
    ]);
})
```

---

## Configuration Reference

All values live in `config/snawbar-tenancy.php`:

| Key | Default | Description |
|-----|---------|-------------|
| `enabled` | `false` | Master switch — when `false` the `InitializeTenancy` middleware is skipped |
| `domain` | `localhost` | Base domain; tenants become `{name}.{domain}` |
| `main_domain` | `null` | The host where the management UI is accessible |
| `main_domain_owner` | `root` | MySQL user that is granted access to every tenant DB |
| `storage_path` | `storage/tenancy/tenants.json` | Path to the JSON tenant registry |
| `upgrade_sql_path` | `storage/tenancy/upgrade.sql` | SQL file executed by `tenancy:upgrade` |
| `database.*` | — | MySQL connection used for creating / deleting databases |
| `mysql_dump_path` | `mysqldump` | Path to `mysqldump` binary (for database cloning) |
| `mysql_path` | `mysql` | Path to `mysql` binary (for database cloning) |
| `health_sort_options` | `[]` | Keys returned by your health callback that are sortable in the UI |

---

## Feature Guide

### 1. Creating a Tenant

#### Via Artisan

```bash
php artisan tenancy:create
```

You will be prompted for a **tenant name** (lowercase, numbers, hyphens) and the **MySQL root password**. The command:

1. Creates the MySQL database (e.g. `my_company`)
2. Creates a dedicated MySQL user (e.g. `my_company_usr`) with a random 16-char password
3. Grants privileges to that user and to `main_domain_owner`
4. Runs your `migrateUsing` callback on the new database
5. Saves the tenant to `tenants.json`

#### Via Code

```php
use Snawbar\Tenancy\Facades\Tenancy;

$tenant = Tenancy::create('acme', 'mysql_root_password');
// $tenant->subdomain  →  "acme.yourdomain.com"
// $tenant->database   →  { database, username, password }

Tenancy::migrate($tenant);
```

#### Via Web UI

Navigate to `https://yourdomain.com/snawbar-tenancy/create-view`, fill in the domain name and root password, and submit.

---

### 2. Deleting a Tenant

#### Via Artisan

```bash
php artisan tenancy:delete
```

Select the tenant from a searchable list, provide the root password. The command drops the database, drops the MySQL user, and removes the tenant from the registry.

#### Via Code

```php
$tenant = Tenancy::findOrFail('acme.yourdomain.com');
Tenancy::delete($tenant, 'mysql_root_password');
```

---

### 3. Upgrading All Tenants

When you need to run a SQL change on every tenant database:

1. Create `storage/tenancy/upgrade.sql`:

```sql
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL;
CREATE INDEX idx_orders_status ON orders(status);
```

2. Run the command:

```bash
php artisan tenancy:upgrade
```

The command connects to each tenant, executes the SQL file, runs any `afterUpgradeUsing` hook, then deletes the SQL file.

---

### 4. Finding & Checking Tenants

```php
use Snawbar\Tenancy\Facades\Tenancy;

// List every tenant
$all = Tenancy::all();               // → Collection of tenant objects

// Find by full subdomain
$tenant = Tenancy::find('acme.yourdomain.com');       // → object|null
$tenant = Tenancy::findOrFail('acme.yourdomain.com'); // → object or throws TenancyNotFound

// Existence check
Tenancy::exists('acme.yourdomain.com');      // → true / false
Tenancy::doesntExist('acme.yourdomain.com'); // → true / false

// Get the tenant for the current request
$current = Tenancy::current(); // resolves from request()->getHost()
```

---

### 5. Connecting to a Tenant Database

The `InitializeTenancy` middleware handles this automatically for web requests. To switch manually:

```php
// By subdomain (looks up credentials from the registry)
Tenancy::connectWithSubdomain('acme.yourdomain.com');

// By raw credentials object
Tenancy::connectWithCredentials($tenant->database);
```

---

### 6. Database Cloning

Copy one tenant's database into another (both must already exist):

```php
Tenancy::clone(
    sourceTenant: 'source.yourdomain.com',
    targetTenant: 'target.yourdomain.com',
    rootPassword: 'mysql_root_password',
);
```

Uses `mysqldump | mysql` under the hood. Configure binary paths in `.env` if they are not on `PATH`:

```env
MYSQL_DUMP_PATH="C:\path\to\mysqldump.exe"
MYSQL_PATH="C:\path\to\mysql.exe"
```

---

### 7. Health Monitoring

Register a callback that receives a `Connection` to each tenant's database and returns an array of metrics:

```php
use Illuminate\Database\Connection;

Tenancy::healthUsing(function (Connection $db) {
    return [
        'users'         => $db->table('users')->count(),
        'orders'        => $db->table('orders')->count(),
        'last_activity' => $db->table('activity_log')->max('created_at'),
    ];
});
```

Then use it:

```php
// Single tenant
$health = Tenancy::health($tenant);
// → ['users' => 42, 'orders' => 108, 'last_activity' => '2026-02-01 09:30:00']

// All tenants with health data attached
$tenants = Tenancy::withHealth();
// Each tenant object now has a ->health property
```

To make those columns sortable in the management UI, add them to the config:

```php
// config/snawbar-tenancy.php
'health_sort_options' => [
    'users'         => 'Most Users',
    'orders'        => 'Most Orders',
    'last_activity' => 'Recent Activity',
],
```

---

### 8. Web Management Interface

Three routes are registered automatically (protected by `EnsureMainTenancy`):

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/snawbar-tenancy/list-view` | Paginated tenant list with search, sort, and health badges |
| GET | `/snawbar-tenancy/create-view` | Tenant creation form |
| POST | `/snawbar-tenancy/create` | AJAX endpoint that creates the tenant and returns JSON |

To add authentication, re-register the routes in your own `routes/web.php`:

```php
use Snawbar\Tenancy\Controllers\TenancyController;
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;

Route::middleware(['auth', EnsureMainTenancy::class])
    ->prefix('snawbar-tenancy')
    ->name('tenancy.')
    ->group(function () {
        Route::get('list-view',   [TenancyController::class, 'listView'])->name('list.view');
        Route::get('create-view', [TenancyController::class, 'createView'])->name('create.view');
        Route::post('create',     [TenancyController::class, 'create'])->name('create');
    });
```

---

### 9. Middleware

#### `InitializeTenancy`

Applied to web routes. When `TENANCY_ENABLED=true`, it reads the subdomain from the request, looks up the tenant in the registry, and calls your `connectUsing` callback. If the tenant is not found, a `TenancyNotFound` exception is thrown (renders a styled 404 page).

#### `EnsureMainTenancy`

A route guard that ensures the current request is on the **main domain**. Any request from a tenant subdomain will receive a 404. Used to protect the management UI and admin routes.

Override the validation logic:

```php
Tenancy::ensureMainTenantUsing(function ($request) {
    return $request->getHost() === 'admin.yourdomain.com';
});
```

---

### 10. Lifecycle Hooks

All hooks are registered as static closures, typically in `AppServiceProvider::boot()`.

```php
// Called after a tenant connection is established (by InitializeTenancy middleware)
Tenancy::afterConnectUsing(function (Illuminate\Http\Request $request) {
    Log::info("Tenant connected: {$request->getHost()}");
});

// Called after each tenant is upgraded via tenancy:upgrade
Tenancy::afterUpgradeUsing(function (object $tenant, Illuminate\Console\Command $command) {
    $command->info("Custom post-upgrade for {$tenant->subdomain}");
    // e.g. seed data, clear caches, etc.
});

// Called after a tenant is deleted via tenancy:delete
Tenancy::afterDeleteUsing(function (string $subdomain, Illuminate\Console\Command $command) {
    Storage::disk('s3')->deleteDirectory("tenants/{$subdomain}");
    $command->info("Cleaned up files for {$subdomain}");
});
```

---

### 11. Exception Handling

The package ships four exception classes:

| Exception | When | Behaviour |
|-----------|------|-----------|
| `TenancyNotFound` | Subdomain not in registry | Renders `404.blade.php` view (or JSON for API requests) |
| `TenancyAlreadyExists` | Creating a tenant that already exists | Standard exception |
| `TenancyDatabaseException` | DB create/delete/connect/migrate failures | Includes a `toArray()` helper |
| `DatabaseCopyFailed` | `mysqldump \| mysql` pipe fails during clone | Includes source, target, and error output |

Example — catching errors during creation:

```php
use Snawbar\Tenancy\Exceptions\TenancyAlreadyExists;
use Snawbar\Tenancy\Exceptions\TenancyDatabaseException;

try {
    $tenant = Tenancy::create('acme', $rootPassword);
    Tenancy::migrate($tenant);
} catch (TenancyAlreadyExists $e) {
    // "Tenant already exists: acme.yourdomain.com"
} catch (TenancyDatabaseException $e) {
    // "Failed to create database: ..."
}
```

---

### 12. Helper Function

A global helper is auto-loaded for formatting health values in Blade views:

```php
formatHealthValue($value): ?string
```

- **Numbers** → formatted with commas (`1000` → `1,000`)
- **Date strings** → formatted as `Y-m-d`
- **Other** → returned as-is
- **Blank** → returns `null`

---

## Tenant Object Structure

Every tenant is stored in `tenants.json` and represented as a plain PHP object:

```json
{
    "subdomain": "acme.yourdomain.com",
    "database": {
        "database": "acme",
        "username": "acme_usr",
        "password": "rAnDoM16ChArPwD!"
    }
}
```

---

## API Quick Reference

### Configuration Hooks

| Method | Description |
|--------|-------------|
| `Tenancy::connectUsing(Closure)` | **Required.** Define how to connect to a tenant DB |
| `Tenancy::migrateUsing(Closure)` | **Required.** Define how to run migrations |
| `Tenancy::healthUsing(Closure)` | Define health-check metrics |
| `Tenancy::ensureMainTenantUsing(Closure)` | Override main-domain validation |
| `Tenancy::afterConnectUsing(Closure)` | Hook after tenant connection |
| `Tenancy::afterUpgradeUsing(Closure)` | Hook after each tenant upgrade |
| `Tenancy::afterDeleteUsing(Closure)` | Hook after tenant deletion |

### Runtime Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `Tenancy::all()` | `Collection` | All tenants |
| `Tenancy::current()` | `?object` | Tenant for current request |
| `Tenancy::find($subdomain)` | `?object` | Find by subdomain |
| `Tenancy::findOrFail($subdomain)` | `object` | Find or throw `TenancyNotFound` |
| `Tenancy::exists($subdomain)` | `bool` | Check existence |
| `Tenancy::doesntExist($subdomain)` | `bool` | Inverse of `exists` |
| `Tenancy::health($tenant)` | `array` | Health metrics for one tenant |
| `Tenancy::withHealth()` | `Collection` | All tenants with `->health` attached |
| `Tenancy::connectWithSubdomain($sub)` | `void` | Switch DB by subdomain |
| `Tenancy::connectWithCredentials($creds)` | `void` | Switch DB by credentials object |
| `Tenancy::migrate($tenant, $cmd?)` | `void` | Run migrations on tenant DB |
| `Tenancy::create($name, $rootPw?)` | `object` | Create tenant + database |
| `Tenancy::clone($src, $tgt, $rootPw?)` | `void` | Copy database between tenants |
| `Tenancy::delete($tenant, $rootPw?)` | `void` | Drop database + remove from registry |

---

## Contributing

1. Fork the repo
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes
4. Push and open a Pull Request

---

## License

MIT — see [LICENSE](LICENSE.md).

---

**Author:** [Snawbar](https://github.com/mikailfaruqali) — alanfaruq85@gmail.com
