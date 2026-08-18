<?php

declare(strict_types=1);

namespace App\Modules\Routing\Presentation\Http\Controllers;

use App\Modules\Routing\Application\Services\RoutingService;
use App\Modules\Routing\Presentation\Http\Requests\ApproveRoutingVersionRequest;
use App\Modules\Routing\Presentation\Http\Requests\ObsoleteRoutingVersionRequest;
use App\Modules\Routing\Presentation\Http\Requests\StoreRoutingOperationRequest;
use App\Modules\Routing\Presentation\Http\Requests\StoreRoutingVersionRequest;
use App\Modules\Routing\Presentation\Http\Requests\UpdateRoutingOperationRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoutingController
{
    public function __construct(private readonly RoutingService $service) {}

    public function indexVersions(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        return ApiResponse::paginated($this->service->paginateVersions($perPage), 'Routing versions list');
    }

    public function storeVersion(StoreRoutingVersionRequest $request): JsonResponse
    {
        $entity = $this->service->createVersion($request->validated());

        return ApiResponse::success($entity, 'Routing version created', 201);
    }

    public function showVersion(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->showVersion($id), 'Routing version detail');
    }

    public function approve(ApproveRoutingVersionRequest $request, int $id): JsonResponse
    {
        $userId = $request->user()?->id;

        $snapshot = $this->service->approveVersion(
            $id,
            $request->validated(),
            $userId
        );

        return ApiResponse::success($snapshot, 'Routing version approved');
    }

    public function obsolete(ObsoleteRoutingVersionRequest $request, int $id): JsonResponse
    {
        $entity = $this->service->markObsolete($id);

        return ApiResponse::success($entity, 'Routing version marked obsolete');
    }

    public function storeOperation(StoreRoutingOperationRequest $request, int $routingVersionId): JsonResponse
    {
        $entity = $this->service->addOperation($routingVersionId, $request->validated());

        return ApiResponse::success($entity, 'Routing operation created', 201);
    }

    public function updateOperation(UpdateRoutingOperationRequest $request, int $routingVersionId, int $operationId): JsonResponse
    {
        $entity = $this->service->updateOperation($routingVersionId, $operationId, $request->validated());

        return ApiResponse::success($entity, 'Routing operation updated');
    }

    public function destroyOperation(int $routingVersionId, int $operationId): JsonResponse
    {
        $this->service->deleteOperation($routingVersionId, $operationId);

        return ApiResponse::success(null, 'Routing operation deleted');
    }
}
