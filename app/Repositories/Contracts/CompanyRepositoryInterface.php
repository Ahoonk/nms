<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CompanyRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function all(): Collection;

    public function find(int $id): ?Company;

    public function create(array $data): Model;

    public function update(Model $company, array $data): Model;

    public function delete(Model $company): bool;
}
