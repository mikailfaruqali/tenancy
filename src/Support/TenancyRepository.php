<?php

namespace Snawbar\Tenancy\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Fluent;
use Snawbar\Tenancy\Exceptions\TenancyNotFound;

class TenancyRepository
{
    private Collection $tenants;

    private readonly string $path;

    public function __construct()
    {
        $this->path = $this->storagePath();
        $this->tenants = $this->load();
    }

    public function all(): Collection
    {
        return $this->tenants;
    }

    public function find(string $subdomain): ?Fluent
    {
        return $this->tenants->firstWhere('subdomain', $subdomain);
    }

    public function findOrFail(string $subdomain): Fluent
    {
        return $this->find($subdomain) ?? throw new TenancyNotFound($subdomain);
    }

    public function exists(string $subdomain): bool
    {
        return (bool) $this->find($subdomain);
    }

    public function add(array $config): Fluent
    {
        $fluent = fluent($config);

        $this->tenants->push($fluent);
        $this->save();

        return $fluent;
    }

    public function remove(string $subdomain): void
    {
        $this->tenants = $this->tenants->reject(fn ($tenant) => $tenant->subdomain === $subdomain);
        $this->save();
    }

    private function storagePath(): string
    {
        return config()->string('snawbar-tenancy.storage_path');
    }

    private function load(): Collection
    {
        return collect(File::json($this->path, []))->map(fn ($tenant) => fluent($tenant));
    }

    private function save(): void
    {
        File::put(
            path: $this->path,
            contents: $this->tenants->toJson(JSON_PRETTY_PRINT),
            lock: TRUE
        );
    }
}
