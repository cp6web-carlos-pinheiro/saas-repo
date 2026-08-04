<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WarehouseLocation extends TenantModel
{
    use HasFactory;

    protected $table = 'warehouse_locations';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
