<?php

declare(strict_types=1);

namespace App\Shared\Application\DTO;

abstract class BaseDTO
{
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
