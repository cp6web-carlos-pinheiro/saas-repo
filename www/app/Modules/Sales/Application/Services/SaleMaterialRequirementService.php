<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\Services;

use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryReservation;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;

final class SaleMaterialRequirementService
{
    private const PRODUCTION_PRODUCT_TYPES = ['FG', 'WIP'];

    /** @var array<int, Product> */
    private array $products = [];

    /** @var array<int, BomHeader|null> */
    private array $bomHeaders = [];

    /** @var array<int, float> */
    private array $initialFreeStock = [];

    /** @var array<int, float> */
    private array $remainingFreeStock = [];

    /** @var array<int, list<array{code: string, name: string, quantity: float}>> */
    private array $stockByWarehouse = [];

    /** @var array<int, float> */
    private array $remainingFinishedReservations = [];

    /** @var array<int, float> */
    private array $remainingMaterialReservations = [];

    /** @var array<int, array<string, mixed>> */
    private array $materials = [];

    /** @var array<int, array<string, mixed>> */
    private array $missingBoms = [];

    /** @var list<array<string, mixed>> */
    private array $cycles = [];

    private string $referenceDate = '';

    /** @return array<string, mixed> */
    public function analyze(Sale $sale): array
    {
        $this->reset();
        $sale->loadMissing(['lines.product.unit']);
        $this->referenceDate = $sale->sale_date?->toDateString() ?? now()->toDateString();
        $this->loadSaleReservations((int) $sale->id);

        $demandByProduct = [];

        foreach ($sale->lines as $line) {
            $productId = (int) $line->product_id;
            $demandByProduct[$productId] = round(
                (float) ($demandByProduct[$productId] ?? 0) + (float) $line->quantity,
                6
            );
        }

        $finishedProducts = [];

        foreach ($demandByProduct as $productId => $quantity) {
            $product = $this->product($productId);
            $linked = $this->consume($this->remainingFinishedReservations, $productId, $quantity);
            $afterLinked = max(0.0, round($quantity - $linked, 6));
            $availableToLink = $this->consume($this->remainingFreeStock, $productId, $afterLinked);
            $quantityToProduce = max(0.0, round($afterLinked - $availableToLink, 6));

            $finishedProducts[] = [
                'product_id' => $productId,
                'sku' => $product->sku,
                'description' => $product->description,
                'unit' => $product->unit?->code,
                'required_quantity' => $quantity,
                'linked_quantity' => $linked,
                'available_to_link' => $availableToLink,
                'quantity_to_produce' => $quantityToProduce,
                'stock_available' => $this->initialFreeStock[$productId],
                'warehouses' => $this->stockByWarehouse[$productId],
            ];

            if ($quantityToProduce > 0 && in_array((string) $product->product_type, self::PRODUCTION_PRODUCT_TYPES, true)) {
                $this->expandProduct($productId, $quantityToProduce, 0, [$productId]);
            }
        }

        $materials = array_values($this->materials);
        usort($materials, static function (array $left, array $right): int {
            $leftPriority = $left['recommended_action'] === 'BUY' && $left['shortage_quantity'] > 0 ? 0 : 1;
            $rightPriority = $right['recommended_action'] === 'BUY' && $right['shortage_quantity'] > 0 ? 0 : 1;

            return [$leftPriority, $left['sku']] <=> [$rightPriority, $right['sku']];
        });

        return [
            'sale_id' => (int) $sale->id,
            'reference_date' => $this->referenceDate,
            'finished_products' => $finishedProducts,
            'materials' => $materials,
            'materials_in_stock_count' => count(array_filter(
                $materials,
                static fn (array $row): bool => $row['linked_quantity'] > 0 || $row['available_to_link'] > 0
            )),
            'purchase_items_count' => count(array_filter(
                $materials,
                static fn (array $row): bool => $row['recommended_action'] === 'BUY' && $row['shortage_quantity'] > 0
            )),
            'production_items_count' => count(array_filter(
                $materials,
                static fn (array $row): bool => $row['recommended_action'] === 'PRODUCE' && $row['shortage_quantity'] > 0
            )),
            'missing_boms' => array_values($this->missingBoms),
            'cycles' => $this->cycles,
        ];
    }

