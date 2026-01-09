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

    'enabled' => env('TENANCY_ENABLED', false),

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
    | Tenant Storage Path
    |--------------------------------------------------------------------------
    |
    | The full path where the tenants.json file will be stored.
    | This file contains all tenant configurations including database credentials.
    | Make sure this path is writable and backed up regularly.
    |
    */

    'storage_path' => storage_path('tenancy/tenants.json'),

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
];