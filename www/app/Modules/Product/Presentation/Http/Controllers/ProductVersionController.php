<?php

declare(strict_types=1);

namespace App\Modules\Product\Presentation\Http\Controllers;

use App\Modules\Product\Application\DTO\ApproveProductVersionDTO;
use App\Modules\Product\Application\DTO\CreateProductVersionDTO;
use App\Modules\Product\Application\DTO\UpdateProductVersionDTO;
use App\Modules\Product\Application\Services\ProductVersionService;
use App\Modules\Product\Presentation\Http\Requests\ApproveProductVersionRequest;
use App\Modules\Product\Presentation\Http\Requests\StoreProductVersionRequest;
use App\Modules\Product\Presentation\Http\Requests\UpdateProductVersionRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductVersionController
{
    public function __construct(private readonly ProductVersionService $service)
    {
    }

    public function store(StoreProductVersionRequest $request, int $productId): JsonResponse
    {
        $dto = CreateProductVersionDTO::fromArray($request->validated());
        $version = $this->service->createDraft($productId, $dto, $request->user()?->id);

        return ApiResponse::success($version, 'Product version draft created', 201);
    }

    public function show(int $productId, int $versionId): JsonResponse
    {
        $history = $this->service->history($productId);
        $version = $history->firstWhere('id', $versionId);

        if (! $version) {
            return ApiResponse::error('Product version not found', 404);
        }

        return ApiResponse::success($version->toArray(), 'Product version detail');
    }

    public function update(UpdateProductVersionRequest $request, int $productId, int $versionId): JsonResponse
    {
        $dto = UpdateProductVersionDTO::fromArray($request->validated());
        $version = $this->service->updateDraft($productId, $versionId, $dto);

        return ApiResponse::success($version, 'Product version draft updated');
    }

    public function approve(ApproveProductVersionRequest $request, int $productId, int $versionId): JsonResponse
    {
        $dto = ApproveProductVersionDTO::fromArray($request->validated());
        $version = $this->service->approve($productId, $versionId, $dto, $request->user()?->id);

        return ApiResponse::success($version, 'Product version approved');
    }

    public function obsolete(int $productId, int $versionId): JsonResponse
    {
        $version = $this->service->markObsolete($productId, $versionId);

        return ApiResponse::success($version, 'Product version marked obsolete');
    }

    public function history(int $productId): JsonResponse
    {
        $items = $this->service->history($productId)->map(static fn ($row) => $row->toArray())->values();

        return ApiResponse::success($items, 'Product version history');
    }

    public function effective(Request $request, int $productId): JsonResponse
    {
        $referenceDate = (string) $request->query('date', now()->toDateString());
        $version = $this->service->findEffectiveVersion($productId, $referenceDate);

        if (! $version) {
            return ApiResponse::error('No effective version found for provided date', 404);
        }

        return ApiResponse::success($version, 'Effective product version');
    }
}
