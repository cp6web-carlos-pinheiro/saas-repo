<?php

declare(strict_types=1);

namespace App\Modules\Routing\Presentation\Http\Controllers;

use App\Modules\Routing\Application\Services\RoutingStandardTimeService;
use App\Modules\Routing\Presentation\Http\Requests\StoreRoutingStandardTimeRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoutingStandardTimeController
{
    public function __construct(private readonly RoutingStandardTimeService $service)
    {
    }

    public function index(Request $request, int $routingOperationId): JsonResponse
    {
        return ApiResponse::paginated($this->service->paginate($routingOperationId, (int) $request->integer('per_page', 15)), 'Routing standard times list');
    }

    public function store(StoreRoutingStandardTimeRequest $request, int $routingOperationId): JsonResponse
    {
        return ApiResponse::success($this->service->create($routingOperationId, $request->validated(), $request->user()?->id), 'Routing standard time created', 201);
    }

    public function update(StoreRoutingStandardTimeRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->updateDraft($id, $request->validated()), 'Routing standard time updated');
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        return ApiResponse::success($this->service->approve($id, $data, $request->user()?->id), 'Routing standard time approved');
    }

    public function obsolete(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->obsolete($id), 'Routing standard time obsoleted');
    }

    public function effective(Request $request, int $routingOperationId): JsonResponse
    {
        $date = (string) $request->query('reference_date', now()->toDateString());

        return ApiResponse::success($this->service->effectiveForOperation($routingOperationId, $date)?->toArray(), 'Effective routing standard time');
    }

    public function calculate(Request $request, int $routingOperationId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference_date' => ['nullable', 'date'],
        ]);

        return ApiResponse::success($this->service->calculate($routingOperationId, (float) $data['quantity'], (string) ($data['reference_date'] ?? now()->toDateString())), 'Routing standard time calculated');
    }
}
