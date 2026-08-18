<?php

declare(strict_types=1);

namespace App\Modules\MRP\Application\Services;

use App\Modules\MRP\Infrastructure\Persistence\Models\MrpPlanRun;
use App\Modules\MRP\Infrastructure\Persistence\Models\MrpSuggestion;
use App\Modules\MRP\Infrastructure\Persistence\Models\MrpSuggestionEvent;
use App\Modules\Production\Application\Services\ProductionOrderService;
use App\Modules\Purchasing\Application\Services\PurchasingService;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MrpSuggestionWorkflowService extends BaseService
{
    public function __construct(
        private readonly ProductionOrderService $productionOrders,
        private readonly PurchasingService $purchasing,
        TransactionManager $transaction, CacheManager $cache, AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function persist(array $payload, array $result, ?int $userId = null): array
    {
        $runKey = sha1(json_encode(['payload' => $payload, 'result' => [$result['reference_date'] ?? null, $result['net_requirements'] ?? []]], JSON_THROW_ON_ERROR));
        $existing = MrpPlanRun::query()->where('run_key', $runKey)->with('suggestions')->first();
        if ($existing) {
            return ['plan_run' => $existing->toArray(), 'suggestions' => $existing->suggestions->toArray(), 'result' => $result];
        }
        $run = $this->inTransaction(function () use ($payload, $result, $runKey, $userId) {
            $run = MrpPlanRun::query()->create(['run_key' => $runKey, 'status' => 'COMPLETED', 'reference_date' => $result['reference_date'] ?? now()->toDateString(), 'planning_bucket' => $result['planning_bucket'] ?? 'daily', 'priority_rule' => $result['priority_rule'] ?? 'priority_due_date', 'request_payload' => $payload, 'result_summary' => ['purchase_count' => count($result['purchase_suggestions'] ?? []), 'production_count' => count($result['production_suggestions'] ?? [])], 'created_by' => $userId]);
            foreach (array_merge($result['purchase_suggestions'] ?? [], $result['production_suggestions'] ?? []) as $source) {
                $type = (string) $source['suggestion_type'];
                $suggestion = MrpSuggestion::query()->create([
                    'mrp_plan_run_id' => $run->id, 'suggestion_key' => $runKey.'|'.$type.'|'.($source['source_requirement_key'] ?? uniqid()), 'suggestion_type' => $type, 'status' => 'GENERATED',
                    'product_id' => $source['product_id'], 'warehouse_id' => $source['warehouse_id'] ?? null, 'original_quantity' => $source['quantity'], 'need_by_date' => $source['need_by_date'], 'release_date' => $source['release_date'] ?? ($source['order_date'] ?? null), 'priority' => $source['priority'] ?? 1000, 'bom_version_number' => $source['bom_version_number'] ?? null, 'routing_version_id' => $source['routing_version_id'] ?? null, 'source_requirement_key' => $source['source_requirement_key'] ?? null, 'source_reference_type' => $source['source_reference_type'] ?? null, 'source_reference_id' => $source['source_reference_id'] ?? null, 'original_payload' => $source, 'adjusted_payload' => $source,
                ]);
                $this->event($suggestion, 'CREATED', null, 'GENERATED', $userId, null, $source);
            }

            return $run;
        });

        return ['plan_run' => $run->load('suggestions')->toArray(), 'suggestions' => $run->suggestions->toArray(), 'result' => $result];
    }

    public function runs(int $perPage = 15): LengthAwarePaginator
    {
        return MrpPlanRun::query()->withCount('suggestions')->orderByDesc('id')->paginate($perPage);
    }

    public function suggestions(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return MrpSuggestion::query()->with(['product', 'planRun'])->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($filters['suggestion_type'] ?? null, fn ($q, $v) => $q->where('suggestion_type', $v))->orderByDesc('id')->paginate($perPage);
    }

    public function show(int $id): array
    {
        return MrpSuggestion::query()->with(['product', 'planRun', 'events'])->findOrFail($id)->toArray();
    }

    public function decide(int $id, string $status, array $payload, ?int $userId = null): array
    {
        $suggestion = MrpSuggestion::query()->findOrFail($id);
        if (in_array($suggestion->status, ['CONVERTED', 'CANCELLED'], true)) {
            throw new DomainException('Suggestion is no longer actionable', 422);
        }
        $from = $suggestion->status;
        $suggestion->update(['status' => $status, 'approved_quantity' => $payload['approved_quantity'] ?? ($status === 'APPROVED' ? $suggestion->original_quantity : null), 'decision_reason' => $payload['reason'] ?? null, 'adjusted_payload' => array_merge($suggestion->adjusted_payload ?? [], $payload), 'decided_by' => $userId, 'decided_at' => now()]);
        $this->event($suggestion, strtoupper($status), $from, $status, $userId, $payload['reason'] ?? null, $payload);

        return $suggestion->fresh()->toArray();
    }

    public function convert(int $id, ?int $userId = null): array
    {
        $suggestion = MrpSuggestion::query()->findOrFail($id);
        if ($suggestion->production_order_id || $suggestion->purchase_requisition_id) {
            return $suggestion->fresh()->toArray();
        }
        if ($suggestion->status !== 'APPROVED') {
            throw new DomainException('Only approved suggestions can be converted', 422);
        }
        $payload = $suggestion->adjusted_payload ?: $suggestion->original_payload;
        $created = $suggestion->suggestion_type === 'PRODUCTION'
            ? $this->productionOrders->createFromMrp($payload['production_order_payload'] ?? $payload, $userId)
            : $this->purchasing->createRequisitionFromMrp(['reference_date' => $suggestion->planRun?->reference_date?->toDateString(), 'purchase_suggestions' => [$payload['purchase_order_payload'] ?? $payload]], $userId);
        $suggestion->update([$suggestion->suggestion_type === 'PRODUCTION' ? 'production_order_id' : 'purchase_requisition_id' => $created['id'] ?? ($created['production_order']['id'] ?? null), 'status' => 'CONVERTED', 'converted_at' => now()]);
        $this->event($suggestion, 'CONVERTED', 'APPROVED', 'CONVERTED', $userId, null, ['created' => $created]);

        return $suggestion->fresh()->toArray();
    }

    private function event(MrpSuggestion $suggestion, string $type, ?string $from, ?string $to, ?int $userId, ?string $reason, array $payload): void
    {
        MrpSuggestionEvent::query()->create(['mrp_suggestion_id' => $suggestion->id, 'event_type' => $type, 'from_status' => $from, 'to_status' => $to, 'created_by' => $userId, 'reason' => $reason, 'payload' => $payload]);
    }
}
