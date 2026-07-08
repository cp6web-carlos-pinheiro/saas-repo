<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\DTO;

use App\Shared\Application\DTO\BaseDTO;

final class CreateProductDTO extends BaseDTO
{
    public function __construct(
        public readonly string $sku,
        public readonly string $description,
        public readonly string $product_type,
        public readonly string $uom,
        public readonly int $safety_stock,
        public readonly int $lead_time_days,
        public readonly bool $lot_control,
        public readonly bool $serial_control,
        public readonly bool $is_active = true,
    ) {
    }
}