    /** @param list<int> $path */
    private function expandProduct(int $productId, float $quantityToProduce, int $level, array $path): void
    {
        $header = $this->activeBom($productId);

        if (! $header instanceof BomHeader) {
            $product = $this->product($productId);
            $this->missingBoms[$productId] = [
                'product_id' => $productId,
                'sku' => $product->sku,
                'description' => $product->description,
            ];

            return;
        }

        $header->loadMissing(['items.componentProduct.unit']);

        foreach ($header->items as $item) {
            $componentId = (int) $item->component_product_id;
            $component = $this->product($componentId);
            $required = round($quantityToProduce * (float) $item->quantity_per, 6);

            if ($required <= 0) {
                continue;
            }

            $linked = $this->consume($this->remainingMaterialReservations, $componentId, $required);
            $afterLinked = max(0.0, round($required - $linked, 6));
            $availableToLink = $this->consume($this->remainingFreeStock, $componentId, $afterLinked);
            $shortage = max(0.0, round($afterLinked - $availableToLink, 6));
            $recommendedAction = in_array((string) $component->product_type, self::PRODUCTION_PRODUCT_TYPES, true)
                ? 'PRODUCE'
                : 'BUY';

            if (! isset($this->materials[$componentId])) {
                $this->materials[$componentId] = [
                    'product_id' => $componentId,
                    'sku' => $component->sku,
                    'description' => $component->description,
                    'product_type' => $component->product_type,
                    'unit' => $component->unit?->code,
                    'level' => $level + 1,
                    'required_quantity' => 0.0,
                    'linked_quantity' => 0.0,
                    'available_to_link' => 0.0,
                    'shortage_quantity' => 0.0,
                    'stock_available' => $this->initialFreeStock[$componentId],
                    'warehouses' => $this->stockByWarehouse[$componentId],
                    'recommended_action' => $recommendedAction,
                ];
            }

            $this->materials[$componentId]['level'] = min((int) $this->materials[$componentId]['level'], $level + 1);
            $this->materials[$componentId]['required_quantity'] = round((float) $this->materials[$componentId]['required_quantity'] + $required, 6);
            $this->materials[$componentId]['linked_quantity'] = round((float) $this->materials[$componentId]['linked_quantity'] + $linked, 6);
            $this->materials[$componentId]['available_to_link'] = round((float) $this->materials[$componentId]['available_to_link'] + $availableToLink, 6);
            $this->materials[$componentId]['shortage_quantity'] = round((float) $this->materials[$componentId]['shortage_quantity'] + $shortage, 6);

            if ($recommendedAction !== 'PRODUCE' || $shortage <= 0) {
                continue;
            }

            if (in_array($componentId, $path, true)) {
                $this->cycles[] = [
                    'sku' => $component->sku,
                    'path' => array_merge($path, [$componentId]),
                ];

                continue;
            }

            $this->expandProduct($componentId, $shortage, $level + 1, array_merge($path, [$componentId]));
        }
    }

    private function activeBom(int $productId): ?BomHeader
    {
        if (array_key_exists($productId, $this->bomHeaders)) {
            return $this->bomHeaders[$productId];
        }

        return $this->bomHeaders[$productId] = BomHeader::query()
            ->where('product_id', $productId)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $this->referenceDate)
            ->where(function ($query): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $this->referenceDate);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('version_number')
            ->first();
    }

    private function product(int $productId): Product
    {
        if (! isset($this->products[$productId])) {
            $this->products[$productId] = Product::query()->with('unit:id,code')->findOrFail($productId);
            $this->loadStock($productId);
        }

        return $this->products[$productId];
    }

    private function loadStock(int $productId): void
    {
        $balances = InventoryBalance::query()
            ->with('warehouse:id,code,name')
            ->where('product_id', $productId)
            ->where('qty_available', '>', 0)
            ->orderByDesc('qty_available')
            ->get();

        $quantity = round((float) $balances->sum('qty_available'), 6);
        $this->initialFreeStock[$productId] = $quantity;
        $this->remainingFreeStock[$productId] = $quantity;
        $this->stockByWarehouse[$productId] = $balances->map(static fn (InventoryBalance $balance): array => [
            'code' => (string) ($balance->warehouse?->code ?? '—'),
            'name' => (string) ($balance->warehouse?->name ?? '—'),
            'quantity' => round((float) $balance->qty_available, 6),
        ])->all();
    }

    private function loadSaleReservations(int $saleId): void
    {
        $reservations = InventoryReservation::query()
            ->where('reference_type', 'sale')
            ->where('reference_id', $saleId)
            ->where('status', 'RESERVED')
            ->get(['product_id', 'reservation_origin', 'quantity', 'metadata']);

        foreach ($reservations as $reservation) {
            $productId = (int) $reservation->product_id;
            $quantity = round((float) $reservation->quantity, 6);
            $allocationType = (string) (($reservation->metadata ?? [])['allocation_type'] ?? '');
            $isFinishedProduct = $allocationType === 'finished_good'
                || ($allocationType === '' && $reservation->reservation_origin === 'SALE');
            $target = $isFinishedProduct ? 'remainingFinishedReservations' : 'remainingMaterialReservations';
            $this->{$target}[$productId] = round((float) ($this->{$target}[$productId] ?? 0) + $quantity, 6);
        }
    }

    /** @param array<int, float> $pool */
    private function consume(array &$pool, int $productId, float $requested): float
    {
        if (! array_key_exists($productId, $pool)) {
            $this->product($productId);
        }

        $available = max(0.0, (float) ($pool[$productId] ?? 0));
        $consumed = round(min($requested, $available), 6);
        $pool[$productId] = max(0.0, round($available - $consumed, 6));

        return $consumed;
    }

    private function reset(): void
    {
        $this->products = [];
        $this->bomHeaders = [];
        $this->initialFreeStock = [];
        $this->remainingFreeStock = [];
        $this->stockByWarehouse = [];
        $this->remainingFinishedReservations = [];
        $this->remainingMaterialReservations = [];
        $this->materials = [];
        $this->missingBoms = [];
        $this->cycles = [];
        $this->referenceDate = '';
    }
}
