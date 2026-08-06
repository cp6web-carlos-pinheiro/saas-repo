<?php
declare(strict_types=1);
namespace App\Modules\Production\Infrastructure\Persistence\Models;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class ProductionOperationOutput extends TenantModel
{
    protected $table = 'production_operation_outputs';
    protected $fillable = ['company_id','production_order_operation_id','quantity_good','quantity_scrapped','quantity_rework','lot_number','inspection_status','scrap_cause_code','destination','operator_id','production_resource_id','reported_at','notes','metadata'];
    protected $casts = ['quantity_good'=>'float','quantity_scrapped'=>'float','quantity_rework'=>'float','reported_at'=>'datetime','metadata'=>'array'];
    public function operation(): BelongsTo { return $this->belongsTo(ProductionOrderOperation::class, 'production_order_operation_id'); }
}
