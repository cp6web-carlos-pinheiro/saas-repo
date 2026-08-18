<?php

declare(strict_types=1);

namespace App\Modules\Product\Presentation\Http\Controllers;

use App\Modules\Product\Application\DTO\CreateProductDTO;
use App\Modules\Product\Application\DTO\UpdateProductDTO;
use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Presentation\Http\Requests\StoreBulkProductsRequest;
use App\Modules\Product\Presentation\Http\Requests\StoreProductRequest;
use App\Modules\Product\Presentation\Http\Requests\UpdateBulkProductsRequest;
use App\Modules\Product\Presentation\Http\Requests\UpdateProductRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductController
{
    public function __construct(private readonly ProductService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $filters = (array) $request->input('filter', []);
        $sortBy = $request->query('sort_by');
        $sortDirection = (string) $request->query('sort_direction', 'asc');
        $result = $this->service->paginate($perPage, $filters, is_string($sortBy) ? $sortBy : null, $sortDirection);

        return ApiResponse::paginated($result, 'Products list');
    }

    public function bulkStore(StoreBulkProductsRequest $request): JsonResponse
    {
        $result = $this->service->bulkCreate($request->validated()['items']);

        return ApiResponse::success($result, 'Products bulk created', 201);
    }

    public function bulkUpdate(UpdateBulkProductsRequest $request): JsonResponse
    {
        $result = $this->service->bulkUpdate($request->validated()['items']);

        return ApiResponse::success($result, 'Products bulk updated');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = CreateProductDTO::fromArray($request->validated());
        $product = $this->service->create($dto);

        return ApiResponse::success($product, 'Product created', 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->service->find($id);

        return ApiResponse::success($product, 'Product detail');
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $dto = UpdateProductDTO::fromArray($request->validated());
        $product = $this->service->update($id, $dto);

        return ApiResponse::success($product, 'Product updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return ApiResponse::success(null, 'Product deleted');
    }
}
