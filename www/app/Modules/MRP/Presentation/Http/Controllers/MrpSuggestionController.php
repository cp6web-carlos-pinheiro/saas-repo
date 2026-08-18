<?php

declare(strict_types=1);

namespace App\Modules\MRP\Presentation\Http\Controllers;

use App\Modules\MRP\Application\Services\MrpSuggestionWorkflowService;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MrpSuggestionController
{
    public function __construct(private readonly MrpSuggestionWorkflowService $service) {}

    public function runs(Request $r): JsonResponse
    {
        return ApiResponse::paginated($this->service->runs((int) $r->integer('per_page', 15)), 'MRP runs list');
    }

    public function index(Request $r): JsonResponse
    {
        return ApiResponse::paginated($this->service->suggestions((int) $r->integer('per_page', 15), $r->only(['status', 'suggestion_type'])), 'MRP suggestions list');
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->show($id), 'MRP suggestion detail');
    }

    public function approve(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->decide($id, 'APPROVED', $r->all(), $r->user()?->id), 'MRP suggestion approved');
    }

    public function reject(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->decide($id, 'REJECTED', $r->all(), $r->user()?->id), 'MRP suggestion rejected');
    }

    public function convert(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->convert($id, $r->user()?->id), 'MRP suggestion converted');
    }
}
