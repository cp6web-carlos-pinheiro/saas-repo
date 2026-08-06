<?php

declare(strict_types=1);

namespace App\Modules\MRP\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MrpSuggestion extends TenantModel
{
    use HasFactory;

    protected $table = 'mrp_suggestions';

    protected $fillable = [
        'company_id', 'mrp_plan_run_id', 'suggestion_key', 'suggestion_type', 'status', 'product_id', 'warehouse_id',
        'original_quantity', 'approved_quantity', 'need_by_date', 'release_date', 'priority', 'bom_version_number',
        'routing_version_id', 'source_requirement_key', 'source_reference_type', 'source_reference_id',
        'production_order_id', 'purchase_requisition_id', 'decision_reason', 'original_payload', 'adjusted_payload',
        'decided_by', 'decided_at', 'converted_at',
    ];

    protected $casts = [
        'original_quantity' => 'float',
        'approved_quantity' => 'float',
        'need_by_date' => 'date',
        'release_date' => 'date',
        'original_payload' => 'array',
        'adjusted_payload' => 'array',
        'decided_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function planRun(): BelongsTo
    {
        return $this->belongsTo(MrpPlanRun::class, 'mrp_plan_run_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MrpSuggestionEvent::class, 'mrp_suggestion_id');
    }
}
