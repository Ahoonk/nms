<?php

namespace App\Repositories\Eloquent;

use App\Models\Device;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DeviceRepository extends EloquentRepository implements DeviceRepositoryInterface
{
    protected function model(): string
    {
        return Device::class;
    }

    public function paginateByCompany(?int $companyId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['site.company'])
            ->when($companyId, fn ($query) => $query->whereHas('site', fn ($siteQuery) => $siteQuery->where('company_id', $companyId)))
            ->orderBy('devices.hostname')
            ->paginate($perPage);
    }

    public function allByCompany(?int $companyId = null): Collection
    {
        return $this->query()
            ->with(['site.company'])
            ->when($companyId, fn ($query) => $query->whereHas('site', fn ($siteQuery) => $siteQuery->where('company_id', $companyId)))
            ->orderBy('devices.hostname')
            ->get();
    }

    public function find(int $id): ?Device
    {
        return parent::find($id);
    }
}
