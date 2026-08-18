<?php

declare(strict_types=1);

namespace App\Modules\Eco\Application\Services;

use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Eco\Infrastructure\Persistence\Models\EngineeringChangeOrder;
use App\Modules\Eco\Infrastructure\Persistence\Models\EngineeringChangeOrderLine;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Product\Infrastructure\Persistence\Models\ProductVersion;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationStandardTime;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersion;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EngineeringChangeOrderService extends BaseService
{
    private const VALID_TARGET_DOMAIN = ['PRODUCT', 'BOM', 'ROUTING', 'STANDARD_TIME'];

    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return EngineeringChangeOrder::query()
            ->withCount('lines')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function show(int $ecoId): array
    {
        return EngineeringChangeOrder::query()
            ->with('lines')
            ->findOrFail($ecoId)
            ->toArray();
    }

    public function createDraft(array $payload, ?int $userId = null): array
    {
        $this->assertEffectiveDating($payload['effective_from'] ?? null, $payload['effective_to'] ?? null);

        if (empty($payload['lines'])) {
            throw new DomainException('At least one ECO line is required', 422);
        }

        $eco = $this->inTransaction(function () use ($payload, $userId) {
            $eco = EngineeringChangeOrder::query()->create([
                'eco_number' => $this->nextEcoNumber(),
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'status' => 'DRAFT',
                'effective_from' => $payload['effective_from'] ?? null,
                'effective_to' => $payload['effective_to'] ?? null,
                'requested_by' => $userId,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            $this->syncLines($eco, $payload['lines']);

            return $eco->refresh()->load('lines');
        });

        $this->logger->info('eco.draft_created', [
            'eco_id' => $eco->id,
            'eco_number' => $eco->eco_number,
        ]);

        return $eco->toArray();
    }

    public function updateDraft(int $ecoId, array $payload): array
    {
        $eco = EngineeringChangeOrder::query()->with('lines')->findOrFail($ecoId);

        if ($eco->status !== 'DRAFT') {
            throw new DomainException('Only draft ECO records can be updated', 422);
        }

        $this->assertEffectiveDating($payload['effective_from'] ?? null, $payload['effective_to'] ?? null);

        $updated = $this->inTransaction(function () use ($eco, $payload) {
            $eco->fill([
                'title' => $payload['title'] ?? $eco->title,
                'description' => $payload['description'] ?? $eco->description,
                'effective_from' => $payload['effective_from'] ?? $eco->effective_from,
                'effective_to' => $payload['effective_to'] ?? $eco->effective_to,
                'metadata' => $payload['metadata'] ?? $eco->metadata,
            ]);
            $eco->save();

            if (! empty($payload['lines'])) {
                $this->syncLines($eco, $payload['lines']);
            }

            return $eco->refresh()->load('lines');
        });

        return $updated->toArray();
    }

    public function submit(int $ecoId, ?int $userId = null): array
    {
        $eco = EngineeringChangeOrder::query()->with('lines')->findOrFail($ecoId);

        if ($eco->status !== 'DRAFT') {
            throw new DomainException('Only draft ECO records can be submitted', 422);
        }

        if ($eco->lines->isEmpty()) {
            throw new DomainException('ECO submission requires at least one change line', 422);
        }

        if (! $eco->effective_from) {
            throw new DomainException('effective_from is required before submission', 422);
        }

        $submitted = $this->inTransaction(function () use ($eco, $userId) {
            $eco->status = 'SUBMITTED';
            $eco->submitted_by = $userId;
            $eco->submitted_at = now();
            $eco->save();

            return $eco;
        });

        return $submitted->refresh()->load('lines')->toArray();
    }

    public function approve(int $ecoId, array $payload, ?int $userId = null): array
    {
        $eco = EngineeringChangeOrder::query()->with('lines')->findOrFail($ecoId);

        if ($eco->status !== 'SUBMITTED') {
            throw new DomainException('Only submitted ECO records can be approved', 422);
        }

        $effectiveFrom = $payload['effective_from'] ?? $eco->effective_from?->toDateString();
        $effectiveTo = $payload['effective_to'] ?? $eco->effective_to?->toDateString();

        if (! $effectiveFrom) {
            throw new DomainException('effective_from is required for approval', 422);
        }

        $this->assertEffectiveDating($effectiveFrom, $effectiveTo);

        $impact = $this->analyzeImpact($ecoId, [
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
        ]);

        $approved = $this->inTransaction(function () use ($eco, $effectiveFrom, $effectiveTo, $impact, $userId) {
            $eco->status = 'APPROVED';
            $eco->effective_from = $effectiveFrom;
            $eco->effective_to = $effectiveTo;
            $eco->approved_by = $userId;
            $eco->approved_at = now();
            $eco->impact_summary = $impact;
            $eco->save();

            return $eco;
        });

        return $approved->refresh()->load('lines')->toArray();
    }

    public function reject(int $ecoId, string $reason, ?int $userId = null): array
    {
        $eco = EngineeringChangeOrder::query()->findOrFail($ecoId);

        if ($eco->status !== 'SUBMITTED') {
            throw new DomainException('Only submitted ECO records can be rejected', 422);
        }

        $rejected = $this->inTransaction(function () use ($eco, $reason, $userId) {
            $eco->status = 'REJECTED';
            $eco->rejection_reason = $reason;
            $eco->rejected_by = $userId;
            $eco->rejected_at = now();
            $eco->save();

            return $eco;
        });

        return $rejected->refresh()->load('lines')->toArray();
    }

    public function implement(int $ecoId, ?int $userId = null): array
    {
        $eco = EngineeringChangeOrder::query()->findOrFail($ecoId);

        if ($eco->status !== 'APPROVED') {
            throw new DomainException('Only approved ECO records can be implemented', 422);
        }

        $implemented = $this->inTransaction(function () use ($eco, $userId) {
            $eco->status = 'IMPLEMENTED';
            $eco->implemented_by = $userId;
            $eco->implemented_at = now();
            $eco->save();

            return $eco;
        });

        return $implemented->refresh()->load('lines')->toArray();
    }

    public function analyzeImpact(int $ecoId, array $effectiveOverride = []): array
    {
        $eco = EngineeringChangeOrder::query()->with('lines')->findOrFail($ecoId);
        $effectiveFrom = $effectiveOverride['effective_from'] ?? $eco->effective_from?->toDateString();
        $effectiveTo = $effectiveOverride['effective_to'] ?? $eco->effective_to?->toDateString();

        $openStatuses = ['DRAFT', 'RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED'];
        $lineImpacts = [];

        foreach ($eco->lines as $line) {
            $lineImpacts[] = match ($line->target_domain) {
                'PRODUCT' => $this->analyzeProductLineImpact($line, $openStatuses, $effectiveFrom, $effectiveTo),
                'BOM' => $this->analyzeBomLineImpact($line, $openStatuses, $effectiveFrom, $effectiveTo),
                'ROUTING' => $this->analyzeRoutingLineImpact($line, $openStatuses, $effectiveFrom, $effectiveTo),
                'STANDARD_TIME' => $this->analyzeStandardTimeLineImpact($line, $openStatuses, $effectiveFrom, $effectiveTo),
                default => [
                    'line_id' => (int) $line->id,
                    'target_domain' => $line->target_domain,
                    'target_entity_id' => (int) $line->target_entity_id,
                    'impact' => 'unknown_target_domain',
                ],
            };
        }

        $totalAffectedOrders = collect($lineImpacts)
            ->sum(static fn (array $impact): int => (int) ($impact['affected_open_production_orders'] ?? 0));

        return [
            'eco_id' => $eco->id,
            'eco_number' => $eco->eco_number,
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
            'line_impacts' => $lineImpacts,
            'summary' => [
                'lines_total' => count($lineImpacts),
                'affected_open_production_orders_total' => $totalAffectedOrders,
            ],
            'analyzed_at' => now()->toDateTimeString(),
        ];
    }

    private function syncLines(EngineeringChangeOrder $eco, array $lines): void
    {
        EngineeringChangeOrderLine::query()
            ->where('engineering_change_order_id', $eco->id)
            ->delete();

        foreach ($lines as $line) {
            $this->assertTargetDomain((string) $line['target_domain']);
            $this->assertEffectiveDating($line['effective_from'] ?? null, $line['effective_to'] ?? null);
            $this->assertTargetEntity((string) $line['target_domain'], (int) $line['target_entity_id']);

            EngineeringChangeOrderLine::query()->create([
                'engineering_change_order_id' => $eco->id,
                'target_domain' => strtoupper((string) $line['target_domain']),
                'target_entity_id' => (int) $line['target_entity_id'],
                'change_type' => strtoupper((string) ($line['change_type'] ?? 'VERSION_CHANGE')),
                'from_version_number' => $line['from_version_number'] ?? null,
                'to_version_number' => $line['to_version_number'] ?? null,
                'effective_from' => $line['effective_from'] ?? null,
                'effective_to' => $line['effective_to'] ?? null,
                'impact_level' => strtoupper((string) ($line['impact_level'] ?? 'MEDIUM')),
                'change_summary' => $line['change_summary'] ?? null,
                'metadata' => $line['metadata'] ?? null,
            ]);
        }
    }

    private function assertTargetDomain(string $targetDomain): void
    {
        if (! in_array(strtoupper($targetDomain), self::VALID_TARGET_DOMAIN, true)) {
            throw new DomainException('Invalid ECO target domain', 422, [
                'target_domain' => self::VALID_TARGET_DOMAIN,
            ]);
        }
    }

    private function assertTargetEntity(string $targetDomain, int $targetEntityId): void
    {
        $exists = match (strtoupper($targetDomain)) {
            'PRODUCT' => Product::query()->whereKey($targetEntityId)->exists(),
            'BOM' => BomHeader::query()->whereKey($targetEntityId)->exists(),
            'ROUTING' => RoutingVersion::query()->whereKey($targetEntityId)->exists(),
            'STANDARD_TIME' => RoutingOperationStandardTime::query()->whereKey($targetEntityId)->exists(),
            default => false,
        };

        if (! $exists) {
            throw new DomainException('ECO target entity was not found in the active tenant', 422, [
                'target_domain' => strtoupper($targetDomain),
                'target_entity_id' => $targetEntityId,
            ]);
        }
    }

    private function assertEffectiveDating(?string $effectiveFrom, ?string $effectiveTo): void
    {
        if ($effectiveFrom !== null && $effectiveTo !== null && $effectiveTo < $effectiveFrom) {
            throw new DomainException('effective_to must be greater or equal to effective_from', 422);
        }
    }

    private function nextEcoNumber(): string
    {
        $datePart = now()->format('Ymd');
        $sequence = (int) ((EngineeringChangeOrder::query()->max('id') ?? 0) + 1);

        return sprintf('ECO-%s-%06d', $datePart, $sequence);
    }

    private function analyzeProductLineImpact(EngineeringChangeOrderLine $line, array $openStatuses, ?string $effectiveFrom, ?string $effectiveTo): array
    {
        $ordersQuery = ProductionOrder::query()
            ->where('product_id', (int) $line->target_entity_id)
            ->whereIn('status', $openStatuses);

        if ($effectiveFrom !== null) {
            $ordersQuery->where(static function ($query) use ($effectiveFrom): void {
                $query->whereNull('scheduled_end_date')
                    ->orWhereDate('scheduled_end_date', '>=', $effectiveFrom);
            });
        }

        $affectedOrders = $ordersQuery->limit(10)->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $overlappingVersions = ProductVersion::query()
            ->where('product_id', (int) $line->target_entity_id)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $effectiveTo ?? ($effectiveFrom ?? now()->toDateString()))
            ->where(static function ($query) use ($effectiveFrom): void {
                if ($effectiveFrom === null) {
                    $query->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', now()->toDateString());

                    return;
                }

                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $effectiveFrom);
            })
            ->count();

        return [
            'line_id' => (int) $line->id,
            'target_domain' => 'PRODUCT',
            'target_entity_id' => (int) $line->target_entity_id,
            'affected_open_production_orders' => count($affectedOrders),
            'affected_open_production_order_ids' => $affectedOrders,
            'overlapping_approved_versions' => (int) $overlappingVersions,
        ];
    }

    private function analyzeBomLineImpact(EngineeringChangeOrderLine $line, array $openStatuses, ?string $effectiveFrom, ?string $effectiveTo): array
    {
        $bomHeader = BomHeader::query()->find((int) $line->target_entity_id);

        $ordersQuery = ProductionOrder::query()
            ->where('bom_header_id', (int) $line->target_entity_id)
            ->whereIn('status', $openStatuses);

        if ($effectiveFrom !== null) {
            $ordersQuery->where(static function ($query) use ($effectiveFrom): void {
                $query->whereNull('scheduled_end_date')
                    ->orWhereDate('scheduled_end_date', '>=', $effectiveFrom);
            });
        }

        $affectedOrders = $ordersQuery->limit(10)->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $overlappingApprovedBom = 0;

        if ($bomHeader) {
            $overlappingApprovedBom = BomHeader::query()
                ->where('product_id', (int) $bomHeader->product_id)
                ->where('status', 'APPROVED')
                ->whereDate('effective_from', '<=', $effectiveTo ?? ($effectiveFrom ?? now()->toDateString()))
                ->where(static function ($query) use ($effectiveFrom): void {
                    if ($effectiveFrom === null) {
                        $query->whereNull('effective_to')
                            ->orWhereDate('effective_to', '>=', now()->toDateString());

                        return;
                    }

                    $query->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', $effectiveFrom);
                })
                ->count();
        }

        return [
            'line_id' => (int) $line->id,
            'target_domain' => 'BOM',
            'target_entity_id' => (int) $line->target_entity_id,
            'affected_open_production_orders' => count($affectedOrders),
            'affected_open_production_order_ids' => $affectedOrders,
            'overlapping_approved_versions' => (int) $overlappingApprovedBom,
        ];
    }

    private function analyzeRoutingLineImpact(EngineeringChangeOrderLine $line, array $openStatuses, ?string $effectiveFrom, ?string $effectiveTo): array
    {
        $routingVersion = RoutingVersion::query()->find((int) $line->target_entity_id);

        $ordersQuery = ProductionOrder::query()
            ->where('routing_version_id', (int) $line->target_entity_id)
            ->whereIn('status', $openStatuses);

        if ($effectiveFrom !== null) {
            $ordersQuery->where(static function ($query) use ($effectiveFrom): void {
                $query->whereNull('scheduled_end_date')
                    ->orWhereDate('scheduled_end_date', '>=', $effectiveFrom);
            });
        }

        $affectedOrders = $ordersQuery->limit(10)->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $overlappingApprovedRouting = 0;

        if ($routingVersion) {
            $overlappingApprovedRouting = RoutingVersion::query()
                ->where('product_id', (int) $routingVersion->product_id)
                ->where('status', 'APPROVED')
                ->whereDate('effective_from', '<=', $effectiveTo ?? ($effectiveFrom ?? now()->toDateString()))
                ->where(static function ($query) use ($effectiveFrom): void {
                    if ($effectiveFrom === null) {
                        $query->whereNull('effective_to')
                            ->orWhereDate('effective_to', '>=', now()->toDateString());

                        return;
                    }

                    $query->whereNull('effective_to')
                        ->orWhereDate('effective_to', '>=', $effectiveFrom);
                })
                ->count();
        }

        return [
            'line_id' => (int) $line->id,
            'target_domain' => 'ROUTING',
            'target_entity_id' => (int) $line->target_entity_id,
            'affected_open_production_orders' => count($affectedOrders),
            'affected_open_production_order_ids' => $affectedOrders,
            'overlapping_approved_versions' => (int) $overlappingApprovedRouting,
        ];
    }

    private function analyzeStandardTimeLineImpact(EngineeringChangeOrderLine $line, array $openStatuses, ?string $effectiveFrom, ?string $effectiveTo): array
    {
        $standardTime = RoutingOperationStandardTime::query()
            ->with('routingOperation')
            ->find((int) $line->target_entity_id);

        if (! $standardTime || ! $standardTime->routingOperation) {
            return [
                'line_id' => (int) $line->id,
                'target_domain' => 'STANDARD_TIME',
                'target_entity_id' => (int) $line->target_entity_id,
                'affected_open_production_orders' => 0,
                'affected_open_production_order_ids' => [],
                'impact' => 'target_not_found',
            ];
        }

        $routingVersionId = (int) $standardTime->routingOperation->routing_version_id;
        $ordersQuery = ProductionOrder::query()
            ->where('routing_version_id', $routingVersionId)
            ->whereIn('status', $openStatuses);

        if ($effectiveFrom !== null) {
            $ordersQuery->where(static function ($query) use ($effectiveFrom): void {
                $query->whereNull('scheduled_end_date')
                    ->orWhereDate('scheduled_end_date', '>=', $effectiveFrom);
            });
        }

        $affectedOrders = $ordersQuery->limit(10)->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        return [
            'line_id' => (int) $line->id,
            'target_domain' => 'STANDARD_TIME',
            'target_entity_id' => (int) $line->target_entity_id,
            'routing_operation_id' => (int) $standardTime->routing_operation_id,
            'routing_version_id' => $routingVersionId,
            'affected_open_production_orders' => count($affectedOrders),
            'affected_open_production_order_ids' => $affectedOrders,
            'current_version_number' => (int) $standardTime->version_number,
        ];
    }
}
