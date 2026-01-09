<?php

namespace Snawbar\Tenancy\Exceptions;

use Exception;
use Illuminate\Http\Request;

class TenancyNotFound extends Exception
{
    public function __construct(
        public readonly string $subdomain
    ) {}

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => sprintf('Tenant %s not found', $this->subdomain),
            ], 404);
        }

        return response()->view('snawbar-tenancy::404', [
            'subdomain' => $this->subdomain,
        ], 404);
    }
}
