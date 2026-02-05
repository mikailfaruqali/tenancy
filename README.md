# Tenancy for Laravel

Fast, file-based multi-tenancy for Laravel. Each tenant gets its own MySQL database, identified by subdomain — no extra tables, no Eloquent models, just a single JSON file.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)
[![Total Downloads](https://img.shields.io/packagist/dt/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)
[![License](https://img.shields.io/packagist/l/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)

---

## Features

- **Database-per-tenant** — fully isolated MySQL database and user for every tenant
- **Subdomain routing** — tenants resolved automatically via `acme.yourapp.com`
- **File-based registry** — all tenants stored in a single `tenants.json`; zero migrations
- **Dynamic connection switching** — swap the active DB connection at runtime
- **Auto migrations** — run Laravel migrations on each tenant DB at creation or upgrade
- **Database cloning** — pipe `mysqldump` from one tenant to another in one call
- **Health checks** — attach custom metrics (row counts, last activity, etc.) per tenant
- **Management UI** — built-in Blade views to list, search, sort, and create tenants
- **Artisan commands** — `tenancy:create`, `tenancy:delete`, `tenancy:upgrade`
- **SQL upgrade scripts** — drop an `upgrade.sql` and run it across every tenant
- **Scoped file storage** — auto-scope a filesystem disk to the current tenant
- **Lifecycle hooks** — tap into connect, migrate, upgrade, and delete events
- **Tenant asset helper** — `tenancy_asset()` for per-tenant public file URLs
- **Publishable config & views** — customize everything

---

## Requirements

- PHP 8.2+
- Laravel 11+
- MySQL with `CREATE DATABASE` / `CREATE USER` / `GRANT` privileges

---

## Installation

```bash
composer require mikailfaruqali/tenancy
```

Publish the config:

```bash
php artisan vendor:publish --tag=snawbar-tenancy-config
```

Optionally publish the views:

```bash
php artisan vendor:publish --tag=snawbar-tenancy-views
```

---

## Configuration

Add to your `.env`:

```dotenv
TENANCY_ENABLED=true
TENANCY_DOMAIN=yourapp.com
TENANCY_MAIN_DOMAIN=yourapp.com
TENANCY_DB_HOST=127.0.0.1
TENANCY_DB_PORT=3306
TENANCY_DB_USERNAME=root
TENANCY_DB_PASSWORD=secret
```

See the full config in `config/snawbar-tenancy.php` after publishing.

---

## Setup

Register connection and migration logic in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Snawbar\Tenancy\Facades\Tenancy;

public function boot(): void
{
    // How to switch to a tenant database
    Tenancy::connectUsing(function (object $credentials) {
        config([
            'database.connections.mysql.database' => $credentials->database,
            'database.connections.mysql.username' => $credentials->username,
            'database.connections.mysql.password' => $credentials->password,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    });

    // How to run migrations on a tenant database
    Tenancy::migrateUsing(function ($command = null) {
        Artisan::call('migrate', ['--force' => true], $command?->getOutput());
    });
}
```

### Middleware

Apply `InitializeTenancy` to tenant-facing routes — it detects the subdomain and switches the connection automatically:

```php
use Snawbar\Tenancy\Middleware\InitializeTenancy;

Route::middleware([InitializeTenancy::class])->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

`EnsureMainTenancy` protects admin-only routes (the built-in UI uses it):

```php
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;

Route::middleware([EnsureMainTenancy::class])->group(function () {
    Route::get('/admin', AdminController::class);
});
```

---

## Artisan Commands

| Command | Description |
|---|---|
| `php artisan tenancy:create` | Create a tenant — prompts for name & root password, creates DB + user, runs migrations |
| `php artisan tenancy:delete` | Search & select a tenant, drop its DB and user |
| `php artisan tenancy:upgrade` | Execute `storage/tenancy/upgrade.sql` on every tenant, then delete the file |

---

## Programmatic API

```php
use Snawbar\Tenancy\Facades\Tenancy;

// All tenants
$tenants = Tenancy::all();

// Find
$tenant = Tenancy::find('acme.yourapp.com');
$tenant = Tenancy::findOrFail('acme.yourapp.com');

// Current tenant (from request host)
$tenant = Tenancy::current();

// Existence
Tenancy::exists('acme.yourapp.com');   // bool
Tenancy::doesntExist('acme.yourapp.com');

// Create & migrate
$tenant = Tenancy::create('acme', $rootPassword);
Tenancy::migrate($tenant);

// Clone one tenant's data into another
Tenancy::clone('source.yourapp.com', 'target.yourapp.com', $rootPassword);

// Delete tenant + database
Tenancy::delete($tenant, $rootPassword);

// Manual connection switching
Tenancy::connectWithSubdomain('acme.yourapp.com');
Tenancy::connectWithCredentials($credentials);
```

---

## Health Checks

Define custom metrics — they appear as badges in the management UI and can be used for sorting:

```php
use Illuminate\Database\Connection;

Tenancy::healthUsing(function (Connection $connection) {
    return [
        'users'         => $connection->table('users')->count(),
        'invoices'      => $connection->table('invoices')->count(),
        'last_activity' => $connection->table('activity_log')->max('created_at'),
    ];
});
```

Make them sortable via config:

```php
// config/snawbar-tenancy.php
'health_sort_options' => [
    'users'         => 'Most Users',
    'invoices'      => 'Most Invoices',
    'last_activity' => 'Recent Activity',
],
```

---

## Lifecycle Hooks

```php
// After a tenant connection is established (runs inside middleware)
Tenancy::afterConnectUsing(function (Request $request) {
    // e.g. load tenant settings, set locale
});

// After each tenant is upgraded via tenancy:upgrade
Tenancy::afterUpgradeUsing(function (object $tenant, Command $command) {
    // e.g. seed data, clear cache
});

// After a tenant is deleted via tenancy:delete
Tenancy::afterDeleteUsing(function (string $subdomain, Command $command) {
    // e.g. remove files, send notification
});

// Customize main-domain validation
Tenancy::ensureMainTenantUsing(function (Request $request) {
    return $request->getHost() === 'admin.yourapp.com';
});
```

---

## Management UI

The package ships with a clean, responsive panel accessible only from the main domain:

| Route | View |
|---|---|
| `GET /snawbar-tenancy/list-view` | List tenants — search, sort, health badges, pagination |
| `GET /snawbar-tenancy/create-view` | Create a tenant — AJAX form with validation |

A custom 404 page is rendered automatically when a tenant subdomain is not found.

---

## Scoped File Storage

Automatically scope a filesystem disk to the current tenant:

```dotenv
TENANCY_STORAGE_DISK=tenant_files
TENANCY_SYMLINK=files
```

```php
<img src="{{ tenancy_asset('uploads/logo.png') }}">
{{-- /files/acme.yourapp.com/uploads/logo.png --}}
```

---

## Tenant Data Structure

Tenants are persisted in `storage/tenancy/tenants.json`:

```json
[
  {
    "subdomain": "acme.yourapp.com",
    "database": {
      "database": "acme",
      "username": "acme_usr",
      "password": "auto-generated-16-chars"
    }
  }
]
```

---

## License

MIT — see [LICENSE](LICENSE) for details.
