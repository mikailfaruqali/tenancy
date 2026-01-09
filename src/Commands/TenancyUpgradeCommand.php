<?php

namespace Snawbar\Tenancy\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Fluent;
use Snawbar\Tenancy\Facades\Tenancy;

class TenancyUpgradeCommand extends Command
{
    protected $signature = 'tenancy:upgrade';

    private static ?Closure $afterUpgradeUsing = NULL;

    public static function afterUpgradeUsing(Closure $callback): void
    {
        self::$afterUpgradeUsing = $callback;
    }

    public function handle(): void
    {
        foreach (Tenancy::all() as $tenant) {
            $this->upgradeTenant($tenant);
        }

        if (File::exists($this->upgradeSqlPath())) {
            File::delete($this->upgradeSqlPath());
        }

        $this->components->info('All tenants upgraded');
    }

    private function upgradeTenant(Fluent $fluent): void
    {
        Tenancy::connect($fluent->subdomain);

        $this->components->info(sprintf('Upgrading tenant: %s', $fluent->subdomain));

        if (File::exists($this->upgradeSqlPath())) {
            DB::unprepared(File::get($this->upgradeSqlPath()));
        }

        if (self::$afterUpgradeUsing instanceof Closure) {
            (self::$afterUpgradeUsing)($fluent, $this);
        }

        $this->components->info(sprintf('Tenant upgraded: %s', $fluent->subdomain));
    }

    private function upgradeSqlPath(): string
    {
        return config()->string('snawbar-tenancy.upgrade_sql_path');
    }
}
