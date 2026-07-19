<?php

namespace App\Repositories\Eloquent;

use App\Models\ZabbixConnection;
use App\Repositories\Contracts\ZabbixConnectionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ZabbixConnectionRepository extends EloquentRepository implements ZabbixConnectionRepositoryInterface
{
    protected function model(): string
    {
        return ZabbixConnection::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return parent::all()->sortBy('name')->values();
    }

    public function find(int $id): ?ZabbixConnection
    {
        return parent::find($id);
    }

    public function active(): Collection
    {
        return $this->query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function activeForCompany(int $companyId): Collection
    {
        return $this->query()
            ->where('status', 'active')
            ->where('company_id', $companyId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function activeDefault(): ?ZabbixConnection
    {
        return $this->query()
            ->where('status', 'active')
            ->whereNull('company_id')
            ->where('is_default', true)
            ->orderBy('name')
            ->first();
    }

    public function defaultForCompany(int $companyId): ?ZabbixConnection
    {
        return $this->query()
            ->where('status', 'active')
            ->where('company_id', $companyId)
            ->where('is_default', true)
            ->orderBy('name')
            ->first();
    }
}
