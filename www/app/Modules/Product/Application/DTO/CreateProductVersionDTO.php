<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\DTO;

use App\Shared\Application\DTO\BaseDTO;

final class CreateProductVersionDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $effective_from,
        public readonly ?string $effective_to,
        public readonly string $compatibility_rule,
        public readonly ?string $change_summary,
        public readonly array $payload,
    ) {}
}
