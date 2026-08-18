<?php

declare(strict_types=1);

namespace App\Modules\Analysis\Presentation\Http\Controllers;

use App\Modules\Analysis\Application\Services\ManufacturingAnalyticsService;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ManufacturingAnalyticsController
{
    public function __construct(private readonly ManufacturingAnalyticsService $service) {}

    public function overview(Request $r): JsonResponse
    {
        return ApiResponse::success($this->service->overview($r->only(['date_from', 'date_to', 'product_id', 'production_order_id', 'work_center_id', 'production_resource_id', 'operator_id'])), 'Manufacturing analytics overview');
    }

    public function efficiency(Request $r): JsonResponse
    {
        return ApiResponse::success($this->service->efficiency($r->only(['date_from', 'date_to', 'product_id', 'production_order_id', 'work_center_id', 'production_resource_id', 'operator_id'])), 'Manufacturing efficiency analysis');
    }

    public function oee(Request $r): JsonResponse
    {
        return ApiResponse::success($this->service->oee($r->only(['date_from', 'date_to', 'product_id', 'production_order_id', 'work_center_id', 'production_resource_id'])), 'Manufacturing OEE analysis');
    }

    public function standardTimes(Request $r): JsonResponse
    {
        return ApiResponse::success($this->service->standardTimeEvidence($r->only(['date_from', 'date_to', 'product_id', 'work_center_id', 'production_resource_id']), (int) $r->integer('minimum_sample', 5)), 'Standard time evidence');
    }

    public function recommend(Request $r): JsonResponse
    {
        return ApiResponse::success($this->service->createRecommendation($r->only(['date_from', 'date_to', 'product_id', 'work_center_id', 'production_resource_id']), (int) $r->integer('minimum_sample', 5), $r->user()?->id), 'Standard time recommendations created', 201);
    }

    public function decide(Request $r, int $id): JsonResponse
    {
        return ApiResponse::success($this->service->decideRecommendation($id, (string) $r->input('status'), $r->input('reason'), $r->user()?->id), 'Standard time recommendation decided');
    }
}
