<?php

declare(strict_types=1);

namespace App\Modules\Bom\Application\Services;

use App\Modules\Bom\Domain\Repositories\BomExplosionRepository;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use RuntimeException;

final class BomExplosionService extends BaseService
{
    public function __construct(
        private readonly BomExplosionRepository $repository,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function explode(
        int $productId,
        string $referenceDate,
        ?int $versionNumber = null,
        int $maxDepth = 100
    ): array {
        $cacheKey = sprintf(
            'bom:explode:%d:%s:%s:%d',
            $productId,
            $referenceDate,
            $versionNumber !== null ? (string) $versionNumber : 'latest',
            $maxDepth
        );

        try {
            $result = $this->cacheRemember($cacheKey, 300, function () use ($productId, $referenceDate, $versionNumber, $maxDepth) {
                return $this->repository->explode($productId, $referenceDate, $versionNumber, $maxDepth);
            });
        } catch (RuntimeException $e) {
            throw new DomainException($e->getMessage(), 422);
        }

        if (! $result['root_bom_header']) {
            throw new DomainException('No BOM version found for product and reference date', 404);
        }

        $this->logger->info('bom.exploded', [
            'product_id' => $productId,
            'reference_date' => $referenceDate,
            'version_number' => $versionNumber,
            'rows' => count($result['exploded_materials']),
            'has_cycle' => $result['has_cycle'],
        ]);

        return $result;
    }
}
