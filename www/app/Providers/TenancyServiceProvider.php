<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, fn (): TenantContext => new TenantContext());
    }
}
