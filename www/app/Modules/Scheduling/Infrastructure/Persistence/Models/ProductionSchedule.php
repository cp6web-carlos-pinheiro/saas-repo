<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionSchedule extends TenantModel
{
    use HasFactory;

    protected $table = 'production_schedules';

    protected $fillable = [
        'company_id', 'plant_id', 'schedule_number', 'version_number', 'status', 'reference_date', 'mode',
        'direction', 'sequencing_rule', 'parameters', 'source_run_key', 'created_by', 'approved_by',
        'approved_at', 'published_by', 'published_at', 'change_reason',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'parameters' => 'array',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProductionScheduleLine::class, 'production_schedule_id');
    }
}
