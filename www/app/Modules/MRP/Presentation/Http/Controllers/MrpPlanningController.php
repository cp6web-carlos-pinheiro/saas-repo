<?php

declare(strict_types=1);

namespace App\Modules\MRP\Presentation\Http\Controllers;

use App\Modules\MRP\Application\Services\MrpPlanningService;
use App\Modules\MRP\Application\Services\MrpSuggestionWorkflowService;
use App\Modules\MRP\Presentation\Http\Requests\RunMrpPlanRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MrpPlanningController
{
    public function __construct(private readonly MrpPlanningService $service, private readonly MrpSuggestionWorkflowService $workflow) {}

    public function run(RunMrpPlanRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->workflow->persist($request->validated(), $this->service->run($request->validated(), $request->user()?->id), $request->user()?->id),
            'MRP plan generated'
        );
    }
}
