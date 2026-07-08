<?php

declare(strict_types=1);

namespace App\Shared\Application\Transactions;

interface TransactionManager
{
    public function run(callable $callback, int $attempts = 1): mixed;
}
