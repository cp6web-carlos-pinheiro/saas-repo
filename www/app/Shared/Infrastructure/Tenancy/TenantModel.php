<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Model $model): void {
            $tenant = app(TenantContext::class);

            if ($tenant->hasTenant() && empty($model->company_id)) {
                $model->setAttribute('company_id', $tenant->companyId());
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenant = app(TenantContext::class);

            if (! $tenant->hasTenant()) {
                return;
            }

            $builder->where($builder->qualifyColumn('company_id'), $tenant->companyId());
        });
    }
}
