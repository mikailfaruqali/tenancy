<?php

namespace Snawbar\Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            Tenancy::connect($request->getHost());
        }

        if (self::$afterConnectUsing instanceof Closure) {
            (self::$afterConnectUsing)($request);
        }

        return $next($request);
    }
}
