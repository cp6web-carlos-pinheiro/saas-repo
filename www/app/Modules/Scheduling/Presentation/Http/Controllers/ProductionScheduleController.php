<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Controllers;

use App\Modules\Scheduling\Application\Services\ProductionScheduleService;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionScheduleController
{
    public function __construct(private readonly ProductionScheduleService $service) {}
    public function index(Request $request): JsonResponse { return ApiResponse::paginated($this->service->paginate((int) $request->integer('per_page', 15)), 'Production schedules list'); }
    public function store(Request $request): JsonResponse { return ApiResponse::success($this->service->createDraft($request->all(), $request->user()?->id), 'Production schedule draft created', 201); }
    public function show(int $id): JsonResponse { return ApiResponse::success($this->service->show($id), 'Production schedule detail'); }
    public function publish(Request $request, int $id): JsonResponse { return ApiResponse::success($this->service->publish($id, $request->user()?->id, $request->input('reason')), 'Production schedule published'); }
    public function cancel(Request $request, int $id): JsonResponse { return ApiResponse::success($this->service->cancel($id, $request->user()?->id, $request->input('reason')), 'Production schedule cancelled'); }
    public function compare(int $id, int $otherId): JsonResponse { return ApiResponse::success($this->service->compare($id, $otherId), 'Production schedules compared'); }
}
