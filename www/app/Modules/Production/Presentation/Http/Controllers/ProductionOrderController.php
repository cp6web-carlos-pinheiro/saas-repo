<?php

declare(strict_types=1);

namespace App\Modules\Production\Presentation\Http\Controllers;

use App\Modules\Production\Application\Services\FreezeProductionOrderSnapshotService;
use App\Modules\Production\Application\Services\MaterialConsumptionService;
use App\Modules\Production\Application\Services\ProductionOrderService;
use App\Modules\Production\Application\Services\ProductionOrderOperationPlanningService;
use App\Modules\Production\Presentation\Http\Requests\StoreManualProductionOrderRequest;
use App\Modules\Production\Presentation\Http\Requests\StoreProductionOrderMaterialConsumptionRequest;
use App\Modules\Production\Presentation\Http\Requests\StoreMrpProductionOrderRequest;
use App\Modules\Production\Presentation\Http\Requests\StoreProductionOrderOutputRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionOrderController
{
    public function __construct(
        private readonly ProductionOrderService $service,
        private readonly FreezeProductionOrderSnapshotService $snapshotService,
        private readonly MaterialConsumptionService $consumptionService
        , private readonly ProductionOrderOperationPlanningService $operationPlanningService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        return ApiResponse::paginated($this->service->paginate($perPage), 'Production orders list');
    }

    public function storeManual(StoreManualProductionOrderRequest $request): JsonResponse
    {
        return ApiResponse::success($this->service->createManual($request->validated(), $request->user()?->id), 'Production order created', 201);
    }

    public function storeMrp(StoreMrpProductionOrderRequest $request): JsonResponse
    {
        return ApiResponse::success($this->service->createFromMrp($request->validated(), $request->user()?->id), 'Production order created from MRP', 201);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->show($id), 'Production order detail');
    }

    public function release(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->release($id, $request->user()?->id), 'Production order released');
    }

    public function partial(StoreProductionOrderOutputRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->partialProduction($id, $request->validated(), $request->user()?->id), 'Production order partial recorded');
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->complete($id, $request->user()?->id), 'Production order completed');
    }

    public function snapshot(int $id): JsonResponse
    {
        return ApiResponse::success($this->snapshotService->getSnapshot($id), 'Production order snapshot');
    }

    public function operations(int $id): JsonResponse
    {
        return ApiResponse::success($this->operationPlanningService->materialize($id), 'Production order planned operations');
    }

    public function materializeOperations(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->operationPlanningService->materialize($id, $request->boolean('force'), $request->user()?->id),
            'Production order operations materialized'
        );
    }

    public function consumptions(Request $request, int $id): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        return ApiResponse::paginated($this->consumptionService->paginate($id, $perPage), 'Material consumptions list');
    }

    public function recordConsumption(StoreProductionOrderMaterialConsumptionRequest $request, int $id): JsonResponse
    {
        $result = $this->consumptionService->record($id, $request->validated(), $request->user()?->id);

        return ApiResponse::success($result, 'Material consumption recorded', 201);
    }

    public function consumptionSummary(int $id): JsonResponse
    {
        return ApiResponse::success($this->consumptionService->aggregateByProduct($id), 'Material consumption summary');
    }

    public function reverseConsumption(Request $request, int $consumptionId): JsonResponse
    {
        return ApiResponse::success($this->consumptionService->reverse($consumptionId, (string) $request->input('reason'), $request->user()?->id), 'Material consumption reversed');
    }
}
