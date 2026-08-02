<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Services;

use App\Modules\Product\Application\DTO\ApproveProductVersionDTO;
use App\Modules\Product\Application\DTO\CreateProductVersionDTO;
use App\Modules\Product\Application\DTO\UpdateProductVersionDTO;
use App\Modules\Product\Domain\Repositories\ProductRepository;
use App\Modules\Product\Domain\Repositories\ProductVersionRepository;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Support\Collection;

final class ProductVersionService extends BaseService
{
    private const VALID_COMPATIBILITY = ['NONE', 'BACKWARD', 'FORWARD', 'FULL'];

    public function __construct(
        private readonly ProductVersionRepository $versions,
        private readonly ProductRepository $products,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function createDraft(int $productId, CreateProductVersionDTO $dto, ?int $userId = null): array
    {
        $product = $this->products->findOrFail($productId);
        $this->assertCompatibility($dto->compatibility_rule);

        $version = $this->inTransaction(function () use ($product, $dto, $userId) {
            return $this->versions->create([
                'product_id' => $product->id,
                'version_number' => $this->versions->nextVersionNumber($product->id),
                'status' => 'DRAFT',
                'effective_from' => $dto->effective_from,
                'effective_to' => $dto->effective_to,
                'compatibility_rule' => $dto->compatibility_rule,
                'change_summary' => $dto->change_summary,
                'payload' => $dto->payload,
                'created_by' => $userId,
            ]);
        });

        $this->logger->info('product_version.draft_created', [
            'product_id' => $productId,
            'version_id' => $version->id,
            'version_number' => $version->version_number,
        ]);

        return $version->toArray();
    }

    public function updateDraft(int $productId, int $versionId, UpdateProductVersionDTO $dto): array
    {
        $entity = $this->versions->findByProductOrFail($productId, $versionId);

        if ($entity->status !== 'DRAFT') {
            throw new DomainException('Only draft versions can be edited', 422);
        }

        $this->assertCompatibility($dto->compatibility_rule);

        $updated = $this->inTransaction(function () use ($productId, $versionId, $dto) {
            return $this->versions->update($productId, $versionId, [
                'effective_from' => $dto->effective_from,
                'effective_to' => $dto->effective_to,
                'compatibility_rule' => $dto->compatibility_rule,
                'change_summary' => $dto->change_summary,
                'payload' => $dto->payload,
            ]);
        });

        $this->logger->info('product_version.draft_updated', [
            'product_id' => $productId,
            'version_id' => $versionId,
        ]);

        return $updated->toArray();
    }

    public function approve(int $productId, int $versionId, ApproveProductVersionDTO $dto, ?int $userId = null): array
    {
        $entity = $this->versions->findByProductOrFail($productId, $versionId);

        if ($entity->status !== 'DRAFT') {
            throw new DomainException('Only draft versions can be approved', 422);
        }

        $effectiveFrom = $dto->effective_from ?? $entity->effective_from?->format('Y-m-d');

        if (! $effectiveFrom) {
            throw new DomainException('effective_from is required for approval', 422);
        }

        $effectiveTo = $dto->effective_to ?? $entity->effective_to?->format('Y-m-d');

        if ($effectiveTo && $effectiveTo < $effectiveFrom) {
            throw new DomainException('effective_to must be greater or equal to effective_from', 422);
        }

        $latestApproved = $this->versions->latestApproved($productId);

        if ($latestApproved && $entity->compatibility_rule === 'NONE') {
            $previousEnd = $latestApproved->effective_to?->format('Y-m-d');

            if ($previousEnd === null || $effectiveFrom <= $previousEnd) {
                throw new DomainException('Compatibility NONE requires non-overlapping effective windows', 422);
            }
        }

        $approved = $this->inTransaction(function () use ($productId, $versionId, $dto, $userId, $effectiveFrom, $effectiveTo) {
            return $this->versions->update($productId, $versionId, [
                'status' => 'APPROVED',
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'change_summary' => $dto->change_summary,
                'approved_at' => now(),
                'approved_by' => $userId,
            ]);
        });

        $this->logger->info('product_version.approved', [
            'product_id' => $productId,
            'version_id' => $versionId,
            'version_number' => $approved->version_number,
        ]);

        return $approved->toArray();
    }

    public function markObsolete(int $productId, int $versionId): array
    {
        $entity = $this->versions->findByProductOrFail($productId, $versionId);

        if ($entity->status !== 'APPROVED') {
            throw new DomainException('Only approved versions can be marked obsolete', 422);
        }

        $obsolete = $this->inTransaction(function () use ($productId, $versionId) {
            return $this->versions->update($productId, $versionId, [
                'status' => 'OBSOLETE',
                'effective_to' => now()->toDateString(),
            ]);
        });

        $this->logger->info('product_version.marked_obsolete', [
            'product_id' => $productId,
            'version_id' => $versionId,
        ]);

        return $obsolete->toArray();
    }

    public function delete(int $productId, int $versionId): void
    {
        $entity = $this->versions->findByProductOrFail($productId, $versionId);

        if ($entity->status === 'APPROVED') {
            throw new DomainException('Approved versions must be obsoleted before deletion', 422);
        }

        $this->inTransaction(function () use ($productId, $versionId): void {
            $this->versions->delete($productId, $versionId);
        });

        $this->logger->info('product_version.deleted', [
            'product_id' => $productId,
            'version_id' => $versionId,
        ]);
    }

    public function history(int $productId): Collection
    {
        $this->products->findOrFail($productId);

        return $this->versions->history($productId);
    }

    public function findEffectiveVersion(int $productId, string $referenceDate): ?array
    {
        $this->products->findOrFail($productId);

        $cacheKey = sprintf('product:%d:effective-version:%s', $productId, $referenceDate);

        $entity = $this->cacheRemember($cacheKey, 300, function () use ($productId, $referenceDate) {
            return $this->versions->findEffectiveVersionByDate($productId, $referenceDate);
        });

        return $entity?->toArray();
    }

    private function assertCompatibility(string $compatibilityRule): void
    {
        if (! in_array($compatibilityRule, self::VALID_COMPATIBILITY, true)) {
            throw new DomainException('Invalid compatibility rule', 422, [
                'compatibility_rule' => self::VALID_COMPATIBILITY,
            ]);
        }
    }
}
