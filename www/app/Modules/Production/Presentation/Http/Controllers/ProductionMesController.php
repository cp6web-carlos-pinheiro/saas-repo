<?php

declare(strict_types=1);

namespace App\Modules\Production\Presentation\Http\Controllers;

use App\Modules\Production\Application\Services\ProductionOperationExecutionService;
use App\Modules\Production\Application\Services\ProductionQualityService;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionMesController
{
    public function __construct(private readonly ProductionOperationExecutionService $execution, private readonly ProductionQualityService $quality) {}

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->execution->show($id), 'MES operation detail');
    }

    public function start(Request $r, int $id): JsonResponse
    {
        return $this->command($r, $id, 'START');
    }

    public function pause(Request $r, int $id): JsonResponse
    {
        return $this->command($r, $id, 'PAUSE');
    }

    public function resume(Request $r, int $id): JsonResponse
    {
        return $this->command($r, $id, 'RESUME');
    }

    public function stop(Request $r, int $id): JsonResponse
    {
        return $this->command($r, $id, 'STOP');
    }

    public function complete(Request $r, int $id): JsonResponse
    {
        return $this->command($r, $id, 'COMPLETE');
    }

    public function cancel(Request $r, int $id): JsonResponse
    {
        return $this->command($r, $id, 'CANCEL');
    }

    public function output(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->execution->reportOutput($id, $r->all(), $r->user()?->id), 'MES operation output recorded', 201);
    }

    public function quality(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->quality->record($id, $r->all(), $r->user()?->id), 'Quality record created', 201);
    }

    public function rework(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->quality->createRework($id, $r->all(), $r->user()?->id), 'Rework order created', 201);
    }

    public function completeRework(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->quality->completeRework($id, $r->user()?->id), 'Rework completed');
    }

    private function command(Request $r, int $id, string $event): JsonResponse
    {
        return ApiResponse::success($this->execution->command($id, $event, $r->all(), $r->user()?->id), 'MES operation '.$event.' recorded');
    }
}
