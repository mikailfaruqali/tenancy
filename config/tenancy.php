<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the multi-tenancy system. When disabled, the application
    | will run in single-tenant mode and tenant middleware will be skipped.
    |
    */

    'enabled' => env('TENANCY_ENABLED', FALSE),

    /*
    |--------------------------------------------------------------------------
    | Tenant Domain
    |--------------------------------------------------------------------------
    |
    | The main domain that will be used for tenant subdomains.
    | Example: If set to 'snawbar.com', tenants will be accessed via:
    | tenant1.snawbar.com, tenant2.snawbar.com, etc.
    |
    */

    'domain' => env('TENANCY_DOMAIN', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Main Tenant Domain
    |--------------------------------------------------------------------------
    |
    | The main domain where the tenant management interface is accessible.
    | This is where you can create/manage tenants. Usually the same as
    | 'domain' above, but can be different if you want a separate admin domain.
    |
    */

    'main_domain' => env('TENANCY_MAIN_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Main Tenant Domain Owner
    |--------------------------------------------------------------------------
    |
    | The MySQL user that has privileges to access all tenant databases.
    | This is used to grant cross-tenant access when needed (like for reports
    | or backups). Usually 'root' or a dedicated admin user.
    |
    */

    'main_domain_owner' => env('TENANCY_MAIN_DOMAIN_OWNER', 'root'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk name used for tenant file operations such as
    | uploads and deletions. This disk must be defined in filesystems.php
    | with both 'root' and 'url' keys. The disk root and url will be
    | scoped to the current tenant automatically during each request.
    |
    */

    'storage_disk' => NULL,

    /*
    |--------------------------------------------------------------------------
    | Symlink
    |--------------------------------------------------------------------------
    |
    | The public directory name that symlinks to the tenant files storage.
    | This is used to generate URLs for tenant files. Must match the
    | symlink path defined in filesystems.links configuration.
    |
    | Example: 'files' generates URLs like /files/{tenant}/uploads/logo.png
    |
    */

    'symlink' => NULL,

    /*
    |--------------------------------------------------------------------------
    | Tenants Path
    |--------------------------------------------------------------------------
    |
    | The full path to the tenants.json file that contains all tenant
    | configurations including database credentials. Make sure this
    | path is writable and backed up regularly.
    |
    */

    'tenants_path' => storage_path('tenancy/tenants.json'),

    /*
    |--------------------------------------------------------------------------
    | Upgrade SQL Path
    |--------------------------------------------------------------------------
    |
    | Path to the SQL file that will be executed when upgrading tenants.
    | This file is run against each tenant database during tenancy:upgrade
    | and is deleted after all tenants are upgraded.
    |
    */

    'upgrade_sql_path' => storage_path('tenancy/upgrade.sql'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for creating new tenant databases. This connection is used
    | when creating, deleting, or managing tenant databases. The user specified
    | here needs CREATE DATABASE, CREATE USER, and GRANT privileges.
    |
    */

    'database' => [
        'driver' => 'mysql',
        'host' => env('TENANCY_DB_HOST', '127.0.0.1'),
        'port' => env('TENANCY_DB_PORT', '3306'),
        'username' => env('TENANCY_DB_USERNAME', 'root'),
        'password' => env('TENANCY_DB_PASSWORD', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | MySQL Binary Paths
    |--------------------------------------------------------------------------
    |
    | Paths to MySQL binaries used for database cloning operations.
    | On Linux servers, defaults usually work. On Windows (Laragon, XAMPP, Herd),
    | you may need to specify full paths.
    |
    | Example for Herd on Windows:
    |   MYSQL_DUMP_PATH="C:\Users\user\.config\herd\bin\services\mysql\8.4.2\bin\mysqldump.exe"
    |   MYSQL_PATH="C:\Users\user\.config\herd\bin\services\mysql\8.4.2\bin\mysql.exe"
    |
    */

    'mysql_dump_path' => env('MYSQL_DUMP_PATH', 'mysqldump'),
    'mysql_path' => env('MYSQL_PATH', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Health Check Sort Options
    |--------------------------------------------------------------------------
    |
    | Define which columns are available for sorting tenants in the UI.
    | Keys are used as option values, labels are displayed in the select.
    |
    | Built-in options:
    |   'name' => sorts by tenant subdomain
    |
    | Health metrics must match keys returned by your Tenancy::healthUsing() callback.
    |
    | Example: If your health callback returns:
    |   ['journals' => 1000, 'invoices' => 500, 'last_activity' => '2024-06-15']
    |
    | You could define:
    |   'name' => 'Name',
    |   'journals' => 'Most Journals',
    |   'invoices' => 'Most Invoices',
    |   'last_activity' => 'Recent Activity',
    |
    */

    'health_sort_options' => [

    ],
];
