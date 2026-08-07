<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Cache;

use App\Shared\Application\Cache\CacheManager;
use Illuminate\Support\Facades\Cache;

final class RedisCacheManager implements CacheManager
{
    private string $store;

    public function __construct()
    {
        $this->store = (string) config('architecture.cache.store', 'redis');
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::store($this->store)->remember($key, $ttl, $callback);
    }

    public function put(string $key, mixed $value, int $ttl): bool
    {
        return Cache::store($this->store)->put($key, $value, $ttl);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::store($this->store)->get($key, $default);
    }

    public function forget(string $key): bool
    {
        return Cache::store($this->store)->forget($key);
    }
}
