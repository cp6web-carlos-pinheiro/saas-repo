<?php

declare(strict_types=1);

namespace App\Modules\Bom\Domain\Repositories;

interface BomExplosionRepository
{
    public function explode(
        int $productId,
        string $referenceDate,
        ?int $versionNumber = null,
        int $maxDepth = 100
    ): array;
}
