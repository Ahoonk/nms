<?php

namespace App\Repositories\Eloquent;

use App\Models\Site;
use App\Repositories\Contracts\SiteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SiteRepository extends EloquentRepository implements SiteRepositoryInterface
{
    protected function model(): string
    {
        return Site::class;
    }

    public function paginateByCompany(?int $companyId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with('company')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allByCompany(?int $companyId = null): Collection
    {
        return $this->query()
            ->with('company')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Site
    {
        return parent::find($id);
    }
}
