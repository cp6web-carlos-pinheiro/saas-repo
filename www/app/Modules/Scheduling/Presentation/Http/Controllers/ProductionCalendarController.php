<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Controllers;

use App\Modules\Scheduling\Application\Services\ProductionCalendarService;
use App\Modules\Scheduling\Presentation\Http\Requests\BulkGenerateProductionCalendarRequest;
use App\Modules\Scheduling\Presentation\Http\Requests\UpsertProductionCalendarDayRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionCalendarController
{
    public function __construct(private readonly ProductionCalendarService $service)
    {
    }

    public function index(Request $request, int $workCenterId): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $items = $this->service
            ->listByRange($workCenterId, $validated['from_date'], $validated['to_date'])
            ->map(static fn ($row) => $row->toArray())
            ->values();

        return ApiResponse::success($items, 'Production calendar range');
    }

    public function upsert(UpsertProductionCalendarDayRequest $request, int $workCenterId): JsonResponse
    {
        $day = $this->service->upsertDay($workCenterId, $request->validated());

        return ApiResponse::success($day, 'Production calendar day upserted');
    }

    public function bulkGenerate(BulkGenerateProductionCalendarRequest $request, int $workCenterId): JsonResponse
    {
        $validated = $request->validated();
        $count = $this->service->bulkGenerate(
            $workCenterId,
            $validated['from_date'],
            $validated['to_date']
        );

        return ApiResponse::success([
            'work_center_id' => $workCenterId,
            'days_generated' => $count,
        ], 'Production calendar generated');
    }
}
