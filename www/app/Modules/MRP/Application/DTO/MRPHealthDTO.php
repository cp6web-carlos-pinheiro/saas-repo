<?php

declare(strict_types=1);

namespace App\Modules\MRP\Application\DTO;

use App\Shared\Application\DTO\BaseDTO;

final class MRPHealthDTO extends BaseDTO
{
    public function __construct(
        public readonly string $module,
        public readonly string $status,
        public readonly string $timestamp
    ) {}
}
