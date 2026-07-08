<?php

declare(strict_types=1);

namespace App\Modules\Bom\Application\Services;

use App\Modules\Bom\Domain\Repositories\BomExplosionRepository;
use App\Modules\Bom\Infrastructure\Persistence\Models\ProductionOrderBomItemSnapshot;
use App\Modules\Bom\Infrastructure\Persistence\Models\ProductionOrderBomSnapshot;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use App\Shared\Presentation\Exceptions\DomainException;
use RuntimeException;

final class FreezeBomSnapshotService extends BaseService
{
    public function __construct(
        private readonly BomExplosionRepository $repository,
        private readonly TenantContext $tenant,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function freezeForProductionOrder(
        int $productionOrderId,
        int $productId,
        string $referenceDate,
        ?int $versionNumber = null,
        float $productionOrderQuantity = 1,
        ?int $createdBy = null,
        int $maxDepth = 100
    ): array {
        $companyId = $this->tenant->companyId();

        if ($companyId === null) {
            throw new DomainException('Tenant context is required to freeze BOM snapshot', 422);
        }

        $alreadyFrozen = ProductionOrderBomSnapshot::query()
            ->where('company_id', $companyId)
            ->where('production_order_id', $productionOrderId)
            ->exists();

        if ($alreadyFrozen) {
            throw new DomainException('BOM snapshot already frozen for this production order', 409);
        }

        try {
            $explosion = $this->repository->explode($productId, $referenceDate, $versionNumber, $maxDepth);
        } catch (RuntimeException $e) {
            throw new DomainException($e->getMessage(), 422);
        }

        if (! $explosion['root_bom_header']) {
            throw new DomainException('No BOM version found for product and reference date', 404);
        }

        /** @var array{id:int,version_number:int,status:string,effective_from:string,effective_to:?string,product_id:int} $root */
        $root = $explosion['root_bom_header'];
        /** @var array<int,array<string,mixed>> $materials */
        $materials = $explosion['exploded_materials'];

        $snapshot = $this->inTransaction(function () use (
            $companyId,
            $productionOrderId,
            $productId,
            $productionOrderQuantity,
            $referenceDate,
            $createdBy,
            $root,
            $materials,
            $explosion
        ) {
            $hashPayload = [
                'production_order_id' => $productionOrderId,
                'product_id' => $productId,
                'reference_date' => $referenceDate,
                'source_bom_header_id' => $root['id'],
                'source_bom_version_number' => $root['version_number'],
                'materials' => $materials,
            ];

            $snapshot = ProductionOrderBomSnapshot::query()->create([
                'company_id' => $companyId,
                'production_order_id' => $productionOrderId,
                'product_id' => $productId,
                'production_order_quantity' => round($productionOrderQuantity, 6),
                'reference_date' => $referenceDate,
                'source_bom_header_id' => $root['id'],
                'source_bom_version_number' => $root['version_number'],
                'snapshot_hash' => hash('sha256', json_encode($hashPayload, JSON_THROW_ON_ERROR)),
                'has_cycle' => (bool) $explosion['has_cycle'],
                'frozen_at' => now(),
                'created_by' => $createdBy,
            ]);

            $rows = array_map(function (array $item) use ($companyId, $snapshot): array {
                return [
                    'company_id' => $companyId,
                    'production_order_bom_snapshot_id' => $snapshot->id,
                    'source_bom_header_id' => (int) $item['bom_header_id'],
                    'source_bom_version_number' => (int) $item['bom_version_number'],
                    'parent_product_id' => (int) $item['parent_product_id'],
                    'component_product_id' => (int) $item['component_product_id'],
                    'line_no' => (int) $item['line_no'],
                    'level' => (int) $item['level'],
                    'quantity_per' => round((float) $item['quantity_per'], 6),
                    'scrap_factor' => round((float) $item['scrap_factor'], 4),
                    'quantity_required' => round((float) $item['quantity_required'], 6),
                    'quantity_accumulated' => round((float) $item['quantity_accumulated'], 6),
                    'path' => (string) $item['path'],
                    'is_cycle' => (bool) $item['is_cycle'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $materials);

            if ($rows !== []) {
                ProductionOrderBomItemSnapshot::query()->insert($rows);
            }

            return $snapshot;
        });

        $this->logger->info('bom.snapshot.frozen', [
            'company_id' => $companyId,
            'production_order_id' => $productionOrderId,
            'snapshot_id' => $snapshot->id,
            'source_bom_header_id' => $root['id'],
            'source_bom_version_number' => $root['version_number'],
            'rows' => count($materials),
            'has_cycle' => (bool) $explosion['has_cycle'],
        ]);

        return [
            'snapshot_id' => (int) $snapshot->id,
            'production_order_id' => $productionOrderId,
            'product_id' => $productId,
            'source_bom_header_id' => $root['id'],
            'source_bom_version_number' => $root['version_number'],
            'items_count' => count($materials),
            'has_cycle' => (bool) $explosion['has_cycle'],
            'frozen_at' => (string) $snapshot->frozen_at,
        ];
    }
}
