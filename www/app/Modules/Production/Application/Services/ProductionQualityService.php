<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionQualityRecord;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionReworkOrder;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;

final class ProductionQualityService extends BaseService
{
    public function __construct(TransactionManager $transaction, CacheManager $cache, AppLogger $logger)
    { parent::__construct($transaction, $cache, $logger); }

    public function record(int $operationId, array $payload, ?int $userId = null): array
    {
        $operation = ProductionOrderOperation::query()->findOrFail($operationId);
        $quantity = round((float) $payload['quantity'], 6);
        if ($quantity <= 0) throw new DomainException('Quality quantity must be positive', 422);
        if ($quantity > (float) $operation->quantity_processed - (float) $operation->quantity_scrapped - (float) $operation->quantity_rework + 0.000001 && empty($payload['allow_excess'])) throw new DomainException('Quality quantity exceeds available processed quantity', 422);
        $record = ProductionQualityRecord::query()->create(['company_id'=>$operation->company_id,'production_order_operation_id'=>$operation->id,'record_type'=>$payload['record_type'] ?? 'NON_CONFORMITY','status'=>$payload['status'] ?? 'PENDING','quantity'=>$quantity,'cause_code'=>$payload['cause_code'] ?? null,'destination'=>$payload['destination'] ?? null,'operator_id'=>$payload['operator_id'] ?? $userId,'production_resource_id'=>$operation->actual_production_resource_id ?: $operation->production_resource_id,'notes'=>$payload['notes'] ?? null,'metadata'=>$payload['metadata'] ?? null]);
        return $record->toArray();
    }

    public function createRework(int $operationId, array $payload, ?int $userId = null): array
    {
        $operation = ProductionOrderOperation::query()->findOrFail($operationId);
        $quantity = round((float) $payload['quantity'], 6);
        if ($quantity <= 0 || $quantity > (float) $operation->quantity_rework + 0.000001 && empty($payload['allow_excess'])) throw new DomainException('Rework quantity is invalid for the operation', 422);
        $rework = ProductionReworkOrder::query()->create(['company_id'=>$operation->company_id,'source_production_order_operation_id'=>$operation->id,'rework_production_order_operation_id'=>$payload['rework_production_order_operation_id'] ?? null,'quantity'=>$quantity,'status'=>'OPEN','reason_code'=>$payload['reason_code'] ?? null,'notes'=>$payload['notes'] ?? null,'created_by'=>$userId]);
        return $rework->load(['sourceOperation','reworkOperation'])->toArray();
    }

    public function completeRework(int $id, ?int $userId = null): array
    {
        $rework = ProductionReworkOrder::query()->findOrFail($id);
        if ($rework->status === 'COMPLETED') return $rework->toArray();
        $rework->update(['status'=>'COMPLETED','completed_at'=>now()]);
        return $rework->fresh()->toArray();
    }
}
