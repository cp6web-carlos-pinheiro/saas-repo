<?php

declare(strict_types=1);

namespace App\Modules\Eco\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EngineeringChangeOrderLine extends TenantModel
{
    use HasFactory;

    protected $table = 'engineering_change_order_lines';

    protected $fillable = [
        'company_id',
        'engineering_change_order_id',
        'target_domain',
        'target_entity_id',
        'change_type',
        'from_version_number',
        'to_version_number',
        'effective_from',
        'effective_to',
        'impact_level',
        'change_summary',
        'metadata',
    ];

    protected $casts = [
        'target_entity_id' => 'integer',
        'from_version_number' => 'integer',
        'to_version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'metadata' => 'array',
    ];

    public function engineeringChangeOrder(): BelongsTo
    {
        return $this->belongsTo(EngineeringChangeOrder::class, 'engineering_change_order_id');
    }
}