<?php

declare(strict_types=1);

namespace App\Shared\Application\Cache;

interface CacheManager
{
    public function remember(string $key, int $ttl, callable $callback): mixed;

    public function put(string $key, mixed $value, int $ttl): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function forget(string $key): bool;
}
