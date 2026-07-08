<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Repositories;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    public function paginate(int $perPage = 15, array $filters = [], ?string $sortBy = null, string $sortDirection = 'asc'): LengthAwarePaginator;

    public function findOrFail(int|string $id): Product;

    public function create(array $attributes): Product;

    public function update(int|string $id, array $attributes): Product;

    public function delete(int|string $id): bool;

    public function skuExists(string $sku, ?int $ignoreId = null): bool;
}
