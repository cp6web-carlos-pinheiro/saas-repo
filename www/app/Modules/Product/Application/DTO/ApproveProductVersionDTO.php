<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\DTO;

use App\Shared\Application\DTO\BaseDTO;

final class ApproveProductVersionDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $effective_from = null,
        public readonly ?string $effective_to = null,
        public readonly ?string $change_summary = null,
    ) {
    }
}
