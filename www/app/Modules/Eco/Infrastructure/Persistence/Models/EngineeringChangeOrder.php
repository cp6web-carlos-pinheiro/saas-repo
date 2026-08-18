<?php

declare(strict_types=1);

namespace App\Modules\Eco\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EngineeringChangeOrder extends TenantModel
{
    use HasFactory;

    protected $table = 'engineering_change_orders';

    protected $fillable = [
        'company_id',
        'eco_number',
        'title',
        'description',
        'status',
        'effective_from',
        'effective_to',
        'requested_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'implemented_by',
        'implemented_at',
        'impact_summary',
        'metadata',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'implemented_at' => 'datetime',
        'impact_summary' => 'array',
        'metadata' => 'array',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(EngineeringChangeOrderLine::class, 'engineering_change_order_id')
            ->orderBy('id');
    }
}
