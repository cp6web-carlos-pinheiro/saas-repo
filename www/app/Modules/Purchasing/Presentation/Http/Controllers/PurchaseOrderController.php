<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Modules\Purchasing\Application\Services\PurchasingService;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PurchaseOrderController
{
    public function __construct(private readonly PurchasingService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $filters = (array) $request->input('filter', []);

        return ApiResponse::paginated($this->service->paginatePurchaseOrders($perPage, $filters), 'Purchase orders list');
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->showPurchaseOrder($id), 'Purchase order detail');
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->approvePurchaseOrder($id, $request->user()?->id),
            'Purchase order approved'
        );
    }
}
