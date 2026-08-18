<?php

declare(strict_types=1);

namespace App\Modules\Routing\Application\Services;

use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperation;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationStandardTime;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class RoutingStandardTimeService extends BaseService
{
    private const TIME_BASES = ['PER_PROCESS', 'PER_UNIT', 'PER_BATCH'];

    private const STATUSES = ['DRAFT', 'APPROVED', 'OBSOLETE'];

    public function __construct(TransactionManager $transaction, CacheManager $cache, AppLogger $logger)
    {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(int $routingOperationId, int $perPage = 15): LengthAwarePaginator
    {
        RoutingOperation::query()->findOrFail($routingOperationId);

        return RoutingOperationStandardTime::query()
            ->where('routing_operation_id', $routingOperationId)
            ->orderByDesc('version_number')
            ->paginate($perPage);
    }

    public function create(int $routingOperationId, array $payload, ?int $createdBy = null): array
    {
        $operation = RoutingOperation::query()->findOrFail($routingOperationId);
        $this->assertPayload($payload);

        $versionNumber = (int) ($payload['version_number'] ?? ((int) RoutingOperationStandardTime::query()
            ->where('routing_operation_id', $operation->id)->max('version_number') + 1));

        $time = $this->inTransaction(function () use ($operation, $payload, $versionNumber, $createdBy): RoutingOperationStandardTime {
            return RoutingOperationStandardTime::query()->create([
                'routing_operation_id' => $operation->id,
                'version_number' => $versionNumber,
                'status' => 'DRAFT',
                'time_basis' => strtoupper((string) ($payload['time_basis'] ?? 'PER_PROCESS')),
                'setup_scope' => strtoupper((string) ($payload['setup_scope'] ?? 'ROUTING')),
                'base_quantity' => $payload['base_quantity'] ?? 1,
                'setup_time_minutes' => $payload['setup_time_minutes'] ?? 0,
                'runtime_minutes' => $payload['runtime_minutes'] ?? 0,
                'queue_time_minutes' => $payload['queue_time_minutes'] ?? 0,
                'move_time_minutes' => $payload['move_time_minutes'] ?? 0,
                'efficiency_factor' => $payload['efficiency_factor'] ?? 100,
                'yield_factor' => $payload['yield_factor'] ?? 100,
                'effective_from' => $payload['effective_from'] ?? null,
                'effective_to' => $payload['effective_to'] ?? null,
                'created_by' => $createdBy,
                'change_reason' => $payload['change_reason'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);
        });

        return $time->refresh()->toArray();
    }

    public function updateDraft(int $id, array $payload): array
    {
        $this->assertPayload($payload);

        $time = $this->inTransaction(function () use ($id, $payload): RoutingOperationStandardTime {
            $time = RoutingOperationStandardTime::query()->findOrFail($id);

            if ($time->status !== 'DRAFT') {
                throw new DomainException('Only draft standard times can be updated', 422);
            }

            $time->fill($payload);
            $time->save();

            return $time;
        });

        return $time->refresh()->toArray();
    }

    public function approve(int $id, array $payload, ?int $userId = null): array
    {
        $time = RoutingOperationStandardTime::query()->with('routingOperation')->findOrFail($id);

        if ($time->status !== 'DRAFT') {
            throw new DomainException('Only draft standard times can be approved', 422);
        }

        $effectiveFrom = $payload['effective_from'] ?? $time->effective_from?->toDateString();
        $effectiveTo = $payload['effective_to'] ?? $time->effective_to?->toDateString();

        if (! $effectiveFrom) {
            throw new DomainException('effective_from is required for standard time approval', 422);
        }

        $this->assertNoOverlap((int) $time->routing_operation_id, $effectiveFrom, $effectiveTo, (int) $time->id);

        $approved = $this->inTransaction(function () use ($time, $effectiveFrom, $effectiveTo, $userId): RoutingOperationStandardTime {
            $time->fill([
                'status' => 'APPROVED',
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
            $time->save();

            return $time;
        });

        return $approved->refresh()->toArray();
    }

    public function obsolete(int $id): array
    {
        $time = RoutingOperationStandardTime::query()->findOrFail($id);

        if ($time->status !== 'APPROVED') {
            throw new DomainException('Only approved standard times can be marked obsolete', 422);
        }

        $time->status = 'OBSOLETE';
        $time->effective_to ??= now()->toDateString();
        $time->save();

        return $time->refresh()->toArray();
    }

    public function effectiveForOperation(int $routingOperationId, string $referenceDate): ?RoutingOperationStandardTime
    {
        return RoutingOperationStandardTime::query()
            ->where('routing_operation_id', $routingOperationId)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $referenceDate)
            ->where(static function (Builder $query) use ($referenceDate): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $referenceDate);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    public function calculate(int $routingOperationId, float $quantity, string $referenceDate): array
    {
        if ($quantity <= 0) {
            throw new DomainException('quantity must be greater than zero', 422);
        }

        $operation = RoutingOperation::query()->findOrFail($routingOperationId);
        $standard = $this->effectiveForOperation($routingOperationId, $referenceDate);

        $source = $standard ?? new RoutingOperationStandardTime([
            'version_number' => 0,
            'time_basis' => 'PER_PROCESS',
            'setup_scope' => 'ROUTING',
            'base_quantity' => 1,
            'setup_time_minutes' => $operation->setup_time_minutes,
            'runtime_minutes' => $operation->runtime_minutes,
            'queue_time_minutes' => $operation->queue_time_minutes,
            'move_time_minutes' => $operation->move_time_minutes,
            'efficiency_factor' => 100,
            'yield_factor' => 100,
        ]);

        if ($operation->is_outsourced) {
            throw new DomainException('Outsourced operations are not calculated as internal production time', 422);
        }

        $baseQuantity = max(0.000001, (float) $source->base_quantity);
        $multiplier = $source->time_basis === 'PER_UNIT' ? $quantity / $baseQuantity : 1.0;
        $setup = $source->setup_scope === 'ROUTING' && (int) $operation->sequence !== 1
            ? 0.0
            : (float) $source->setup_time_minutes;
        $runtime = (float) $source->runtime_minutes * $multiplier;
        $leadTime = (float) $source->queue_time_minutes + (float) $source->move_time_minutes;

        return [
            'routing_operation_id' => $routingOperationId,
            'operation_no' => (int) $operation->operation_no,
            'standard_time_id' => $standard?->id,
            'standard_time_version' => (int) $source->version_number,
            'time_basis' => $source->time_basis,
            'setup_scope' => $source->setup_scope,
            'quantity' => $quantity,
            'setup_time_minutes' => $this->roundMinutes($setup),
            'runtime_time_minutes' => $this->roundMinutes($runtime),
            'queue_time_minutes' => (float) $source->queue_time_minutes,
            'move_time_minutes' => (float) $source->move_time_minutes,
            'productive_time_minutes' => $this->roundMinutes($setup + $runtime),
            'lead_time_minutes' => $this->roundMinutes($leadTime),
            'total_time_minutes' => $this->roundMinutes($setup + $runtime + $leadTime),
        ];
    }

    private function assertPayload(array $payload): void
    {
        if (! in_array(strtoupper((string) ($payload['time_basis'] ?? 'PER_PROCESS')), self::TIME_BASES, true)) {
            throw new DomainException('Invalid standard time basis', 422, ['time_basis' => self::TIME_BASES]);
        }

        if (isset($payload['effective_from'], $payload['effective_to']) && $payload['effective_to'] < $payload['effective_from']) {
            throw new DomainException('effective_to must be greater or equal to effective_from', 422);
        }

        if ((float) ($payload['base_quantity'] ?? 1) <= 0 || (float) ($payload['efficiency_factor'] ?? 100) <= 0 || (float) ($payload['yield_factor'] ?? 100) <= 0) {
            throw new DomainException('base_quantity, efficiency_factor and yield_factor must be greater than zero', 422);
        }

        if (! in_array(strtoupper((string) ($payload['setup_scope'] ?? 'ROUTING')), ['ROUTING', 'OPERATION'], true)) {
            throw new DomainException('Invalid standard time setup scope', 422);
        }
    }

    private function roundMinutes(float $minutes): float
    {
        return round($minutes / 5) * 5.0;
    }

    private function assertNoOverlap(int $operationId, string $from, ?string $to, int $ignoreId): void
    {
        $exists = RoutingOperationStandardTime::query()
            ->where('routing_operation_id', $operationId)
            ->where('status', 'APPROVED')
            ->where('id', '!=', $ignoreId)
            ->whereDate('effective_from', '<=', $to ?? $from)
            ->where(static function (Builder $query) use ($from): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $from);
            })
            ->exists();

        if ($exists) {
            throw new DomainException('Approved standard times cannot overlap', 422);
        }
    }
}
