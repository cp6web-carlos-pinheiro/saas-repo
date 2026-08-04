<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Plant extends TenantModel
{
    use HasFactory;

    protected $table = 'plants';

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'code',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
