<?php

declare(strict_types=1);

namespace App\Modules\MRP\Application\Actions;

use App\Modules\MRP\Application\DTO\MRPHealthDTO;
use App\Shared\Application\Actions\BaseAction;

final class GetMRPHealthAction extends BaseAction
{
    public function execute(mixed ...$payload): MRPHealthDTO
    {
        return new MRPHealthDTO(
            module: 'MRP',
            status: 'healthy',
            timestamp: now()->toIso8601String(),
        );
    }
}
