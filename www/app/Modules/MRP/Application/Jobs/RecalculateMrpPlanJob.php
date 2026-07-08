<?php

declare(strict_types=1);

namespace App\Modules\MRP\Application\Jobs;

use App\Modules\MRP\Application\Services\MrpPlanningService;
use App\Shared\Application\Jobs\BaseJob;

final class RecalculateMrpPlanJob extends BaseJob
{
    public function __construct(
        private readonly array $payload,
        private readonly ?int $createdBy = null,
        private readonly ?string $idempotencyKey = null
    ) {
    }

    public function handle(): void
    {
        app(MrpPlanningService::class)->recalculateIncrementally(
            $this->payload,
            $this->createdBy,
            $this->idempotencyKey
        );
    }
}