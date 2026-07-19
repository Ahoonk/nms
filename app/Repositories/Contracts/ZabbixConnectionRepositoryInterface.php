<?php

namespace App\Repositories\Contracts;

use App\Models\ZabbixConnection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ZabbixConnectionRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function all(): Collection;

    public function find(int $id): ?ZabbixConnection;

    public function active(): Collection;

    public function activeForCompany(int $companyId): Collection;

    public function activeDefault(): ?ZabbixConnection;

    public function defaultForCompany(int $companyId): ?ZabbixConnection;

    public function create(array $data): Model;

    public function update(Model $connection, array $data): Model;

    public function delete(Model $connection): bool;
}
