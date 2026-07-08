<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Application\Services;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrderLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisitionLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\SupplierProduct;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PurchasingService extends BaseService
{
    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginateSuppliers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Supplier::query()
            ->when(! empty($filters['q']), static function ($query) use ($filters): void {
                $term = (string) $filters['q'];
                $query->where(static function ($nested) use ($term): void {
                    $nested->where('code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%");
                });
            })
            ->when(! empty($filters['status']), static fn ($query) => $query->where('status', (string) $filters['status']))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function createSupplier(array $payload): array
    {
        $supplier = Supplier::query()->create([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'status' => $payload['status'] ?? 'ACTIVE',
            'default_lead_time_days' => (int) ($payload['default_lead_time_days'] ?? 0),
            'payment_terms' => $payload['payment_terms'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
        ]);

        return $supplier->toArray();
    }

    public function updateSupplier(int $supplierId, array $payload): array
    {
        $supplier = Supplier::query()->findOrFail($supplierId);

        $supplier->fill([
            'name' => $payload['name'] ?? $supplier->name,
            'email' => $payload['email'] ?? $supplier->email,
            'phone' => $payload['phone'] ?? $supplier->phone,
            'status' => $payload['status'] ?? $supplier->status,
            'default_lead_time_days' => $payload['default_lead_time_days'] ?? $supplier->default_lead_time_days,
            'payment_terms' => $payload['payment_terms'] ?? $supplier->payment_terms,
            'metadata' => $payload['metadata'] ?? $supplier->metadata,
        ]);
        $supplier->save();

        return $supplier->toArray();
    }

    public function upsertSupplierProductRule(int $supplierId, int $productId, array $payload): array
    {
        Supplier::query()->findOrFail($supplierId);
        Product::query()->findOrFail($productId);

        $rule = SupplierProduct::query()->updateOrCreate(
            [
                'supplier_id' => $supplierId,
                'product_id' => $productId,
            ],
            [
                'supplier_sku' => $payload['supplier_sku'] ?? null,
                'moq' => round((float) ($payload['moq'] ?? 1), 6),
                'lead_time_days' => (int) ($payload['lead_time_days'] ?? 0),
                'unit_price' => isset($payload['unit_price']) ? round((float) $payload['unit_price'], 6) : null,
                'is_preferred' => (bool) ($payload['is_preferred'] ?? false),
                'is_active' => (bool) ($payload['is_active'] ?? true),
                'metadata' => $payload['metadata'] ?? null,
            ]
        );

        if ($rule->is_preferred) {
            SupplierProduct::query()
                ->where('product_id', $productId)
                ->where('id', '!=', $rule->id)
                ->update(['is_preferred' => false]);
        }

        return $rule->toArray();
    }

    public function supplierProductRules(int $supplierId): array
    {
        Supplier::query()->findOrFail($supplierId);

        return SupplierProduct::query()
            ->where('supplier_id', $supplierId)
            ->with('product:id,sku,description')
            ->orderByDesc('is_preferred')
            ->orderBy('id')
            ->get()
            ->map(static fn (SupplierProduct $row): array => $row->toArray())
            ->all();
    }

    public function paginateRequisitions(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return PurchaseRequisition::query()
            ->when(! empty($filters['status']), static fn ($query) => $query->where('status', (string) $filters['status']))
            ->when(! empty($filters['source_type']), static fn ($query) => $query->where('source_type', (string) $filters['source_type']))
            ->withCount('lines')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function showRequisition(int $id): array
    {
        return PurchaseRequisition::query()
            ->with(['lines.product:id,sku,description', 'lines.supplier:id,code,name'])
            ->findOrFail($id)
            ->toArray();
    }

    public function createRequisition(array $payload, ?int $userId = null): array
    {
        if (empty($payload['lines'])) {
            throw new DomainException('At least one requisition line is required', 422);
        }

        $requisition = $this->inTransaction(function () use ($payload, $userId) {
            $header = PurchaseRequisition::query()->create([
                'requisition_number' => $this->nextRequisitionNumber(),
                'status' => 'DRAFT',
                'required_date' => $payload['required_date'] ?? null,
                'source_type' => $payload['source_type'] ?? 'manual',
                'source_reference_id' => $payload['source_reference_id'] ?? null,
                'source_reference_type' => $payload['source_reference_type'] ?? null,
                'requested_by' => $userId,
                'notes' => $payload['notes'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            foreach ($payload['lines'] as $line) {
                $suggested = round((float) ($line['suggested_quantity'] ?? $line['requested_quantity']), 6);
                $requested = round((float) $line['requested_quantity'], 6);
                $moq = round((float) ($line['moq_applied'] ?? 1), 6);

                PurchaseRequisitionLine::query()->create([
                    'purchase_requisition_id' => $header->id,
                    'product_id' => (int) $line['product_id'],
                    'warehouse_id' => $line['warehouse_id'] ?? null,
                    'supplier_id' => $line['supplier_id'] ?? null,
                    'suggested_quantity' => $suggested,
                    'requested_quantity' => $requested,
                    'moq_applied' => $moq,
                    'lead_time_days' => (int) ($line['lead_time_days'] ?? 0),
                    'need_by_date' => Carbon::parse($line['need_by_date'])->toDateString(),
                    'order_date' => Carbon::parse($line['order_date'])->toDateString(),
                    'status' => 'OPEN',
                    'source_requirement_key' => $line['source_requirement_key'] ?? null,
                    'mrp_reference_date' => isset($line['mrp_reference_date'])
                        ? Carbon::parse($line['mrp_reference_date'])->toDateString()
                        : null,
                    'metadata' => $line['metadata'] ?? null,
                ]);
            }

            return $header->refresh()->load('lines');
        });

        return $requisition->toArray();
    }

    public function createRequisitionFromMrp(array $payload, ?int $userId = null): array
    {
        $mrpSuggestions = array_values($payload['purchase_suggestions'] ?? []);

        if (empty($mrpSuggestions)) {
            throw new DomainException('purchase_suggestions is required and cannot be empty', 422);
        }

        $lines = [];

        foreach ($mrpSuggestions as $suggestion) {
            $productId = (int) $suggestion['product_id'];
            $suggestedQty = round((float) $suggestion['quantity'], 6);

            if ($suggestedQty <= 0) {
                continue;
            }

            $supplierRule = $this->bestSupplierRuleForProduct($productId);
            $leadTimeDays = $this->resolveLeadTimeDays($supplierRule, $suggestion);
            $moq = max(0.000001, round((float) ($supplierRule?->moq ?? 1), 6));
            $needByDate = Carbon::parse((string) $suggestion['need_by_date'])->toDateString();
            $orderDate = Carbon::parse($needByDate)->subDays($leadTimeDays)->toDateString();
            $requestedQty = round(max($suggestedQty, $moq), 6);

            $lines[] = [
                'product_id' => $productId,
                'warehouse_id' => $suggestion['warehouse_id'] ?? null,
                'supplier_id' => $supplierRule?->supplier_id,
                'suggested_quantity' => $suggestedQty,
                'requested_quantity' => $requestedQty,
                'moq_applied' => $moq,
                'lead_time_days' => $leadTimeDays,
                'need_by_date' => $needByDate,
                'order_date' => $orderDate,
                'source_requirement_key' => $suggestion['source_requirement_key'] ?? null,
                'mrp_reference_date' => $suggestion['reference_date'] ?? ($payload['reference_date'] ?? null),
                'metadata' => [
                    'mrp_suggestion' => $suggestion,
                    'supplier_rule_id' => $supplierRule?->id,
                ],
            ];
        }

        if (empty($lines)) {
            throw new DomainException('No valid purchase suggestion lines were provided', 422);
        }

        $requiredDate = collect($lines)
            ->pluck('need_by_date')
            ->sort()
            ->first();

        return $this->createRequisition([
            'required_date' => $requiredDate,
            'source_type' => 'mrp',
            'source_reference_id' => $payload['source_reference_id'] ?? null,
            'source_reference_type' => $payload['source_reference_type'] ?? 'mrp_plan',
            'notes' => $payload['notes'] ?? 'Generated from MRP purchase suggestions',
            'metadata' => [
                'reference_date' => $payload['reference_date'] ?? null,
                'line_count' => count($lines),
            ],
            'lines' => $lines,
        ], $userId);
    }

    public function convertRequisitionToPurchaseOrders(int $requisitionId, ?int $userId = null): array
    {
        $requisition = PurchaseRequisition::query()
            ->with('lines')
            ->findOrFail($requisitionId);

        if ($requisition->lines->isEmpty()) {
            throw new DomainException('Requisition has no lines to convert into purchase orders', 422);
        }

        $missingSupplierLine = $requisition->lines->first(static fn (PurchaseRequisitionLine $line): bool => $line->supplier_id === null);

        if ($missingSupplierLine) {
            throw new DomainException('All requisition lines must have an assigned supplier to generate purchase orders', 422);
        }

        $createdOrders = $this->inTransaction(function () use ($requisition, $userId) {
            $orders = [];
            $groups = $requisition->lines->groupBy('supplier_id');

            foreach ($groups as $supplierId => $supplierLines) {
                $orderDate = $supplierLines
                    ->pluck('order_date')
                    ->filter()
                    ->sort()
                    ->first();
                $expectedDate = $supplierLines
                    ->pluck('need_by_date')
                    ->filter()
                    ->sort()
                    ->last();

                $order = PurchaseOrder::query()->create([
                    'purchase_order_number' => $this->nextPurchaseOrderNumber(),
                    'supplier_id' => (int) $supplierId,
                    'purchase_requisition_id' => $requisition->id,
                    'status' => 'DRAFT',
                    'order_date' => Carbon::parse((string) $orderDate)->toDateString(),
                    'expected_delivery_date' => Carbon::parse((string) $expectedDate)->toDateString(),
                    'created_by' => $userId,
                    'notes' => sprintf('Generated from requisition %s', $requisition->requisition_number),
                ]);

                foreach ($supplierLines as $line) {
                    PurchaseOrderLine::query()->create([
                        'purchase_order_id' => $order->id,
                        'purchase_requisition_line_id' => $line->id,
                        'product_id' => $line->product_id,
                        'warehouse_id' => $line->warehouse_id,
                        'quantity_ordered' => $line->requested_quantity,
                        'quantity_received' => 0,
                        'need_by_date' => $line->need_by_date,
                        'promised_date' => $line->need_by_date,
                        'status' => 'OPEN',
                        'metadata' => [
                            'requisition_number' => $requisition->requisition_number,
                            'source_requirement_key' => $line->source_requirement_key,
                        ],
                    ]);
                }

                $orders[] = $order->refresh()->load(['lines.product:id,sku,description'])->toArray();
            }

            $requisition->status = 'CONVERTED';
            $requisition->save();

            return $orders;
        });

        return [
            'purchase_orders' => $createdOrders,
            'count' => count($createdOrders),
        ];
    }

    public function paginatePurchaseOrders(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return PurchaseOrder::query()
            ->when(! empty($filters['status']), static fn ($query) => $query->where('status', (string) $filters['status']))
            ->when(! empty($filters['supplier_id']), static fn ($query) => $query->where('supplier_id', (int) $filters['supplier_id']))
            ->with('supplier:id,code,name')
            ->withCount('lines')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function showPurchaseOrder(int $id): array
    {
        return PurchaseOrder::query()
            ->with(['supplier:id,code,name', 'lines.product:id,sku,description'])
            ->findOrFail($id)
            ->toArray();
    }

    public function approvePurchaseOrder(int $id, ?int $userId = null): array
    {
        $po = PurchaseOrder::query()->findOrFail($id);

        if ($po->status !== 'DRAFT') {
            throw new DomainException('Only draft purchase orders can be approved', 422);
        }

        $po->status = 'APPROVED';
        $po->approved_by = $userId;
        $po->approved_at = now();
        $po->save();

        return $po->refresh()->load(['supplier:id,code,name', 'lines.product:id,sku,description'])->toArray();
    }

    private function bestSupplierRuleForProduct(int $productId): ?SupplierProduct
    {
        return SupplierProduct::query()
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->orderByDesc('is_preferred')
            ->orderBy('lead_time_days')
            ->first();
    }

    private function resolveLeadTimeDays(?SupplierProduct $supplierRule, array $suggestion): int
    {
        if ($supplierRule !== null) {
            return max(0, (int) $supplierRule->lead_time_days);
        }

        $product = Product::query()->find((int) $suggestion['product_id']);

        if ($product) {
            return max(0, (int) $product->lead_time_days);
        }

        return max(0, (int) ($suggestion['lead_time_days'] ?? 0));
    }

    private function nextRequisitionNumber(): string
    {
        $sequence = (int) ((PurchaseRequisition::query()->max('id') ?? 0) + 1);

        return sprintf('PR-%s-%06d', now()->format('Ymd'), $sequence);
    }

    private function nextPurchaseOrderNumber(): string
    {
        $sequence = (int) ((PurchaseOrder::query()->max('id') ?? 0) + 1);

        return sprintf('PO-%s-%06d', now()->format('Ymd'), $sequence);
    }
}
