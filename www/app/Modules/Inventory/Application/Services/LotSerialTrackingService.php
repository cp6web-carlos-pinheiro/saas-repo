<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryLot;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventorySerial;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LotSerialTrackingService extends BaseService
{
    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginateLots(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = InventoryLot::query()
            ->with(['warehouse', 'product', 'serials'])
            ->orderByDesc('id');

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', (int) $filters['warehouse_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        if (isset($filters['lot_number'])) {
            $query->where('lot_number', $filters['lot_number']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function paginateSerials(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = InventorySerial::query()
            ->with(['warehouse', 'product', 'lot'])
            ->orderByDesc('id');

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', (int) $filters['warehouse_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        if (isset($filters['serial_number'])) {
            $query->where('serial_number', $filters['serial_number']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function createLot(array $payload): array
    {
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        Product::query()->findOrFail((int) $payload['product_id']);

        $lot = $this->inTransaction(function () use ($payload) {
            return InventoryLot::query()->updateOrCreate(
                [
                    'warehouse_id' => $payload['warehouse_id'],
                    'product_id' => $payload['product_id'],
                    'lot_number' => $payload['lot_number'],
                ],
                [
                    'manufactured_at' => $payload['manufactured_at'] ?? null,
                    'expires_at' => $payload['expires_at'] ?? null,
                    'status' => $payload['status'] ?? 'ACTIVE',
                    'source_movement_id' => $payload['source_movement_id'] ?? null,
                    'metadata' => $payload['metadata'] ?? null,
                ]
            );
        });

        return $this->serializeLot($lot->refresh()->load(['warehouse', 'product', 'serials']));
    }

    public function createSerial(array $payload): array
    {
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        Product::query()->findOrFail((int) $payload['product_id']);

        $lot = null;

        if (isset($payload['inventory_lot_id'])) {
            $lot = InventoryLot::query()->findOrFail((int) $payload['inventory_lot_id']);
        }

        $serial = $this->inTransaction(function () use ($payload, $lot) {
            return InventorySerial::query()->updateOrCreate(
                [
                    'product_id' => $payload['product_id'],
                    'serial_number' => $payload['serial_number'],
                ],
                [
                    'warehouse_id' => $payload['warehouse_id'],
                    'inventory_lot_id' => $lot?->id,
                    'status' => $payload['status'] ?? 'ACTIVE',
                    'source_movement_id' => $payload['source_movement_id'] ?? null,
                    'metadata' => $payload['metadata'] ?? null,
                ]
            );
        });

        return $this->serializeSerial($serial->refresh()->load(['warehouse', 'product', 'lot']));
    }

    public function traceLot(string $lotNumber, int $warehouseId, int $productId): array
    {
        $lot = InventoryLot::query()
            ->with(['warehouse', 'product', 'serials'])
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('lot_number', $lotNumber)
            ->firstOrFail();

        $movements = StockLedgerMovement::query()
            ->with(['warehouse', 'product'])
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('lot_number', $lotNumber)
            ->orderBy('movement_at')
            ->orderBy('id')
            ->get();

        return [
            'lot' => $this->serializeLot($lot),
            'movements' => $movements->map(static fn (StockLedgerMovement $movement): array => [
                'id' => (int) $movement->id,
                'movement_type' => $movement->movement_type,
                'quantity' => (float) $movement->quantity,
                'movement_at' => $movement->movement_at?->toDateTimeString(),
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'source_bucket' => $movement->source_bucket,
                'target_bucket' => $movement->target_bucket,
            ])->values()->all(),
        ];
    }

    public function traceSerial(string $serialNumber, int $productId): array
    {
        $serial = InventorySerial::query()
            ->with(['warehouse', 'product', 'lot'])
            ->where('product_id', $productId)
            ->where('serial_number', $serialNumber)
            ->firstOrFail();

        return $this->serializeSerial($serial);
    }

    private function serializeLot(InventoryLot $lot): array
    {
        return [
            'id' => (int) $lot->id,
            'warehouse_id' => (int) $lot->warehouse_id,
            'product_id' => (int) $lot->product_id,
            'lot_number' => $lot->lot_number,
            'manufactured_at' => $lot->manufactured_at?->toDateString(),
            'expires_at' => $lot->expires_at?->toDateString(),
            'status' => $lot->status,
            'source_movement_id' => $lot->source_movement_id,
            'warehouse' => $lot->warehouse?->toArray(),
            'product' => $lot->product?->toArray(),
            'serials' => $lot->serials->map(fn (InventorySerial $serial): array => $this->serializeSerial($serial))->values()->all(),
        ];
    }

    private function serializeSerial(InventorySerial $serial): array
    {
        return [
            'id' => (int) $serial->id,
            'warehouse_id' => (int) $serial->warehouse_id,
            'product_id' => (int) $serial->product_id,
            'inventory_lot_id' => $serial->inventory_lot_id ? (int) $serial->inventory_lot_id : null,
            'serial_number' => $serial->serial_number,
            'status' => $serial->status,
            'source_movement_id' => $serial->source_movement_id,
            'warehouse' => $serial->warehouse?->toArray(),
            'product' => $serial->product?->toArray(),
            'lot' => $serial->lot?->toArray(),
        ];
    }
}
