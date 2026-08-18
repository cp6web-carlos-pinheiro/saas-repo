<?php

declare(strict_types=1);

namespace App\Modules\Genealogy\Presentation\Http\Controllers;

use App\Modules\Genealogy\Application\Services\GenealogyService;
use App\Modules\Genealogy\Presentation\Http\Requests\TraceGenealogyRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GenealogyController
{
    public function __construct(private readonly GenealogyService $service) {}

    public function trace(TraceGenealogyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $direction = $validated['direction'] ?? 'forward';

        $result = $direction === 'backward'
            ? $this->service->traceBackward($validated['node_type'], (int) $validated['source_id'])
            : $this->service->traceForward($validated['node_type'], (int) $validated['source_id']);

        return ApiResponse::success($result, 'Genealogy trace');
    }

    public function linkLotProductionOutput(Request $request, int $productionOrderId): JsonResponse
    {
        $validated = $request->validate([
            'lot_number' => ['required', 'string', 'max:120'],
            'metadata' => ['nullable', 'array'],
        ]);

        return ApiResponse::success(
            $this->service->linkLotProductionOutput($productionOrderId, $validated['lot_number'], $validated),
            'Genealogy relation created',
            201
        );
    }

    public function linkMaterialConsumption(Request $request, int $consumptionId): JsonResponse
    {
        $validated = $request->validate([
            'metadata' => ['nullable', 'array'],
        ]);

        return ApiResponse::success(
            $this->service->linkMaterialConsumption($consumptionId),
            'Genealogy relation created',
            201
        );
    }

    public function linkLotSerial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lot_number' => ['required', 'string', 'max:120'],
            'serial_number' => ['required', 'string', 'max:120'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
        ]);

        return ApiResponse::success(
            $this->service->linkLotSerial(
                $validated['lot_number'],
                $validated['serial_number'],
                (int) $validated['product_id'],
                (int) $validated['warehouse_id']
            ),
            'Genealogy relation created',
            201
        );
    }
}
