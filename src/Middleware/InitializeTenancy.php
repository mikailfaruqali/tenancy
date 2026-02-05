<?php

namespace Snawbar\Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Snawbar\Tenancy\Facades\Tenancy;

class InitializeTenancy
{
    private static ?Closure $afterConnectUsing = NULL;

    public static function afterConnectUsing(Closure $callback): void
    {
        self::$afterConnectUsing = $callback;
    }

    public function handle(Request $request, Closure $next)
    {
        if (config()->boolean('snawbar-tenancy.enabled')) {
            Tenancy::connectWithSubdomain($request->getHost());

            $this->setStorageDisk($request);
        }

        if (self::$afterConnectUsing instanceof Closure) {
            (self::$afterConnectUsing)($request);
        }

        return $next($request);
    }

    private function setStorageDisk(Request $request): void
    {
        $disk = config()->string('snawbar-tenancy.storage_disk');

        $root = sprintf('filesystems.disks.%s.root', $disk);

        $path = sprintf('%s/%s', config()->string($root), $request->getHost());

        Config::set($root, $path);
    }
}
