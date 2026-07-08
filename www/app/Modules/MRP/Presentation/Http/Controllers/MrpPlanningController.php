<?php

declare(strict_types=1);

namespace App\Modules\MRP\Presentation\Http\Controllers;

use App\Modules\MRP\Application\Services\MrpPlanningService;
use App\Modules\MRP\Presentation\Http\Requests\RunMrpPlanRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MrpPlanningController
{
    public function __construct(private readonly MrpPlanningService $service)
    {
    }

    public function run(RunMrpPlanRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->run($request->validated(), $request->user()?->id),
            'MRP plan generated'
        );
    }
}