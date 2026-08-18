<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Bom\Domain\Repositories\BomExplosionRepository;
use App\Modules\Bom\Infrastructure\Persistence\Repositories\MySqlBomExplosionRepository;
use App\Modules\Product\Domain\Repositories\ProductRepository;
use App\Modules\Product\Domain\Repositories\ProductVersionRepository;
use App\Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductVersionRepository;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Cache\RedisCacheManager;
use App\Shared\Infrastructure\Transactions\DbTransactionManager;
use Illuminate\Support\ServiceProvider;

class ArchitectureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TransactionManager::class, DbTransactionManager::class);
        $this->app->singleton(CacheManager::class, RedisCacheManager::class);
        $this->app->bind(BomExplosionRepository::class, MySqlBomExplosionRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(ProductVersionRepository::class, EloquentProductVersionRepository::class);
    }

    public function boot(): void {}
}
