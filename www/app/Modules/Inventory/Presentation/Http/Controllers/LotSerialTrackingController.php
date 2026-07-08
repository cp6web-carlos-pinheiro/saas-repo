<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Modules\Inventory\Application\Services\LotSerialTrackingService;
use App\Modules\Inventory\Presentation\Http\Requests\StoreInventoryLotRequest;
use App\Modules\Inventory\Presentation\Http\Requests\StoreInventorySerialRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LotSerialTrackingController
{
    public function __construct(private readonly \App\Modules\Inventory\Application\Services\LotSerialTrackingService $service)
    {
    }

    public function lots(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        $filters = array_filter([
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'product_id' => $request->integer('product_id') ?: null,
            'lot_number' => $request->string('lot_number')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ], static fn ($value) => $value !== null);

        return ApiResponse::paginated($this->service->paginateLots($filters, $perPage), 'Inventory lots list');
    }

    public function storeLot(\App\Modules\Inventory\Presentation\Http\Requests\StoreInventoryLotRequest $request): JsonResponse
    {
        return ApiResponse::success($this->service->createLot($request->validated()), 'Inventory lot created', 201);
    }

    public function traceLot(Request $request, string $lotNumber): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        return ApiResponse::success(
            $this->service->traceLot($lotNumber, (int) $validated['warehouse_id'], (int) $validated['product_id']),
            'Inventory lot trace'
        );
    }

    public function serials(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        $filters = array_filter([
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'product_id' => $request->integer('product_id') ?: null,
            'serial_number' => $request->string('serial_number')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ], static fn ($value) => $value !== null);

        return ApiResponse::paginated($this->service->paginateSerials($filters, $perPage), 'Inventory serials list');
    }

    public function storeSerial(\App\Modules\Inventory\Presentation\Http\Requests\StoreInventorySerialRequest $request): JsonResponse
    {
        return ApiResponse::success($this->service->createSerial($request->validated()), 'Inventory serial created', 201);
    }

    public function traceSerial(Request $request, string $serialNumber): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        return ApiResponse::success(
            $this->service->traceSerial($serialNumber, (int) $validated['product_id']),
            'Inventory serial trace'
        );
    }
}
