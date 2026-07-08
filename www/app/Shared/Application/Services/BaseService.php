<?php

declare(strict_types=1);

namespace App\Shared\Application\Services;

use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;

abstract class BaseService
{
    public function __construct(
        protected TransactionManager $transaction,
        protected CacheManager $cache,
        protected AppLogger $logger
    ) {
    }

    protected function inTransaction(callable $callback, int $attempts = 1): mixed
    {
        return $this->transaction->run($callback, $attempts);
    }

    protected function cacheRemember(string $key, int $ttl, callable $callback): mixed
    {
        return $this->cache->remember($key, $ttl, $callback);
    }
}
