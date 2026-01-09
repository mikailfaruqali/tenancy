<?php

namespace Snawbar\Tenancy\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
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

    public function find(string $subdomain): ?object
    {
        return $this->tenants->firstWhere('subdomain', $subdomain);
    }

    public function findOrFail(string $subdomain): object
    {
        return $this->find($subdomain) ?? throw new TenancyNotFound($subdomain);
    }

    public function exists(string $subdomain): bool
    {
        return (bool) $this->find($subdomain);
    }

    public function add(array $config): object
    {
        $config = (object) $config;

        $this->tenants->push($config);
        $this->save();

        return $config;
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
        return collect(json_decode(
            json: File::get($this->path),
            associative: FALSE,
        ));
    }

    private function save(): void
    {
        File::put(
            path: $this->path,
            contents: $this->tenants->values()->toJson(JSON_PRETTY_PRINT),
            lock: TRUE
        );
    }
}
