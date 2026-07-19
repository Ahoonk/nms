<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CompanyRepository extends EloquentRepository implements CompanyRepositoryInterface
{
    protected function model(): string
    {
        return Company::class;
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

    public function find(int $id): ?Company
    {
        return parent::find($id);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function update(Model $company, array $data): Model
    {
        return parent::update($company, $data);
    }

    public function delete(Model $company): bool
    {
        return parent::delete($company);
    }
}
