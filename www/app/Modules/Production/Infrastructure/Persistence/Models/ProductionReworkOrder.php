<?php
declare(strict_types=1);
namespace App\Modules\Production\Infrastructure\Persistence\Models;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class ProductionReworkOrder extends TenantModel
{
    protected $table = 'production_rework_orders';
    protected $fillable = ['company_id','source_production_order_operation_id','rework_production_order_operation_id','quantity','status','reason_code','notes','created_by','completed_at'];
    protected $casts = ['quantity'=>'float','completed_at'=>'datetime'];
    public function sourceOperation(): BelongsTo { return $this->belongsTo(ProductionOrderOperation::class, 'source_production_order_operation_id'); }
    public function reworkOperation(): BelongsTo { return $this->belongsTo(ProductionOrderOperation::class, 'rework_production_order_operation_id'); }
}
