<?php

namespace Snawbar\Tenancy\Controllers;

use Illuminate\Routing\Controller;
use Snawbar\Tenancy\Facades\Tenancy;
use Snawbar\Tenancy\Requests\CreateTenantRequest;
use Throwable;

class TenancyController extends Controller
{
    public function createView()
    {
        return view('snawbar-tenancy::create');
    }

    public function create(CreateTenantRequest $createTenantRequest)
    {
        try {
            $tenant = Tenancy::create(
                $createTenantRequest->input('domain'),
                $createTenantRequest->input('password')
            );

            Tenancy::migrate($tenant);

            return response()->json([
                'redirect' => $tenant->subdomain,
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => $throwable->getMessage(),
            ], 422);
        }
    }
}
