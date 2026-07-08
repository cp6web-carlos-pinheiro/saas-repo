<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Repositories;

use App\Modules\Product\Infrastructure\Persistence\Models\ProductVersion;
use Illuminate\Support\Collection;

interface ProductVersionRepository
{
    public function nextVersionNumber(int $productId): int;

    public function create(array $attributes): ProductVersion;

    public function findByProductOrFail(int $productId, int $versionId): ProductVersion;

    public function update(int $productId, int $versionId, array $attributes): ProductVersion;

    public function history(int $productId): Collection;

    public function latestApproved(int $productId): ?ProductVersion;

    public function findEffectiveVersionByDate(int $productId, string $referenceDate): ?ProductVersion;
}
