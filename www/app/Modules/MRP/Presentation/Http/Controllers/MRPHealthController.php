<?php

declare(strict_types=1);

namespace App\Modules\MRP\Presentation\Http\Controllers;

use App\Modules\MRP\Application\Actions\GetMRPHealthAction;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MRPHealthController
{
    public function __invoke(GetMRPHealthAction $action): JsonResponse
    {
        $result = $action->execute();

        return ApiResponse::success($result->toArray(), 'MRP module health check');
    }
}
