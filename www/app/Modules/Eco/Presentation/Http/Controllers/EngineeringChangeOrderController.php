<?php

declare(strict_types=1);

namespace App\Modules\Eco\Presentation\Http\Controllers;

use App\Modules\Eco\Application\Services\EngineeringChangeOrderService;
use App\Modules\Eco\Presentation\Http\Requests\ApproveEngineeringChangeOrderRequest;
use App\Modules\Eco\Presentation\Http\Requests\RejectEngineeringChangeOrderRequest;
use App\Modules\Eco\Presentation\Http\Requests\StoreEngineeringChangeOrderRequest;
use App\Modules\Eco\Presentation\Http\Requests\UpdateEngineeringChangeOrderRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EngineeringChangeOrderController
{
    public function __construct(private readonly EngineeringChangeOrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        return ApiResponse::paginated($this->service->paginate($perPage), 'ECO list');
    }

    public function store(StoreEngineeringChangeOrderRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->createDraft($request->validated(), $request->user()?->id),
            'ECO draft created',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->show($id), 'ECO detail');
    }

    public function update(UpdateEngineeringChangeOrderRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->updateDraft($id, $request->validated()),
            'ECO draft updated'
        );
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->submit($id, $request->user()?->id),
            'ECO submitted'
        );
    }

    public function approve(ApproveEngineeringChangeOrderRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->approve($id, $request->validated(), $request->user()?->id),
            'ECO approved'
        );
    }

    public function reject(RejectEngineeringChangeOrderRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->reject($id, $request->validated()['reason'], $request->user()?->id),
            'ECO rejected'
        );
    }

    public function implement(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            $this->service->implement($id, $request->user()?->id),
            'ECO implemented'
        );
    }

    public function impact(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->analyzeImpact($id), 'ECO impact analysis');
    }
}
