<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Transactions;

use App\Shared\Application\Transactions\TransactionManager;
use Illuminate\Support\Facades\DB;

final class DbTransactionManager implements TransactionManager
{
    public function run(callable $callback, int $attempts = 1): mixed
    {
        return DB::transaction($callback, $attempts);
    }
}
