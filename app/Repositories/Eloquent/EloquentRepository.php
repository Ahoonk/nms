<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class EloquentRepository
{
    abstract protected function model(): string;

    protected function query(): Builder
    {
        $model = $this->model();

        return $model::query();
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function find(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function create(array $data): Model
    {
        $model = $this->model();

        return $model::create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
