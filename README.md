# 🏢 Snawbar Tenancy - Laravel Multi-Tenancy Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)
[![Total Downloads](https://img.shields.io/packagist/dt/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)
[![License](https://img.shields.io/packagist/l/mikailfaruqali/tenancy.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/tenancy)

A powerful yet simple Laravel package for **database-per-tenant** multi-tenancy with subdomain-based tenant identification. Fast file-based tenant registry, automatic database isolation, and dynamic connection switching.

## ✨ Features

- 🗄️ **Database-Per-Tenant** - Complete database isolation for each tenant
- 🌐 **Subdomain-Based** - Automatic tenant identification via subdomains
- ⚡ **Fast File Storage** - JSON-based tenant registry for quick access
- 🔐 **Secure Isolation** - Separate MySQL users and databases per tenant
- 🎨 **Management Interface** - Web UI for tenant management with health monitoring
- 🔍 **Health Monitoring** - Built-in tenant health checks with customization
- 🛠️ **Artisan Commands** - CLI tools for creating, deleting, and upgrading tenants
- 🎯 **Middleware Support** - Automatic tenant detection and connection switching
- 🔧 **Highly Customizable** - Hooks for custom connection, migration, and health check logic

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- MySQL database
- MySQL user with `CREATE DATABASE`, `CREATE USER`, and `GRANT` privileges

## 📦 Installation

### 1. Install via Composer

```bash
composer require mikailfaruqali/tenancy
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=snawbar-tenancy-config
```

This will create `config/snawbar-tenancy.php`.

### 3. Publish Views (Optional)

```bash
php artisan vendor:publish --tag=snawbar-tenancy-views
```

This will publish views to `resources/views/vendor/snawbar-tenancy/`.

### 4. Configure Environment

Add to your `.env` file:

```env
# Enable/disable multi-tenancy
TENANCY_ENABLED=true

# Main domain for tenant subdomains
TENANCY_DOMAIN=yourdomain.com

# Main domain (where admin panel is accessible)
TENANCY_MAIN_DOMAIN=yourdomain.com

# MySQL user that can access all tenant databases (for admin operations)
TENANCY_MAIN_DOMAIN_OWNER=root

# MySQL root credentials (for creating/deleting databases)
TENANCY_DB_HOST=127.0.0.1
TENANCY_DB_PORT=3306
TENANCY_DB_USERNAME=root
TENANCY_DB_PASSWORD=your_root_password
```

### 5. Create Storage Directory

```bash
mkdir -p storage/tenancy
```

The package stores tenant information in `storage/tenancy/tenants.json`.

## ⚙️ Configuration

### Main Configuration (`config/snawbar-tenancy.php`)

```php
<?php

return [
    // Enable or disable multi-tenancy
    'enabled' => env('TENANCY_ENABLED', false),

    // Domain for tenant subdomains (tenant1.yourdomain.com)
    'domain' => env('TENANCY_DOMAIN', 'localhost'),

    // Main domain where admin panel is accessible
    'main_domain' => env('TENANCY_MAIN_DOMAIN'),

    // MySQL user with access to all tenant databases
    'main_domain_owner' => env('TENANCY_MAIN_DOMAIN_OWNER', 'root'),

    // Path to tenants.json file
    'storage_path' => storage_path('tenancy/tenants.json'),

    // Path to upgrade SQL file
    'upgrade_sql_path' => storage_path('tenancy/upgrade.sql'),

    // Database configuration for tenant management
    'database' => [
        'driver' => 'mysql',
        'host' => env('TENANCY_DB_HOST', '127.0.0.1'),
        'port' => env('TENANCY_DB_PORT', '3306'),
        'username' => env('TENANCY_DB_USERNAME', 'root'),
        'password' => env('TENANCY_DB_PASSWORD', ''),
    ],
];
```

## 🚀 Basic Setup

### 1. Configure Connection Handler

In your `AppServiceProvider` or a dedicated service provider:

```php
use Illuminate\Support\Facades\DB;
use Snawbar\Tenancy\Facades\Tenancy;

public function boot(): void
{
    // Define how to connect to a tenant database
    Tenancy::connectUsing(function ($credentials) {
        config([
            'database.connections.tenant' => [
                'driver' => 'mysql',
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => $credentials->database,
                'username' => $credentials->username,
                'password' => $credentials->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        ]);

        DB::setDefaultConnection('tenant');
        DB::reconnect('tenant');
    });
}
```

### 2. Configure Migration Handler

```php
Tenancy::migrateUsing(function ($command = null) {
    $command?->call('migrate', [
        '--database' => 'tenant',
        '--force' => true,
    ]);
});
```

### 3. Register Middleware

In `bootstrap/app.php` (Laravel 11):

```php
use Snawbar\Tenancy\Middleware\InitializeTenancy;
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;

->withMiddleware(function (Middleware $middleware) {
    // Apply to web routes for automatic tenant detection
    $middleware->web(append: [
        InitializeTenancy::class,
    ]);
    
    // Apply to admin routes to ensure they only work on main domain
    $middleware->alias([
        'main-tenancy' => EnsureMainTenancy::class,
    ]);
})
```

Or in `app/Http/Kernel.php` (Laravel 10):

```php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \Snawbar\Tenancy\Middleware\InitializeTenancy::class,
    ],
];

protected $middlewareAliases = [
    // ... other aliases
    'main-tenancy' => \Snawbar\Tenancy\Middleware\EnsureMainTenancy::class,
];
```

## 🎯 Usage

### Creating Tenants

#### Via Artisan Command

```bash
php artisan tenancy:create
```

You'll be prompted for:
- Tenant name (alphanumeric and hyphens only)
- MySQL root password

The command will:
1. Create a new MySQL database
2. Create a dedicated MySQL user for the tenant
3. Run migrations on the tenant database
4. Store tenant information in `tenants.json`

#### Via Code

```php
use Snawbar\Tenancy\Facades\Tenancy;

// Create tenant
$tenant = Tenancy::create('company-name', 'mysql_root_password');

// The tenant object contains:
// - subdomain: "company-name.yourdomain.com"
// - database: { database, username, password }

// Run migrations
Tenancy::migrate($tenant);
```

#### Via Management Interface

Access the web interface at your main domain:
- `https://yourdomain.com/tenancy` - List all tenants
- `https://yourdomain.com/tenancy/create` - Create new tenant

### Deleting Tenants

#### Via Artisan Command

```bash
php artisan tenancy:delete
```

Select a tenant from the list and provide the MySQL root password.

#### Via Code

```php
Tenancy::delete('company-name.yourdomain.com', 'mysql_root_password');
```

### Upgrading Tenants

When you need to run SQL updates across all tenants:

1. Create `storage/tenancy/upgrade.sql` with your SQL:
```sql
ALTER TABLE users ADD COLUMN phone VARCHAR(20);
```

2. Run the upgrade command:
```bash
php artisan tenancy:upgrade
```

This will:
- Connect to each tenant database
- Execute the SQL file
- Run any custom upgrade logic
- Delete the SQL file after completion

### Finding Tenants

```php
// Get all tenants
$tenants = Tenancy::all();

// Find specific tenant
$tenant = Tenancy::find('company.yourdomain.com');

// Find or throw exception
$tenant = Tenancy::findOrFail('company.yourdomain.com');

// Check if tenant exists
if (Tenancy::exists('company.yourdomain.com')) {
    // ...
}
```

### Manual Connection Switching

```php
// Connect by subdomain
Tenancy::connectWithSubdomain('company.yourdomain.com');

// Connect with credentials object
Tenancy::connectWithCredentials($tenant->database);
```

### Health Monitoring

```php
// Check single tenant health
$health = Tenancy::health($tenant);
// Returns: ['status' => 'active', 'record_count' => 1250, 'table_count' => 15]

// Get all tenants with health data
$tenants = Tenancy::withHealth();
```

## 🔧 Advanced Configuration

### Custom Connection Logic

```php
Tenancy::connectUsing(function ($credentials) {
    // Your custom connection logic
    // e.g., connect to different database servers based on tenant
});
```

### After Connection Hook

```php
Tenancy::afterConnectUsing(function ($request) {
    // Run after tenant connection is established
    // e.g., set up tenant-specific configuration
    Log::info('Connected to tenant: ' . $request->getHost());
});
```

### Custom Health Checks

```php
use Illuminate\Database\Connection;

Tenancy::healthUsing(function (Connection $connection, object $tenant) {
    // Return custom health metrics
    return [
        'users_count' => $connection->table('users')->count(),
        'posts_count' => $connection->table('posts')->count(),
        'last_activity' => $connection->table('activity_logs')->max('created_at'),
    ];
});
```

### After Upgrade Hook

```php
Tenancy::afterUpgradeUsing(function ($tenant, $command) {
    $command->info("Running custom upgrade for {$tenant->subdomain}");
    // Custom upgrade logic per tenant
});
```

### After Delete Hook

```php
Tenancy::afterDeleteUsing(function ($subdomain, $command) {
    // Cleanup logic after tenant deletion
    Storage::disk('s3')->deleteDirectory("tenants/{$subdomain}");
});
```

### Custom Main Domain Validation

```php
Tenancy::ensureMainTenantUsing(function ($request) {
    // Custom logic to determine if request is on main domain
    return $request->getHost() === 'admin.yourdomain.com';
});
```

## 🎨 Management Interface

The package includes a beautiful web interface for managing tenants.

### Routing Setup

The routes are automatically registered. Protect them with your auth middleware:

```php
// In routes/web.php or RouteServiceProvider
Route::middleware(['auth', 'main-tenancy'])->group(function () {
    // Tenancy routes are auto-registered at /tenancy/*
});
```

### Available Routes

- `GET /tenancy` - List all tenants with health status, search, and sorting
- `GET /tenancy/create` - Create new tenant form
- `POST /tenancy/create` - Handle tenant creation

### Features

- 📊 Real-time health monitoring
- 🔍 Search tenants by subdomain
- 📈 Sort by database usage
- 📄 Pagination support
- ⚡ Ajax-based tenant creation
- 🎨 Modern, responsive UI

## �️ Middleware

### InitializeTenancy

Automatically detects tenant from subdomain and switches database connection.

```php
// Applied to web middleware group
public function handle(Request $request, Closure $next)
{
    if (config('snawbar-tenancy.enabled')) {
        Tenancy::connectWithSubdomain($request->getHost());
    }
    
    return $next($request);
}
```

### EnsureMainTenancy

Ensures routes are only accessible on the main domain (admin panel).

```php
Route::middleware('main-tenancy')->group(function () {
    // Only accessible on main domain
    Route::get('/admin', ...);
});
```

## 🎭 Exception Handling

### TenancyNotFound

Thrown when a tenant subdomain doesn't exist. Automatically renders a beautiful 404 page.

```php
try {
    $tenant = Tenancy::findOrFail('nonexistent.yourdomain.com');
} catch (TenancyNotFound $e) {
    // Handled automatically with custom 404 view
}
```

### TenancyAlreadyExists

Thrown when trying to create a tenant that already exists.

```php
try {
    Tenancy::create('existing-tenant');
} catch (TenancyAlreadyExists $e) {
    // Handle duplicate tenant
}
```

### TenancyDatabaseException

Thrown when database operations fail.

```php
try {
    Tenancy::create('new-tenant', 'wrong_password');
} catch (TenancyDatabaseException $e) {
    Log::error($e->getMessage());
}
```

## 📚 API Reference

### Facade Methods

#### Configuration Hooks
```php
Tenancy::connectUsing(Closure $callback): void
Tenancy::migrateUsing(Closure $callback): void
Tenancy::healthUsing(Closure $callback): void
Tenancy::ensureMainTenantUsing(Closure $callback): void
Tenancy::afterConnectUsing(Closure $callback): void
Tenancy::afterUpgradeUsing(Closure $callback): void
Tenancy::afterDeleteUsing(Closure $callback): void
```

#### Runtime API
```php
Tenancy::all(): Collection
Tenancy::find(string $subdomain): ?object
Tenancy::findOrFail(string $subdomain): object
Tenancy::exists(string $subdomain): bool
Tenancy::health(object $tenant): array
Tenancy::withHealth(): Collection
```

#### Connection & Migration
```php
Tenancy::connectWithSubdomain(string $subdomain): void
Tenancy::connectWithCredentials(object $credentials): void
Tenancy::migrate(object $tenant, ?Command $command = null): void
```

#### Tenant Lifecycle
```php
Tenancy::create(string $name, ?string $rootPassword = null): object
Tenancy::delete(string $subdomain, ?string $rootPassword = null): void
```

### Tenant Object Structure

```php
{
    "subdomain": "company.yourdomain.com",
    "database": {
        "database": "company_db",
        "username": "company_usr",
        "password": "random_16_char_password"
    }
}
```

## 🎯 Common Patterns

### Multi-Database Queries

```php
// Get data from all tenants
$allData = Tenancy::all()->map(function ($tenant) {
    Tenancy::connectWithCredentials($tenant->database);
    return DB::table('users')->count();
});
```

### Tenant-Specific Configuration

```php
Tenancy::afterConnectUsing(function ($request) {
    $tenant = Tenancy::find($request->getHost());
    
    // Set tenant-specific config
    config([
        'app.name' => $tenant->name ?? config('app.name'),
        'mail.from.name' => $tenant->email ?? config('mail.from.name'),
    ]);
});
```

## 🔒 Security Considerations

1. **Tenant Isolation**: Each tenant has a dedicated database and MySQL user with access only to their database
2. **Credential Storage**: Tenant credentials are stored in `storage/tenancy/tenants.json` - ensure proper file permissions
3. **Root Password**: MySQL root password is only used during tenant creation/deletion, never stored
4. **Subdomain Validation**: Tenant names are sanitized to alphanumeric and hyphens only
5. **Main Domain Protection**: Use `EnsureMainTenancy` middleware to protect admin routes

## 🐛 Troubleshooting

### Tenant Not Found

**Issue**: Getting 404 when accessing tenant subdomain

**Solutions**:
- Ensure DNS wildcard record `*.yourdomain.com` points to your server
- Check `TENANCY_ENABLED=true` in `.env`
- Verify tenant exists: `php artisan tinker` → `Tenancy::all()`

### Connection Not Switching

**Issue**: Still connected to main database when accessing tenant

**Solutions**:
- Verify `connectUsing()` callback is registered in `AppServiceProvider`
- Ensure `InitializeTenancy` middleware is applied to web routes
- Check connection is being set: `DB::getDefaultConnection()`

### Migration Fails

**Issue**: Migrations don't run on tenant database

**Solutions**:
- Verify `migrateUsing()` callback is configured
- Ensure migrations exist in `database/migrations/`
- Check MySQL user has proper permissions
- Run manually: `Tenancy::migrate($tenant)`

### Permission Denied

**Issue**: Cannot create database or user

**Solutions**:
- Verify MySQL root credentials in `.env`
- Ensure MySQL user has `CREATE DATABASE`, `CREATE USER`, `GRANT` privileges
- Test connection: `mysql -u root -p`

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👨‍💻 Author

**Snawbar**
- Email: alanfaruq85@gmail.com
- GitHub: [@mikailfaruqali](https://github.com/mikailfaruqali)

## 🙏 Acknowledgments

Built with ❤️ for the Laravel community.

---

**Need Help?** Open an issue on [GitHub](https://github.com/mikailfaruqali/tenancy/issues)