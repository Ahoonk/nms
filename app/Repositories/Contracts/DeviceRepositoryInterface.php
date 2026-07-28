<?php

namespace App\Repositories\Contracts;

use App\Models\Device;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface DeviceRepositoryInterface
{
    public function paginateByCompany(?int $companyId = null, int $perPage = 15): LengthAwarePaginator;

    public function allByCompany(?int $companyId = null): Collection;

    public function find(int $id): ?Device;

    public function findByZabbixHostId(string $hostId): ?Device;

    public function create(array $data): Model;

    public function update(Model $device, array $data): Model;

    public function delete(Model $device): bool;
}
