<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Persistence\Repositories;

use App\Modules\Product\Domain\Repositories\ProductVersionRepository;
use App\Modules\Product\Infrastructure\Persistence\Models\ProductVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

final class EloquentProductVersionRepository implements ProductVersionRepository
{
    public function __construct(private readonly ProductVersion $model)
    {
    }

    public function nextVersionNumber(int $productId): int
    {
        $last = $this->model->newQuery()->where('product_id', $productId)->max('version_number');

        return ((int) $last) + 1;
    }

    public function create(array $attributes): ProductVersion
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function findByProductOrFail(int $productId, int $versionId): ProductVersion
    {
        $entity = $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('id', $versionId)
            ->first();

        if (! $entity) {
            throw (new ModelNotFoundException())->setModel(ProductVersion::class, [$versionId]);
        }

        return $entity;
    }

    public function update(int $productId, int $versionId, array $attributes): ProductVersion
    {
        $entity = $this->findByProductOrFail($productId, $versionId);
        $entity->fill($attributes);
        $entity->save();

        return $entity;
    }

    public function delete(int $productId, int $versionId): bool
    {
        $entity = $this->findByProductOrFail($productId, $versionId);

        return (bool) $entity->delete();
    }

    public function history(int $productId): Collection
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->orderByDesc('version_number')
            ->get();
    }

    public function latestApproved(int $productId): ?ProductVersion
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('status', 'APPROVED')
            ->orderByDesc('version_number')
            ->first();
    }

    public function findEffectiveVersionByDate(int $productId, string $referenceDate): ?ProductVersion
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $referenceDate)
            ->where(function ($query) use ($referenceDate): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $referenceDate);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('version_number')
            ->first();
    }
}
