<?php

declare(strict_types=1);

namespace App\Modules\Genealogy\Application\Services;

use App\Modules\Genealogy\Infrastructure\Persistence\Models\GenealogyNode;
use App\Modules\Genealogy\Infrastructure\Persistence\Models\GenealogyRelation;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryLot;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventorySerial;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderMaterialConsumption;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOutput;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class GenealogyService extends BaseService
{
    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function linkLotProductionOutput(int $productionOrderId, string $lotNumber, array $payload = []): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);
        $output = ProductionOrderOutput::query()
            ->where('production_order_id', $productionOrderId)
            ->where('lot_number', $lotNumber)
            ->firstOrFail();

        $productNode = $this->upsertProductNode((int) $order->product_id, (int) $order->warehouse_id, [
            'source_reference' => $order->product?->sku,
        ]);

        $parentNode = $this->upsertNode('PRODUCTION_ORDER', $order->id, [
            'source_reference' => $order->order_number,
            'product_id' => $order->product_id,
            'warehouse_id' => $order->warehouse_id,
        ]);

        $childNode = $this->upsertNode('LOT', $output->id, [
            'source_reference' => $lotNumber,
            'product_id' => $order->product_id,
            'warehouse_id' => $order->warehouse_id,
            'metadata' => $payload['lot_metadata'] ?? null,
        ]);

        $producesRelation = $this->upsertRelation($productNode, $parentNode, 'PRODUCES_ORDER', (float) $output->quantity_completed, $order->product?->uom, $order->id, null, $payload['metadata'] ?? null);
        $lotRelation = $this->upsertRelation($parentNode, $childNode, 'PRODUCES_LOT', (float) $output->quantity_completed, $order->product?->uom, $order->id, null, $payload['metadata'] ?? null);
        $lotProductRelation = $this->upsertRelation($childNode, $productNode, 'IDENTIFIES_PRODUCT', (float) $output->quantity_completed, $order->product?->uom, $order->id, null, $payload['metadata'] ?? null);

        return [
            'product_node' => $productNode->toArray(),
            'parent_node' => $parentNode->toArray(),
            'child_node' => $childNode->toArray(),
            'relations' => [
                $producesRelation->toArray(),
                $lotRelation->toArray(),
                $lotProductRelation->toArray(),
            ],
        ];
    }

    public function linkMaterialConsumption(int $consumptionId): array
    {
        $consumption = ProductionOrderMaterialConsumption::query()
            ->with(['productionOrder', 'product'])
            ->findOrFail($consumptionId);

        $order = $consumption->productionOrder;

        $materialProductNode = $this->upsertProductNode((int) $consumption->product_id, (int) $consumption->warehouse_id, [
            'source_reference' => $consumption->product?->sku,
        ]);

        $parentNode = $this->upsertNode('PRODUCTION_ORDER', $order->id, [
            'source_reference' => $order->order_number,
            'product_id' => $order->product_id,
            'warehouse_id' => $order->warehouse_id,
        ]);

        $childNode = $this->upsertNode('MATERIAL', $consumption->id, [
            'source_reference' => $consumption->lot_number ?? $consumption->product?->sku,
            'product_id' => $consumption->product_id,
            'warehouse_id' => $consumption->warehouse_id,
            'metadata' => $consumption->metadata,
        ]);

        $consumesRelation = $this->upsertRelation($parentNode, $materialProductNode, 'CONSUMES', (float) $consumption->quantity_consumed, $consumption->product?->uom, $order->id, null, $consumption->metadata ?? null);
        $materialNodeRelation = $this->upsertRelation($materialProductNode, $childNode, 'HAS_CONSUMPTION_RECORD', (float) $consumption->quantity_consumed, $consumption->product?->uom, $order->id, null, $consumption->metadata ?? null);

        return [
            'product_node' => $materialProductNode->toArray(),
            'parent_node' => $parentNode->toArray(),
            'child_node' => $childNode->toArray(),
            'relations' => [
                $consumesRelation->toArray(),
                $materialNodeRelation->toArray(),
            ],
        ];
    }

    public function linkLotSerial(string $lotNumber, string $serialNumber, int $productId, int $warehouseId): array
    {
        $lot = InventoryLot::query()
            ->where('lot_number', $lotNumber)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->firstOrFail();

        $serial = InventorySerial::query()
            ->where('serial_number', $serialNumber)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->firstOrFail();

        $productNode = $this->upsertProductNode($productId, $warehouseId, [
            'source_reference' => $serial->product?->sku,
        ]);

        $lotNode = $this->upsertNode('LOT', $lot->id, [
            'source_reference' => $lotNumber,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);

        $serialNode = $this->upsertNode('SERIAL', $serial->id, [
            'source_reference' => $serialNumber,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);

        $relation = $this->upsertRelation($lotNode, $serialNode, 'DERIVES', 1, null, null, null, null);
        $productRelation = $this->upsertRelation($lotNode, $productNode, 'IDENTIFIES_PRODUCT', 1, null, null, null, null);

        return [
            'product_node' => $productNode->toArray(),
            'parent_node' => $lotNode->toArray(),
            'child_node' => $serialNode->toArray(),
            'relations' => [
                $relation->toArray(),
                $productRelation->toArray(),
            ],
        ];
    }

    public function traceForward(string $nodeType, int $sourceId): array
    {
        $node = $this->resolveNode($nodeType, $sourceId);
        $edges = $this->fetchRecursiveTrace($node, 'forward', 10);

        return [
            'root_node' => $node->toArray(),
            'edges' => $edges,
        ];
    }

    public function traceBackward(string $nodeType, int $sourceId): array
    {
        $node = $this->resolveNode($nodeType, $sourceId);
        $edges = $this->fetchRecursiveTrace($node, 'backward', 10);

        return [
            'root_node' => $node->toArray(),
            'edges' => $edges,
        ];
    }

    private function resolveNode(string $nodeType, int $sourceId): GenealogyNode
    {
        return GenealogyNode::query()
            ->where('node_type', strtoupper($nodeType))
            ->where('source_id', $sourceId)
            ->firstOrFail();
    }

    private function upsertNode(string $nodeType, int $sourceId, array $attributes): GenealogyNode
    {
        return GenealogyNode::query()->updateOrCreate(
            [
                'node_type' => strtoupper($nodeType),
                'source_id' => $sourceId,
            ],
            [
                'source_reference' => $attributes['source_reference'] ?? null,
                'product_id' => $attributes['product_id'] ?? null,
                'warehouse_id' => $attributes['warehouse_id'] ?? null,
                'metadata' => $attributes['metadata'] ?? null,
            ]
        );
    }

    private function upsertProductNode(int $productId, ?int $warehouseId, array $attributes = []): GenealogyNode
    {
        return GenealogyNode::query()->updateOrCreate(
            [
                'node_type' => 'PRODUCT',
                'source_id' => $productId,
            ],
            [
                'source_reference' => $attributes['source_reference'] ?? null,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'metadata' => $attributes['metadata'] ?? null,
            ]
        );
    }

    private function upsertRelation(
        GenealogyNode $parent,
        GenealogyNode $child,
        string $relationType,
        ?float $quantity,
        ?string $uom,
        ?int $productionOrderId,
        ?int $stockMovementId,
        mixed $metadata
    ): GenealogyRelation {
        return GenealogyRelation::query()->updateOrCreate(
            [
                'parent_node_id' => $parent->id,
                'child_node_id' => $child->id,
                'relation_type' => strtoupper($relationType),
            ],
            [
                'quantity' => $quantity,
                'uom' => $uom,
                'production_order_id' => $productionOrderId,
                'stock_movement_id' => $stockMovementId,
                'metadata' => $metadata,
            ]
        );
    }

    private function fetchRecursiveTrace(GenealogyNode $node, string $direction, int $maxDepth): array
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new DomainException('Genealogy trace requires the MySQL driver for recursive CTE support', 422);
        }

        $companyId = (int) app(\App\Shared\Infrastructure\Tenancy\TenantContext::class)->companyId();
        $direction = strtolower($direction);

        $rows = DB::select(
            <<<'SQL'
            WITH RECURSIVE genealogy_walk AS (
                SELECT
                    r.id,
                    r.parent_node_id,
                    r.child_node_id,
                    r.relation_type,
                    r.quantity,
                    r.uom,
                    r.production_order_id,
                    r.stock_movement_id,
                    0 AS depth,
                    CAST(CONCAT(r.parent_node_id, ',', r.child_node_id) AS CHAR(4000)) AS path
                FROM genealogy_relations r
                INNER JOIN genealogy_nodes start_node ON start_node.id = :start_node_id
                WHERE r.company_id = :company_id
                    AND (
                        (:direction = 'forward' AND r.parent_node_id = start_node.id)
                        OR (:direction = 'backward' AND r.child_node_id = start_node.id)
                    )

                UNION ALL

                SELECT
                    r.id,
                    r.parent_node_id,
                    r.child_node_id,
                    r.relation_type,
                    r.quantity,
                    r.uom,
                    r.production_order_id,
                    r.stock_movement_id,
                    genealogy_walk.depth + 1 AS depth,
                    CONCAT(genealogy_walk.path, ',', CASE WHEN :direction = 'forward' THEN r.child_node_id ELSE r.parent_node_id END) AS path
                FROM genealogy_walk
                INNER JOIN genealogy_relations r ON r.company_id = :company_id
                    AND (
                        (:direction = 'forward' AND r.parent_node_id = genealogy_walk.child_node_id)
                        OR (:direction = 'backward' AND r.child_node_id = genealogy_walk.parent_node_id)
                    )
                WHERE genealogy_walk.depth < :max_depth
                    AND FIND_IN_SET(
                        CASE WHEN :direction = 'forward' THEN r.child_node_id ELSE r.parent_node_id END,
                        genealogy_walk.path
                    ) = 0
            )
            SELECT
                genealogy_walk.*,
                parent_node.node_type AS parent_node_type,
                parent_node.source_id AS parent_source_id,
                parent_node.source_reference AS parent_source_reference,
                parent_node.product_id AS parent_product_id,
                parent_node.warehouse_id AS parent_warehouse_id,
                child_node.node_type AS child_node_type,
                child_node.source_id AS child_source_id,
                child_node.source_reference AS child_source_reference,
                child_node.product_id AS child_product_id,
                child_node.warehouse_id AS child_warehouse_id
            FROM genealogy_walk
            INNER JOIN genealogy_nodes parent_node ON parent_node.id = genealogy_walk.parent_node_id
            INNER JOIN genealogy_nodes child_node ON child_node.id = genealogy_walk.child_node_id
            ORDER BY genealogy_walk.depth, genealogy_walk.id
            SQL,
            [
                'start_node_id' => $node->id,
                'company_id' => $companyId,
                'direction' => $direction,
                'max_depth' => $maxDepth,
            ]
        );

        return array_map(static function (object $row): array {
            return [
                'id' => (int) $row->id,
                'parent_node' => [
                    'id' => (int) $row->parent_node_id,
                    'node_type' => (string) $row->parent_node_type,
                    'source_id' => (int) $row->parent_source_id,
                    'source_reference' => $row->parent_source_reference,
                    'product_id' => $row->parent_product_id ? (int) $row->parent_product_id : null,
                    'warehouse_id' => $row->parent_warehouse_id ? (int) $row->parent_warehouse_id : null,
                ],
                'child_node' => [
                    'id' => (int) $row->child_node_id,
                    'node_type' => (string) $row->child_node_type,
                    'source_id' => (int) $row->child_source_id,
                    'source_reference' => $row->child_source_reference,
                    'product_id' => $row->child_product_id ? (int) $row->child_product_id : null,
                    'warehouse_id' => $row->child_warehouse_id ? (int) $row->child_warehouse_id : null,
                ],
                'relation_type' => (string) $row->relation_type,
                'quantity' => $row->quantity !== null ? (float) $row->quantity : null,
                'uom' => $row->uom,
                'production_order_id' => $row->production_order_id !== null ? (int) $row->production_order_id : null,
                'stock_movement_id' => $row->stock_movement_id !== null ? (int) $row->stock_movement_id : null,
                'depth' => (int) $row->depth,
            ];
        }, $rows);
    }

    private function serializeEdge(GenealogyRelation $relation): array
    {
        return [
            'id' => (int) $relation->id,
            'parent_node' => [
                'id' => (int) $relation->parentNode->id,
                'node_type' => $relation->parentNode->node_type,
                'source_id' => (int) $relation->parentNode->source_id,
                'source_reference' => $relation->parentNode->source_reference,
            ],
            'child_node' => [
                'id' => (int) $relation->childNode->id,
                'node_type' => $relation->childNode->node_type,
                'source_id' => (int) $relation->childNode->source_id,
                'source_reference' => $relation->childNode->source_reference,
            ],
            'relation_type' => $relation->relation_type,
            'quantity' => $relation->quantity,
            'uom' => $relation->uom,
            'production_order_id' => $relation->production_order_id,
            'stock_movement_id' => $relation->stock_movement_id,
            'metadata' => $relation->metadata,
        ];
    }
}