<?php

declare(strict_types=1);

namespace App\Shared\Application\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository
{
    public function __construct(protected Model $model) {}

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function findOrFail(int|string $id): Model
    {
        $entity = $this->query()->find($id);

        if (! $entity) {
            throw (new ModelNotFoundException)->setModel($this->model::class, [(string) $id]);
        }

        return $entity;
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(int|string $id, array $attributes): Model
    {
        $entity = $this->findOrFail($id);
        $entity->fill($attributes);
        $entity->save();

        return $entity;
    }

    public function delete(int|string $id): bool
    {
        $entity = $this->findOrFail($id);

        return (bool) $entity->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }
}
