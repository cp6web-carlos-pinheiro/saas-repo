<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Modules\Purchasing\Application\Services\PurchasingService;
use App\Modules\Purchasing\Presentation\Http\Requests\StoreMrpPurchaseRequisitionRequest;
use App\Modules\Purchasing\Presentation\Http\Requests\StorePurchaseRequisitionRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PurchaseRequisitionController
{
    public function __construct(private readonly PurchasingService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $filters = (array) $request->input('filter', []);

        return ApiResponse::paginated($this->service->paginateRequisitions($perPage, $filters), 'Purchase requisitions list');
    }

    public function store(StorePurchaseRequisitionRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->createRequisition($request->validated(), $request->user()?->id),
            'Purchase requisition created',
            201
        );
    }

    public function storeFromMrp(StoreMrpPurchaseRequisitionRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->createRequisitionFromMrp($request->validated(), $request->user()?->id),
            'Purchase requisition generated from MRP',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->showRequisition($id), 'Purchase requisition detail');
    }

    public function convertToPurchaseOrders(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->convertRequisitionToPurchaseOrders($id, $request->user()?->id),
            'Purchase orders generated from requisition',
            201
        );
    }
}
