<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseRequisitionLine extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_requisition_lines';

    protected $fillable = [
        'company_id',
        'purchase_requisition_id',
        'product_id',
        'warehouse_id',
        'supplier_id',
        'suggested_quantity',
        'requested_quantity',
        'moq_applied',
        'lead_time_days',
        'need_by_date',
        'order_date',
        'status',
        'source_requirement_key',
        'mrp_reference_date',
        'metadata',
    ];

    protected $casts = [
        'suggested_quantity' => 'float',
        'requested_quantity' => 'float',
        'moq_applied' => 'float',
        'lead_time_days' => 'integer',
        'need_by_date' => 'date',
        'order_date' => 'date',
        'mrp_reference_date' => 'date',
        'metadata' => 'array',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
