<?php

namespace Snawbar\Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMainTenancy
{
    private static ?Closure $validateUsing = NULL;

    public static function validateUsing(Closure $callback): void
    {
        self::$validateUsing = $callback;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->isValid($request)) {
            return $next($request);
        }

        abort(404);
    }

    private function isValid(Request $request): bool
    {
        if (self::$validateUsing instanceof Closure) {
            return (self::$validateUsing)($request);
        }

        return $this->defaultValidation($request);
    }

    private function defaultValidation(Request $request): bool
    {
        return $request->getHost() === config()->string('snawbar-tenancy.main_domain');
    }
}
