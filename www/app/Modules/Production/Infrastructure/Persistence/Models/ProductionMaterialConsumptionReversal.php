<?php
declare(strict_types=1);
namespace App\Modules\Production\Infrastructure\Persistence\Models;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class ProductionMaterialConsumptionReversal extends TenantModel
{
    protected $table = 'production_material_consumption_reversals';
    protected $fillable = ['company_id','production_order_material_consumption_id','original_ledger_movement_id','reversal_ledger_movement_id','quantity','reason','created_by'];
    protected $casts = ['quantity'=>'float'];
    public function consumption(): BelongsTo { return $this->belongsTo(ProductionOrderMaterialConsumption::class, 'production_order_material_consumption_id'); }
}
