<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class Unit extends TenantModel
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Model $model): void {
            $tenant = app(TenantContext::class);

            if ($tenant->hasTenant() && empty($model->company_id)) {
                $model->setAttribute('company_id', $tenant->companyId());
            }
        });

        static::addGlobalScope('tenant', static function (Builder $builder): void {
            $tenant = app(TenantContext::class);

            if (! $tenant->hasTenant()) {
                return;
            }

            $builder->where(static function (Builder $query) use ($builder, $tenant): void {
                $query->where($builder->qualifyColumn('company_id'), $tenant->companyId())
                    ->orWhereNull($builder->qualifyColumn('company_id'));
            });
        });
    }
}
