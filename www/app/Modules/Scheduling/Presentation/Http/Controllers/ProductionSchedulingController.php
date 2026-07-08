<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Controllers;

use App\Modules\Scheduling\Application\Services\ProductionSchedulingService;
use App\Modules\Scheduling\Presentation\Http\Requests\RunProductionSchedulingRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ProductionSchedulingController
{
    public function __construct(private readonly ProductionSchedulingService $service)
    {
    }

    public function run(RunProductionSchedulingRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->schedule($request->validated()),
            'Production schedule generated'
        );
    }
}