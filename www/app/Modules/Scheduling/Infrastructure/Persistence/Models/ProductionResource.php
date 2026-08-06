<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionResource extends TenantModel
{
    use HasFactory;

    protected $table = 'production_resources';

    protected $fillable = [
        'company_id', 'plant_id', 'work_center_id', 'code', 'name', 'resource_type',
        'status', 'capacity_per_day', 'efficiency_factor', 'effective_from', 'effective_to', 'metadata',
    ];

    protected $casts = [
        'capacity_per_day' => 'float',
        'efficiency_factor' => 'float',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'metadata' => 'array',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
