<?php
declare(strict_types=1);
namespace App\Modules\Production\Infrastructure\Persistence\Models;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class ProductionQualityRecord extends TenantModel
{
    protected $table = 'production_quality_records';
    protected $fillable = ['company_id','production_order_operation_id','record_type','status','quantity','cause_code','destination','operator_id','production_resource_id','notes','metadata'];
    protected $casts = ['quantity'=>'float','metadata'=>'array'];
    public function operation(): BelongsTo { return $this->belongsTo(ProductionOrderOperation::class, 'production_order_operation_id'); }
}
