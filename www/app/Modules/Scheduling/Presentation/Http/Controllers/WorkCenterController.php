<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Controllers;

use App\Modules\Scheduling\Application\Services\WorkCenterService;
use App\Modules\Scheduling\Presentation\Http\Requests\StoreWorkCenterRequest;
use App\Modules\Scheduling\Presentation\Http\Requests\StoreWorkCenterShiftRequest;
use App\Modules\Scheduling\Presentation\Http\Requests\UpdateWorkCenterRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WorkCenterController
{
    public function __construct(private readonly WorkCenterService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        return ApiResponse::paginated($this->service->paginate($perPage), 'Work centers list');
    }

    public function store(StoreWorkCenterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $workCenter = $this->service->create($data);

        return ApiResponse::success($workCenter, 'Work center created', 201);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->show($id), 'Work center detail');
    }

    public function update(UpdateWorkCenterRequest $request, int $id): JsonResponse
    {
        $workCenter = $this->service->update($id, $request->validated());

        return ApiResponse::success($workCenter, 'Work center updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return ApiResponse::success(null, 'Work center deleted');
    }

    public function addShift(StoreWorkCenterShiftRequest $request, int $id): JsonResponse
    {
        $shift = $this->service->addShift($id, $request->validated());

        return ApiResponse::success($shift, 'Shift added to work center', 201);
    }
}
