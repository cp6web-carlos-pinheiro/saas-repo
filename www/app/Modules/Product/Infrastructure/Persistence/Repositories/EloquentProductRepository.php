<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Persistence\Repositories;

use App\Modules\Product\Domain\Repositories\ProductRepository;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Application\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentProductRepository extends BaseRepository implements ProductRepository
{
    private const ALLOWED_SORT_COLUMNS = ['id', 'sku', 'product_type', 'lead_time_days', 'is_active', 'created_at'];

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $perPage = 15, array $filters = [], ?string $sortBy = null, string $sortDirection = 'asc'): LengthAwarePaginator
    {
        $query = $this->query()
            ->when(! empty($filters['q']), static function ($builder) use ($filters): void {
                $term = (string) $filters['q'];
                $builder->where(static function ($nested) use ($term): void {
                    $nested->where('sku', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when(! empty($filters['sku']), static fn ($builder) => $builder->where('sku', 'like', '%'.(string) $filters['sku'].'%'))
            ->when(! empty($filters['product_type']), static fn ($builder) => $builder->where('product_type', (string) $filters['product_type']))
            ->when(isset($filters['is_active']), static fn ($builder) => $builder->where('is_active', (bool) $filters['is_active']))
            ->when(isset($filters['lead_time_min']), static fn ($builder) => $builder->where('lead_time_days', '>=', (int) $filters['lead_time_min']))
            ->when(isset($filters['lead_time_max']), static fn ($builder) => $builder->where('lead_time_days', '<=', (int) $filters['lead_time_max']));

        $sortBy = in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $sortBy : 'sku';
        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage);
    }

    public function findOrFail(int|string $id): Product
    {
        /** @var Product $product */
        $product = parent::findOrFail($id);

        return $product;
    }

    public function create(array $attributes): Product
    {
        /** @var Product $product */
        $product = parent::create($attributes);

        return $product;
    }

    public function update(int|string $id, array $attributes): Product
    {
        /** @var Product $product */
        $product = parent::update($id, $attributes);

        return $product;
    }

    public function delete(int|string $id): bool
    {
        return parent::delete($id);
    }

    public function skuExists(string $sku, ?int $ignoreId = null): bool
    {
        return $this->query()
            ->when($ignoreId !== null, static fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('sku', $sku)
            ->exists();
    }
}
