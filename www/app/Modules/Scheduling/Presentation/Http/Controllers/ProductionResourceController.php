<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Controllers;

use App\Modules\Scheduling\Application\Services\ProductionResourceService;
use App\Modules\Scheduling\Presentation\Http\Requests\StoreProductionResourceRequest;
use App\Modules\Scheduling\Presentation\Http\Requests\StoreWorkCenterHourRateRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionResourceController
{
    public function __construct(private readonly ProductionResourceService $service) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated($this->service->paginateResources((int) $request->integer('per_page', 15)), 'Production resources list');
    }

    public function store(StoreProductionResourceRequest $request): JsonResponse
    {
        return ApiResponse::success($this->service->createResource($request->validated()), 'Production resource created', 201);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->showResource($id), 'Production resource detail');
    }

    public function update(StoreProductionResourceRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->updateResource($id, $request->validated()), 'Production resource updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteResource($id);

        return ApiResponse::success(null, 'Production resource decommissioned');
    }

    public function rates(Request $request, int $workCenterId): JsonResponse
    {
        return ApiResponse::paginated($this->service->paginateRates($workCenterId, (int) $request->integer('per_page', 15)), 'Work center hour rates list');
    }

    public function storeRate(StoreWorkCenterHourRateRequest $request, int $workCenterId): JsonResponse
    {
        return ApiResponse::success($this->service->createRate($workCenterId, $request->validated(), $request->user()?->id), 'Work center hour rate created', 201);
    }

    public function effectiveRate(Request $request, int $workCenterId): JsonResponse
    {
        $date = (string) $request->query('reference_date', now()->toDateString());

        return ApiResponse::success($this->service->effectiveRate($workCenterId, $date), 'Effective work center hour rate');
    }
}
