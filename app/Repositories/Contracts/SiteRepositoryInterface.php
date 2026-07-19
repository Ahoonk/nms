<?php

namespace App\Repositories\Contracts;

use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface SiteRepositoryInterface
{
    public function paginateByCompany(?int $companyId = null, int $perPage = 15): LengthAwarePaginator;

    public function allByCompany(?int $companyId = null): Collection;

    public function find(int $id): ?Site;

    public function create(array $data): Model;

    public function update(Model $site, array $data): Model;

    public function delete(Model $site): bool;
}
