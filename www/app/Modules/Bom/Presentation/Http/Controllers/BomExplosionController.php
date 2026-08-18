<?php

declare(strict_types=1);

namespace App\Modules\Bom\Presentation\Http\Controllers;

use App\Modules\Bom\Application\Services\BomExplosionService;
use App\Modules\Bom\Presentation\Http\Requests\ExplodeBomRequest;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class BomExplosionController
{
    public function __construct(private readonly BomExplosionService $service) {}

    public function __invoke(ExplodeBomRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->explode(
            productId: (int) $validated['product_id'],
            referenceDate: (string) ($validated['reference_date'] ?? now()->toDateString()),
            versionNumber: isset($validated['version_number']) ? (int) $validated['version_number'] : null,
            maxDepth: (int) ($validated['max_depth'] ?? 100),
        );

        return ApiResponse::success($result, 'BOM exploded successfully');
    }
}
