<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Modules\Purchasing\Application\Services\PurchasingService;
use App\Modules\Purchasing\Presentation\Http\Requests\StoreSupplierRequest;
use App\Modules\Purchasing\Presentation\Http\Requests\UpdateSupplierRequest;
use App\Modules\Purchasing\Presentation\Http\Requests\UpsertSupplierProductRuleRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SupplierController
{
    public function __construct(private readonly PurchasingService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $filters = (array) $request->input('filter', []);

        return ApiResponse::paginated($this->service->paginateSuppliers($perPage, $filters), 'Suppliers list');
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        return ApiResponse::success($this->service->createSupplier($request->validated()), 'Supplier created', 201);
    }

    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->updateSupplier($id, $request->validated()), 'Supplier updated');
    }

    public function upsertRule(UpsertSupplierProductRuleRequest $request, int $supplierId, int $productId): JsonResponse
    {
        return ApiResponse::success(
            $this->service->upsertSupplierProductRule($supplierId, $productId, $request->validated()),
            'Supplier product rule upserted'
        );
    }

    public function rules(int $supplierId): JsonResponse
    {
        return ApiResponse::success($this->service->supplierProductRules($supplierId), 'Supplier product rules');
    }
}
