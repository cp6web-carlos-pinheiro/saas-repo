<?php

declare(strict_types=1);

namespace App\Modules\MRP\Presentation\Http\Controllers;

use App\Modules\MRP\Application\Jobs\RecalculateMrpPlanJob;
use App\Modules\MRP\Application\Services\MrpPlanningService;
use App\Modules\MRP\Presentation\Http\Requests\RecalculateMrpPlanRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MrpRecalculationController
{
    public function __construct(private readonly MrpPlanningService $service)
    {
    }

    public function run(RecalculateMrpPlanRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (! empty($payload['async'])) {
            RecalculateMrpPlanJob::dispatch($payload, $request->user()?->id, $payload['idempotency_key'] ?? null)
                ->onQueue('mrp');

            return ApiResponse::success([
                'queued' => true,
                'idempotency_key' => $payload['idempotency_key'] ?? null,
            ], 'MRP recalculation queued', 202);
        }

        return ApiResponse::success(
            $this->service->recalculateIncrementally($payload, $request->user()?->id, $payload['idempotency_key'] ?? null),
            'MRP recalculated'
        );
    }
}