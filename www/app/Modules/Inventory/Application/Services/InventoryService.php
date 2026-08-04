<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryReservation;
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
        'TRANSFER_OUT' => ['source_bucket' => 'AVAILABLE', 'target_bucket' => null, 'balance' => ['qty_available' => -1]],
        'TRANSFER_IN' => ['source_bucket' => null, 'target_bucket' => 'AVAILABLE', 'balance' => ['qty_available' => 1]],
        'INSPECTION_HOLD' => ['source_bucket' => 'AVAILABLE', 'target_bucket' => 'INSPECTION', 'balance' => ['qty_available' => -1, 'qty_inspection' => 1]],
        'INSPECTION_RELEASE' => ['source_bucket' => 'INSPECTION', 'target_bucket' => 'AVAILABLE', 'balance' => ['qty_inspection' => -1, 'qty_available' => 1]],
    ];

    private const REVERSIBLE_MOVEMENT_TYPES = [
        'RECEIPT' => 'ISSUE',
        'ISSUE' => 'RECEIPT',
        'RESERVE' => 'RELEASE',
        'RELEASE' => 'RESERVE',
        'TRANSFER_OUT' => 'TRANSFER_IN',
        'TRANSFER_IN' => 'TRANSFER_OUT',
        'INSPECTION_HOLD' => 'INSPECTION_RELEASE',
        'INSPECTION_RELEASE' => 'INSPECTION_HOLD',
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

    public function paginateReservations(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = InventoryReservation::query()
            ->with(['warehouse', 'product'])
            ->orderBy('priority')
            ->orderBy('reserved_at')
            ->orderBy('id');

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', (int) $filters['warehouse_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        if (isset($filters['reservation_origin'])) {
            $query->where('reservation_origin', $filters['reservation_origin']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
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

    public function reserveStock(array $payload, ?int $createdBy = null): array
    {
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        Product::query()->findOrFail((int) $payload['product_id']);

        $quantity = round((float) $payload['quantity'], 6);
        $priority = (int) ($payload['priority'] ?? 100);
        $reservationOrigin = strtoupper((string) $payload['reservation_origin']);

        if ($quantity <= 0) {
            throw new DomainException('Reservation quantity must be greater than zero', 422);
        }

        if (! in_array($reservationOrigin, ['SALE', 'PRODUCTION', 'MAINTENANCE'], true)) {
            throw new DomainException('Unsupported reservation origin', 422);
        }

        if ($priority < 1) {
            throw new DomainException('Reservation priority must be greater than zero', 422);
        }

        $movementAt = isset($payload['movement_at'])
            ? Carbon::parse($payload['movement_at'])
            : now();

        $result = $this->inTransaction(function () use ($payload, $createdBy, $quantity, $priority, $reservationOrigin, $movementAt) {
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

            if ((float) $balance->qty_available < $quantity) {
                throw new DomainException('Insufficient available stock for reservation', 422);
            }

            $reservation = InventoryReservation::query()->create([
                'company_id' => $balance->company_id,
                'warehouse_id' => $balance->warehouse_id,
                'product_id' => $balance->product_id,
                'reservation_origin' => $reservationOrigin,
                'priority' => $priority,
                'quantity' => $quantity,
                'status' => 'RESERVED',
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'reserved_at' => $movementAt,
                'expires_at' => isset($payload['expires_at']) ? Carbon::parse($payload['expires_at']) : null,
                'created_by' => $createdBy,
                'metadata' => array_merge((array) ($payload['metadata'] ?? []), [
                    'reservation_origin' => $reservationOrigin,
                    'reservation_priority' => $priority,
                ]),
            ]);

            $movement = $this->postMovement([
                'warehouse_id' => $payload['warehouse_id'],
                'product_id' => $payload['product_id'],
                'movement_type' => 'RESERVE',
                'quantity' => $quantity,
                'reference_type' => 'inventory_reservation',
                'reference_id' => $reservation->id,
                'notes' => $payload['notes'] ?? null,
                'metadata' => array_merge((array) ($payload['metadata'] ?? []), [
                    'reservation_id' => $reservation->id,
                    'reservation_origin' => $reservationOrigin,
                    'reservation_priority' => $priority,
                ]),
                'movement_at' => $movementAt->toDateTimeString(),
            ], $createdBy);

            return [
                'reservation' => $reservation->refresh()->load(['warehouse', 'product']),
                'movement' => $movement['movement'],
                'balance' => $movement['balance'],
            ];
        });

        return [
            'reservation' => $result['reservation']->toArray(),
            'movement' => $result['movement'],
            'balance' => $result['balance'],
        ];
    }

    public function releaseReservation(int $reservationId, array $payload, ?int $createdBy = null): array
    {
        $reservation = InventoryReservation::query()
            ->with(['warehouse', 'product'])
            ->whereKey($reservationId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($reservation->status === 'RELEASED') {
            return ['reservation' => $reservation->toArray()];
        }

        if ($reservation->status !== 'RESERVED') {
            throw new DomainException('Reservation is not active', 422);
        }

        $movementAt = isset($payload['movement_at'])
            ? Carbon::parse($payload['movement_at'])
            : now();

        $result = $this->inTransaction(function () use ($reservation, $payload, $createdBy, $movementAt): array {
            $movement = $this->postMovement([
                'warehouse_id' => $reservation->warehouse_id,
                'product_id' => $reservation->product_id,
                'movement_type' => 'RELEASE',
                'quantity' => (float) $reservation->quantity,
                'reference_type' => 'inventory_reservation_release',
                'reference_id' => $reservation->id,
                'notes' => $payload['notes'] ?? null,
                'metadata' => array_merge((array) ($reservation->metadata ?? []), (array) ($payload['metadata'] ?? []), [
                    'reservation_id' => $reservation->id,
                    'reservation_origin' => $reservation->reservation_origin,
                    'reservation_priority' => $reservation->priority,
                    'release_reason' => $payload['reason'],
                ]),
                'movement_at' => $movementAt->toDateTimeString(),
            ], $createdBy);

            $reservation->status = 'RELEASED';
            $reservation->released_at = $movementAt;
            $reservation->released_by = $createdBy;
            $reservation->release_reason = $payload['reason'];
            $reservation->save();

            return [
                'reservation' => $reservation->refresh()->load(['warehouse', 'product']),
                'movement' => $movement['movement'],
                'balance' => $movement['balance'],
            ];
        });

        return [
            'reservation' => $result['reservation']->toArray(),
            'movement' => $result['movement'],
            'balance' => $result['balance'],
        ];
    }

    public function releaseExpiredReservations(?int $createdBy = null): int
    {
        return (int) $this->inTransaction(function () use ($createdBy): int {
            $expiredReservations = InventoryReservation::query()
                ->where('status', 'RESERVED')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->orderBy('priority')
                ->orderBy('reserved_at')
                ->lockForUpdate()
                ->get();

            $releasedCount = 0;

            foreach ($expiredReservations as $reservation) {
                $this->releaseReservation((int) $reservation->id, [
                    'reason' => 'expired',
                    'movement_at' => now()->toDateTimeString(),
                    'metadata' => ['auto_released' => true],
                ], $createdBy);
                $releasedCount++;
            }

            return $releasedCount;
        });
    }

    public function transferStock(array $payload, ?int $createdBy = null): array
    {
        $sourceWarehouseId = (int) $payload['source_warehouse_id'];
        $targetWarehouseId = (int) $payload['target_warehouse_id'];

        if ($sourceWarehouseId === $targetWarehouseId) {
            throw new DomainException('Transfer requires different source and target warehouses', 422);
        }

        Warehouse::query()->findOrFail($sourceWarehouseId);
        Warehouse::query()->findOrFail($targetWarehouseId);
        Product::query()->findOrFail((int) $payload['product_id']);

        $quantity = round((float) $payload['quantity'], 6);

        if ($quantity <= 0) {
            throw new DomainException('Transfer quantity must be greater than zero', 422);
        }

        $movementAt = isset($payload['movement_at'])
            ? Carbon::parse($payload['movement_at'])
            : now();

        return $this->inTransaction(function () use ($payload, $createdBy, $sourceWarehouseId, $targetWarehouseId, $quantity, $movementAt) {
            $contextMetadata = array_merge(
                (array) ($payload['metadata'] ?? []),
                [
                    'transfer_source_warehouse_id' => $sourceWarehouseId,
                    'transfer_target_warehouse_id' => $targetWarehouseId,
                ]
            );

            $outgoing = $this->postMovement([
                'warehouse_id' => $sourceWarehouseId,
                'product_id' => $payload['product_id'],
                'movement_type' => 'TRANSFER_OUT',
                'quantity' => $quantity,
                'allocation_strategy' => $payload['allocation_strategy'] ?? null,
                'lot_number' => $payload['lot_number'] ?? null,
                'expires_at' => $payload['expires_at'] ?? null,
                'reference_type' => $payload['reference_type'] ?? 'stock_transfer',
                'reference_id' => $payload['reference_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'metadata' => $contextMetadata,
                'movement_at' => $movementAt->toDateTimeString(),
            ], $createdBy);

            $incoming = $this->postMovement([
                'warehouse_id' => $targetWarehouseId,
                'product_id' => $payload['product_id'],
                'movement_type' => 'TRANSFER_IN',
                'quantity' => $quantity,
                'allocation_strategy' => $payload['allocation_strategy'] ?? null,
                'lot_number' => $payload['lot_number'] ?? null,
                'expires_at' => $payload['expires_at'] ?? null,
                'reference_type' => $payload['reference_type'] ?? 'stock_transfer',
                'reference_id' => $payload['reference_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'metadata' => array_merge($contextMetadata, [
                    'transfer_out_movement_id' => $outgoing['movement']['id'] ?? null,
                ]),
                'movement_at' => $movementAt->toDateTimeString(),
            ], $createdBy);

            return [
                'source_movement' => $outgoing['movement'],
                'target_movement' => $incoming['movement'],
                'source_balance' => $outgoing['balance'],
                'target_balance' => $incoming['balance'],
            ];
        });
    }

    public function reverseMovement(array $payload, ?int $createdBy = null): array
    {
        $movement = StockLedgerMovement::query()
            ->with(['warehouse', 'product'])
            ->whereKey((int) $payload['movement_id'])
            ->lockForUpdate()
            ->firstOrFail();

        $movementAt = isset($payload['movement_at'])
            ? Carbon::parse($payload['movement_at'])
            : $movement->movement_at ?? now();

        if (in_array($movement->movement_type, ['TRANSFER_OUT', 'TRANSFER_IN'], true)) {
            $transferSourceWarehouseId = (int) data_get($movement->metadata, 'transfer_source_warehouse_id');
            $transferTargetWarehouseId = (int) data_get($movement->metadata, 'transfer_target_warehouse_id');

            if ($transferSourceWarehouseId <= 0 || $transferTargetWarehouseId <= 0) {
                throw new DomainException('Transfer movement is missing counter-warehouse metadata', 422);
            }

            $sourceWarehouseId = $movement->movement_type === 'TRANSFER_OUT'
                ? $transferTargetWarehouseId
                : (int) $movement->warehouse_id;
            $targetWarehouseId = $movement->movement_type === 'TRANSFER_OUT'
                ? (int) $movement->warehouse_id
                : $transferSourceWarehouseId;

            return $this->transferStock([
                'source_warehouse_id' => $sourceWarehouseId,
                'target_warehouse_id' => $targetWarehouseId,
                'product_id' => $movement->product_id,
                'quantity' => $movement->quantity,
                'allocation_strategy' => $movement->allocation_strategy,
                'lot_number' => $movement->lot_number,
                'expires_at' => $movement->expires_at?->toDateString(),
                'reference_type' => 'stock_movement_reversal',
                'reference_id' => $movement->id,
                'notes' => $payload['notes'] ?? $movement->notes,
                'metadata' => array_merge((array) ($movement->metadata ?? []), (array) ($payload['metadata'] ?? []), [
                    'reversal_of_movement_id' => $movement->id,
                    'reversal_reason' => $payload['reason'] ?? null,
                ]),
                'movement_at' => $movementAt->toDateTimeString(),
            ], $createdBy);
        }

        $reversalType = self::REVERSIBLE_MOVEMENT_TYPES[$movement->movement_type] ?? null;

        if ($reversalType === null) {
            throw new DomainException('Unsupported stock movement type reversal', 422);
        }

        $result = $this->postMovement([
            'warehouse_id' => $movement->warehouse_id,
            'product_id' => $movement->product_id,
            'movement_type' => $reversalType,
            'quantity' => $movement->quantity,
            'allocation_strategy' => $movement->allocation_strategy,
            'lot_number' => $movement->lot_number,
            'expires_at' => $movement->expires_at?->toDateString(),
            'reference_type' => 'stock_movement_reversal',
            'reference_id' => $movement->id,
            'notes' => $payload['notes'] ?? $movement->notes,
            'metadata' => array_merge((array) ($movement->metadata ?? []), (array) ($payload['metadata'] ?? []), [
                'reversal_of_movement_id' => $movement->id,
                'reversal_reason' => $payload['reason'] ?? null,
            ]),
            'movement_at' => $movementAt->toDateTimeString(),
        ], $createdBy);

        return [
            'reversal_movement' => $result['movement'],
            'balance' => $result['balance'],
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
