<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Bom\Application\Services\FreezeBomSnapshotService;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Production\Application\Services\FreezeProductionOrderSnapshotService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOutput;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersion;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersionSnapshot;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProductionOrderService extends BaseService
{
    public function __construct(
        private readonly FreezeBomSnapshotService $bomSnapshotService,
        private readonly FreezeProductionOrderSnapshotService $snapshotService,
        private readonly InventoryService $inventoryService,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ProductionOrder::query()
            ->with(['product', 'warehouse', 'outputs', 'snapshot'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function show(int $productionOrderId): array
    {
        $order = ProductionOrder::query()
            ->with(['product', 'warehouse', 'outputs', 'snapshot.routingOperations', 'snapshot.bomSnapshot'])
            ->findOrFail($productionOrderId);

        return $order->toArray();
    }

    public function createManual(array $payload, ?int $userId = null): array
    {
        return $this->createOrder($payload, 'MANUAL', null, null, $userId);
    }

    public function createFromMrp(array $payload, ?int $userId = null): array
    {
        return $this->createOrder(
            $payload,
            'MRP',
            $payload['source_reference_id'] ?? null,
            $payload['source_reference_type'] ?? 'mrp_recommendation',
            $userId
        );
    }

    public function partialProduction(int $productionOrderId, array $payload, ?int $userId = null): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);

        if (in_array($order->status, ['CANCELLED', 'COMPLETED'], true)) {
            throw new DomainException('Cannot produce against a closed production order', 422);
        }

        $quantityCompleted = round((float) $payload['quantity_completed'], 6);
        $quantityScrapped = round((float) ($payload['quantity_scrapped'] ?? 0), 6);

        $result = $this->inTransaction(function () use ($order, $payload, $quantityCompleted, $quantityScrapped, $userId) {
            $output = ProductionOrderOutput::query()->create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'quantity_completed' => $quantityCompleted,
                'quantity_scrapped' => $quantityScrapped,
                'operation_no' => $payload['operation_no'] ?? null,
                'work_center_id' => $payload['work_center_id'] ?? null,
                'setup_time_minutes' => round((float) ($payload['setup_time_minutes'] ?? 0), 6),
                'process_time_minutes' => round((float) ($payload['process_time_minutes'] ?? 0), 6),
                'inspection_status' => strtoupper((string) ($payload['inspection_status'] ?? 'APPROVED')),
                'inspected_at' => isset($payload['inspected_at']) ? now()->parse($payload['inspected_at']) : null,
                'inspection_notes' => $payload['inspection_notes'] ?? null,
                'lot_number' => $payload['lot_number'] ?? null,
                'produced_at' => isset($payload['produced_at']) ? now()->parse($payload['produced_at']) : now(),
                'created_by' => $userId,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            $this->postFinishedGoodsReceipt($order, $output, $userId);

            $order->quantity_produced = round((float) $order->quantity_produced + $quantityCompleted, 6);
            $order->quantity_scrapped = round((float) $order->quantity_scrapped + $quantityScrapped, 6);

            if ($order->quantity_produced > 0 && $order->status === 'RELEASED') {
                $order->status = 'IN_PROGRESS';
                $order->started_at ??= now();
            }

            if ($order->quantity_produced >= $order->quantity_planned) {
                $order->status = 'COMPLETED';
                $order->completed_at ??= now();
                $order->completed_by = $userId;
            } elseif ($order->quantity_produced > 0) {
                $order->status = 'PARTIALLY_COMPLETED';
            }

            $order->save();

            return $output->refresh()->load('productionOrder');
        });

        $this->logger->info('production_order.partial_recorded', [
            'production_order_id' => $productionOrderId,
            'quantity_completed' => $quantityCompleted,
            'quantity_scrapped' => $quantityScrapped,
        ]);

        return [
            'output' => $result->toArray(),
            'production_order' => $order->fresh()->load(['product', 'warehouse', 'outputs'])->toArray(),
        ];
    }

    public function complete(int $productionOrderId, ?int $userId = null): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);

        if (in_array($order->status, ['CANCELLED', 'COMPLETED'], true)) {
            throw new DomainException('Production order is already closed', 422);
        }

        $updated = $this->inTransaction(function () use ($order, $userId) {
            if ($order->quantity_produced < $order->quantity_planned) {
                $missingQuantity = round((float) $order->quantity_planned - (float) $order->quantity_produced, 6);

                if ($missingQuantity > 0) {
                    $output = ProductionOrderOutput::query()->create([
                        'company_id' => $order->company_id,
                        'production_order_id' => $order->id,
                        'quantity_completed' => $missingQuantity,
                        'quantity_scrapped' => 0,
                        'operation_no' => null,
                        'work_center_id' => null,
                        'setup_time_minutes' => 0,
                        'process_time_minutes' => 0,
                        'inspection_status' => 'APPROVED',
                        'inspected_at' => now(),
                        'inspection_notes' => null,
                        'lot_number' => null,
                        'produced_at' => now(),
                        'created_by' => $userId,
                        'metadata' => ['auto_completion' => true],
                    ]);

                    $this->postFinishedGoodsReceipt($order, $output, $userId);
                    $order->quantity_produced = round((float) $order->quantity_planned, 6);
                }
            }

            $order->status = 'COMPLETED';
            $order->completed_at = now();
            $order->completed_by = $userId;
            $order->save();

            return $order;
        });

        $this->logger->info('production_order.completed', [
            'production_order_id' => $productionOrderId,
        ]);

        return $updated->fresh()->load(['product', 'warehouse', 'outputs'])->toArray();
    }

    public function release(int $productionOrderId, ?int $userId = null): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);

        if ($order->status !== 'DRAFT') {
            throw new DomainException('Only draft orders can be released', 422);
        }

        $released = $this->inTransaction(function () use ($order, $userId) {
            $order->status = 'RELEASED';
            $order->released_at = now();
            $order->released_by = $userId;
            $order->save();

            return $order;
        });

        // Freeze full production snapshot atomically at release
        $this->snapshotService->freeze($released->id, $userId);

        return $released->fresh()->load(['product', 'warehouse', 'outputs', 'snapshot.routingOperations'])->toArray();
    }

    private function createOrder(array $payload, string $sourceType, ?int $sourceReferenceId, ?string $sourceReferenceType, ?int $userId): array
    {
        Product::query()->findOrFail((int) $payload['product_id']);

        if (! empty($payload['warehouse_id'])) {
            Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        }

        $bomHeaderId = $payload['bom_header_id'] ?? null;
        $bomVersionNumber = $payload['bom_version_number'] ?? null;
        $routingVersionId = $payload['routing_version_id'] ?? null;
        $routingVersionNumber = $payload['routing_version_number'] ?? null;

        if ($routingVersionId !== null && $routingVersionNumber === null) {
            $routingVersion = RoutingVersion::query()->findOrFail((int) $routingVersionId);
            $routingVersionNumber = (int) $routingVersion->version_number;
        }

        $order = $this->inTransaction(function () use ($payload, $sourceType, $sourceReferenceId, $sourceReferenceType, $bomHeaderId, $bomVersionNumber, $routingVersionId, $routingVersionNumber, $userId) {
            $orderNumber = $this->generateOrderNumber();

            $entity = ProductionOrder::query()->create([
                'product_id' => $payload['product_id'],
                'warehouse_id' => $payload['warehouse_id'] ?? null,
                'bom_header_id' => $bomHeaderId,
                'bom_version_number' => $bomVersionNumber,
                'routing_version_id' => $routingVersionId,
                'routing_version_number' => $routingVersionNumber,
                'source_type' => $sourceType,
                'source_reference_id' => $sourceReferenceId,
                'source_reference_type' => $sourceReferenceType,
                'order_number' => $orderNumber,
                'status' => 'DRAFT',
                'quantity_planned' => round((float) $payload['quantity_planned'], 6),
                'quantity_produced' => 0,
                'quantity_scrapped' => round((float) ($payload['quantity_scrapped'] ?? 0), 6),
                'scheduled_start_date' => $payload['scheduled_start_date'] ?? null,
                'scheduled_end_date' => $payload['scheduled_end_date'] ?? null,
                'created_by' => $userId,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            $snapshot = $this->bomSnapshotService->freezeForProductionOrder(
                productionOrderId: (int) $entity->id,
                productId: (int) $entity->product_id,
                referenceDate: (string) ($payload['reference_date'] ?? now()->toDateString()),
                versionNumber: isset($payload['bom_version_number']) ? (int) $payload['bom_version_number'] : null,
                productionOrderQuantity: (float) $entity->quantity_planned,
                createdBy: $userId,
            );

            $entity->bom_header_id = $snapshot['source_bom_header_id'];
            $entity->bom_version_number = $snapshot['source_bom_version_number'];

            if ($routingVersionId !== null && $routingVersionNumber === null) {
                $routingVersion = RoutingVersion::query()->findOrFail((int) $routingVersionId);
                $routingVersionNumber = (int) $routingVersion->version_number;
            }

            $entity->routing_version_id = $routingVersionId;
            $entity->routing_version_number = $routingVersionNumber;
            $entity->save();

            return $entity;
        });

        $this->logger->info('production_order.created', [
            'production_order_id' => $order->id,
            'order_number' => $order->order_number,
            'source_type' => $sourceType,
        ]);

        return $order->fresh()->load(['product', 'warehouse', 'outputs'])->toArray();
    }

    private function postFinishedGoodsReceipt(ProductionOrder $order, ProductionOrderOutput $output, ?int $userId): void
    {
        if ($order->warehouse_id === null || (float) $output->quantity_completed <= 0) {
            return;
        }

        $this->inventoryService->postMovement([
            'warehouse_id' => $order->warehouse_id,
            'product_id' => $order->product_id,
            'movement_type' => 'RECEIPT',
            'quantity' => (float) $output->quantity_completed,
            'lot_number' => $output->lot_number,
            'reference_type' => 'production_order',
            'reference_id' => $order->id,
            'notes' => $output->inspection_notes,
            'metadata' => [
                'production_order_output_id' => $output->id,
                'quantity_scrapped' => (float) $output->quantity_scrapped,
                'operation_no' => $output->operation_no,
                'work_center_id' => $output->work_center_id,
                'setup_time_minutes' => (float) $output->setup_time_minutes,
                'process_time_minutes' => (float) $output->process_time_minutes,
                'inspection_status' => $output->inspection_status,
                'inspected_at' => $output->inspected_at?->toDateTimeString(),
                'inspection_notes' => $output->inspection_notes,
            ],
            'movement_at' => $output->produced_at?->toDateTimeString() ?? now()->toDateTimeString(),
        ], $userId);
    }

    private function generateOrderNumber(): string
    {
        $datePart = now()->format('Ymd');
        $sequence = (int) ((ProductionOrder::query()->max('id') ?? 0) + 1);

        return sprintf('PO-%s-%06d', $datePart, $sequence);
    }
}
