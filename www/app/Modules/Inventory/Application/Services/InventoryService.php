<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerAllocation;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class InventoryService extends BaseService
{
    private const MOVEMENT_EFFECTS = [
        'RECEIPT' => ['source_bucket' => null, 'target_bucket' => 'AVAILABLE', 'balance' => ['qty_available' => 1]],
        'ISSUE' => ['source_bucket' => 'AVAILABLE', 'target_bucket' => null, 'balance' => ['qty_available' => -1]],
        'RESERVE' => ['source_bucket' => 'AVAILABLE', 'target_bucket' => 'RESERVED', 'balance' => ['qty_available' => -1, 'qty_reserved' => 1]],
        'RELEASE' => ['source_bucket' => 'RESERVED', 'target_bucket' => 'AVAILABLE', 'balance' => ['qty_reserved' => -1, 'qty_available' => 1]],
        'TRANSFER_OUT' => ['source_bucket' => 'AVAILABLE', 'target_bucket' => 'IN_TRANSIT', 'balance' => ['qty_available' => -1, 'qty_in_transit' => 1]],
        'TRANSFER_IN' => ['source_bucket' => 'IN_TRANSIT', 'target_bucket' => 'AVAILABLE', 'balance' => ['qty_in_transit' => -1, 'qty_available' => 1]],
        'INSPECTION_HOLD' => ['source_bucket' => 'AVAILABLE', 'target_bucket' => 'INSPECTION', 'balance' => ['qty_available' => -1, 'qty_inspection' => 1]],
        'INSPECTION_RELEASE' => ['source_bucket' => 'INSPECTION', 'target_bucket' => 'AVAILABLE', 'balance' => ['qty_inspection' => -1, 'qty_available' => 1]],
    ];

    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = InventoryBalance::query()
            ->with(['warehouse', 'product'])
            ->orderBy('warehouse_id')
            ->orderBy('product_id');

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', (int) $filters['warehouse_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        return $query->paginate($perPage);
    }

    public function upsertBalance(array $payload): array
    {
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        Product::query()->findOrFail((int) $payload['product_id']);

        $this->assertNonNegativeBuckets(
            (float) $payload['qty_available'],
            (float) $payload['qty_reserved'],
            (float) $payload['qty_in_transit'],
            (float) $payload['qty_inspection']
        );

        $entity = $this->inTransaction(function () use ($payload) {
            return InventoryBalance::query()->updateOrCreate(
                [
                    'warehouse_id' => $payload['warehouse_id'],
                    'product_id' => $payload['product_id'],
                ],
                [
                    'qty_available' => round((float) $payload['qty_available'], 6),
                    'qty_reserved' => round((float) $payload['qty_reserved'], 6),
                    'qty_in_transit' => round((float) $payload['qty_in_transit'], 6),
                    'qty_inspection' => round((float) $payload['qty_inspection'], 6),
                    'last_movement_at' => now(),
                ]
            );
        });

        return $this->serialize($entity->refresh()->load(['warehouse', 'product']));
    }

    public function adjustBuckets(array $payload): array
    {
        $entity = InventoryBalance::query()->firstOrNew([
            'warehouse_id' => $payload['warehouse_id'],
            'product_id' => $payload['product_id'],
        ]);

        if (! $entity->exists) {
            Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
            Product::query()->findOrFail((int) $payload['product_id']);
            $entity->fill([
                'qty_available' => 0,
                'qty_reserved' => 0,
                'qty_in_transit' => 0,
                'qty_inspection' => 0,
            ]);
        }

        $updated = $this->inTransaction(function () use ($entity, $payload) {
            $qtyAvailable = round((float) $entity->qty_available + (float) ($payload['delta_available'] ?? 0), 6);
            $qtyReserved = round((float) $entity->qty_reserved + (float) ($payload['delta_reserved'] ?? 0), 6);
            $qtyInTransit = round((float) $entity->qty_in_transit + (float) ($payload['delta_in_transit'] ?? 0), 6);
            $qtyInspection = round((float) $entity->qty_inspection + (float) ($payload['delta_inspection'] ?? 0), 6);

            $this->assertNonNegativeBuckets($qtyAvailable, $qtyReserved, $qtyInTransit, $qtyInspection);

            $entity->fill([
                'qty_available' => $qtyAvailable,
                'qty_reserved' => $qtyReserved,
                'qty_in_transit' => $qtyInTransit,
                'qty_inspection' => $qtyInspection,
                'last_movement_at' => now(),
            ]);
            $entity->save();

            return $entity;
        });

        return $this->serialize($updated->refresh()->load(['warehouse', 'product']));
    }

    public function paginateMovements(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = StockLedgerMovement::query()
            ->with(['warehouse', 'product'])
            ->orderByDesc('movement_at')
            ->orderByDesc('id');

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', (int) $filters['warehouse_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        if (isset($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        return $query->paginate($perPage);
    }

    public function postMovement(array $payload, ?int $createdBy = null): array
    {
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        Product::query()->findOrFail((int) $payload['product_id']);

        if (! array_key_exists($payload['movement_type'], self::MOVEMENT_EFFECTS)) {
            throw new DomainException('Unsupported stock movement type', 422);
        }

        $movementAt = isset($payload['movement_at'])
            ? Carbon::parse($payload['movement_at'])
            : now();

        $effect = self::MOVEMENT_EFFECTS[$payload['movement_type']];
        $quantity = round((float) $payload['quantity'], 6);

        $result = $this->inTransaction(function () use ($payload, $createdBy, $movementAt, $effect, $quantity) {
            $balance = InventoryBalance::query()
                ->where('warehouse_id', $payload['warehouse_id'])
                ->where('product_id', $payload['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = InventoryBalance::query()->create([
                    'warehouse_id' => $payload['warehouse_id'],
                    'product_id' => $payload['product_id'],
                    'qty_available' => 0,
                    'qty_reserved' => 0,
                    'qty_in_transit' => 0,
                    'qty_inspection' => 0,
                    'last_movement_at' => null,
                ]);
            }

            $movement = StockLedgerMovement::query()->create([
                'company_id' => $balance->company_id,
                'warehouse_id' => $balance->warehouse_id,
                'product_id' => $balance->product_id,
                'movement_type' => $payload['movement_type'],
                'source_bucket' => $effect['source_bucket'],
                'target_bucket' => $effect['target_bucket'],
                'quantity' => $quantity,
                'allocation_strategy' => $payload['allocation_strategy'] ?? null,
                'lot_number' => $payload['lot_number'] ?? null,
                'expires_at' => $payload['expires_at'] ?? null,
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
                'movement_at' => $movementAt,
                'created_by' => $createdBy,
            ]);

            if ($payload['movement_type'] === 'ISSUE') {
                $this->allocateIssueMovement($movement, $payload, $movementAt);
            }

            $this->applyBalanceEffect($balance, $effect['balance'], $quantity);
            $balance->last_movement_at = $movementAt;
            $balance->save();

            return $movement->refresh()->load(['warehouse', 'product', 'allocations.receiptMovement']);
        });

        return [
            'movement' => $result->toArray(),
            'balance' => InventoryBalance::query()
                ->with(['warehouse', 'product'])
                ->where('warehouse_id', $payload['warehouse_id'])
                ->where('product_id', $payload['product_id'])
                ->first()
                ?->toArray(),
        ];
    }

    private function assertNonNegativeBuckets(
        float $qtyAvailable,
        float $qtyReserved,
        float $qtyInTransit,
        float $qtyInspection
    ): void {
        if ($qtyAvailable < 0 || $qtyReserved < 0 || $qtyInTransit < 0 || $qtyInspection < 0) {
            throw new DomainException('Inventory buckets cannot be negative', 422);
        }
    }

    private function applyBalanceEffect(InventoryBalance $balance, array $effects, float $quantity): void
    {
        foreach ($effects as $column => $direction) {
            $balance->{$column} = round((float) $balance->{$column} + ($direction * $quantity), 6);
        }

        $this->assertNonNegativeBuckets(
            (float) $balance->qty_available,
            (float) $balance->qty_reserved,
            (float) $balance->qty_in_transit,
            (float) $balance->qty_inspection
        );
    }

    private function allocateIssueMovement(StockLedgerMovement $issueMovement, array $payload, Carbon $movementAt): void
    {
        $strategy = strtoupper((string) ($payload['allocation_strategy'] ?? 'FIFO'));

        $receiptQuery = StockLedgerMovement::query()
            ->where('warehouse_id', $issueMovement->warehouse_id)
            ->where('product_id', $issueMovement->product_id)
            ->where('target_bucket', 'AVAILABLE')
            ->whereIn('movement_type', ['RECEIPT', 'TRANSFER_IN', 'INSPECTION_RELEASE', 'RELEASE'])
            ->where('movement_at', '<=', $movementAt)
            ->orderByRaw($strategy === 'FEFO'
                ? 'CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END, expires_at ASC, movement_at ASC, id ASC'
                : 'movement_at ASC, id ASC')
            ->lockForUpdate();

        /** @var Collection<int, StockLedgerMovement> $receipts */
        $receipts = $receiptQuery->get();
        $remainingToAllocate = (float) $issueMovement->quantity;
        $sequence = 1;

        foreach ($receipts as $receipt) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            $allocatedQuantity = (float) StockLedgerAllocation::query()
                ->where('receipt_movement_id', $receipt->id)
                ->sum('quantity');

            $availableQuantity = round((float) $receipt->quantity - $allocatedQuantity, 6);

            if ($availableQuantity <= 0) {
                continue;
            }

            $quantityToAllocate = min($availableQuantity, $remainingToAllocate);

            StockLedgerAllocation::query()->create([
                'company_id' => $issueMovement->company_id,
                'issue_movement_id' => $issueMovement->id,
                'receipt_movement_id' => $receipt->id,
                'quantity' => round($quantityToAllocate, 6),
                'sequence_no' => $sequence++,
            ]);

            $remainingToAllocate = round($remainingToAllocate - $quantityToAllocate, 6);
        }

        if ($remainingToAllocate > 0) {
            throw new DomainException('Insufficient stock for issue movement', 422);
        }
    }

    private function serialize(InventoryBalance $entity): array
    {
        return [
            'id' => (int) $entity->id,
            'warehouse_id' => (int) $entity->warehouse_id,
            'product_id' => (int) $entity->product_id,
            'qty_available' => (float) $entity->qty_available,
            'qty_reserved' => (float) $entity->qty_reserved,
            'qty_in_transit' => (float) $entity->qty_in_transit,
            'qty_inspection' => (float) $entity->qty_inspection,
            'qty_free' => round((float) $entity->qty_available - (float) $entity->qty_reserved, 6),
            'last_movement_at' => $entity->last_movement_at?->toDateTimeString(),
            'warehouse' => $entity->warehouse?->toArray(),
            'product' => $entity->product?->toArray(),
        ];
    }
}
