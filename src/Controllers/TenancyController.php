<?php

namespace Snawbar\Tenancy\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Snawbar\Tenancy\Facades\Tenancy;
use Snawbar\Tenancy\Requests\CreateTenantRequest;
use Throwable;

class TenancyController extends Controller
{
    public function listView(Request $request)
    {
        $tenants = Tenancy::withHealth();

        $tenants = $this->filterBySearch($tenants, $request->input('search'));
        $tenants = $this->sortByUsage($tenants, $request->input('sort'));

        return view('snawbar-tenancy::index', [
            'tenants' => $this->paginate($tenants, $request),
        ]);
    }

    public function createView()
    {
        return view('snawbar-tenancy::create');
    }

    public function create(CreateTenantRequest $createTenantRequest)
    {
        try {
            $tenant = Tenancy::create($createTenantRequest->input('domain'), $createTenantRequest->input('password'));

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

    private function filterBySearch(Collection $tenants, ?string $search): Collection
    {
        if (blank($search)) {
            return $tenants;
        }

        return $tenants->filter(fn ($tenant) => str_contains(
            strtolower((string) $tenant->subdomain),
            strtolower($search)
        ));
    }

    private function sortByUsage(Collection $tenants, ?string $sort): Collection
    {
        return match ($sort) {
            'usage' => $tenants->sortByDesc(fn ($tenant) => collect($tenant->health)
                ->filter(fn ($value) => is_numeric($value))
                ->sum()
            ),
            default => $tenants,
        };
    }

    private function paginate(Collection $collection, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $page = $request->input('page', 1);

        return new LengthAwarePaginator(
            items: $collection->forPage($page, $perPage)->values(),
            total: $collection->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => $request->url(),
            ],
        );
    }
}
