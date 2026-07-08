<?php

declare(strict_types=1);

namespace App\Shared\Application\Actions;

abstract class BaseAction
{
    abstract public function execute(mixed ...$payload): mixed;

    public function __invoke(mixed ...$payload): mixed
    {
        return $this->execute(...$payload);
    }
}
