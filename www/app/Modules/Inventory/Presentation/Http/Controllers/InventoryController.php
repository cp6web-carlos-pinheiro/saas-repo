<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Presentation\Http\Requests\AdjustInventoryBalanceRequest;
use App\Modules\Inventory\Presentation\Http\Requests\StoreStockLedgerMovementRequest;
use App\Modules\Inventory\Presentation\Http\Requests\UpsertInventoryBalanceRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InventoryController
{
    public function __construct(private readonly InventoryService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        $filters = array_filter([
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'product_id' => $request->integer('product_id') ?: null,
        ], static fn ($value) => $value !== null);

        $result = $this->service->paginate($filters, $perPage);

        return ApiResponse::paginated($result, 'Inventory balances list');
    }

    public function upsert(UpsertInventoryBalanceRequest $request): JsonResponse
    {
        $result = $this->service->upsertBalance($request->validated());

        return ApiResponse::success($result, 'Inventory balance upserted');
    }

    public function adjust(AdjustInventoryBalanceRequest $request): JsonResponse
    {
        $result = $this->service->adjustBuckets($request->validated());

        return ApiResponse::success($result, 'Inventory buckets adjusted');
    }

    public function ledger(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        $filters = array_filter([
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'product_id' => $request->integer('product_id') ?: null,
            'movement_type' => $request->string('movement_type')->toString() ?: null,
        ], static fn ($value) => $value !== null);

        $result = $this->service->paginateMovements($filters, $perPage);

        return ApiResponse::paginated($result, 'Stock ledger movements list');
    }

    public function storeMovement(StoreStockLedgerMovementRequest $request): JsonResponse
    {
        $result = $this->service->postMovement($request->validated(), $request->user()?->id);

        return ApiResponse::success($result, 'Stock ledger movement posted', 201);
    }
}
