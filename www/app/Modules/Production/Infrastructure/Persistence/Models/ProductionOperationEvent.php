<?php
declare(strict_types=1);
namespace App\Modules\Production\Infrastructure\Persistence\Models;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class ProductionOperationEvent extends TenantModel
{
    protected $table = 'production_operation_events';
    protected $fillable = ['company_id','production_order_operation_id','event_type','idempotency_key','occurred_at','operator_id','production_resource_id','reason_code','notes','metadata'];
    protected $casts = ['occurred_at'=>'datetime','metadata'=>'array'];
    public function operation(): BelongsTo { return $this->belongsTo(ProductionOrderOperation::class, 'production_order_operation_id'); }
}
