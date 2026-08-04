<?php

declare(strict_types=1);

namespace App\Modules\Bom\Infrastructure\Persistence\Repositories;

use App\Modules\Bom\Domain\Repositories\BomExplosionRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class MySqlBomExplosionRepository implements BomExplosionRepository
{
    public function explode(
        int $productId,
        string $referenceDate,
        ?int $versionNumber = null,
        int $maxDepth = 100
    ): array {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'mysql') {
            throw new RuntimeException('BOM explosion recursive CTE is implemented for MySQL driver.');
        }

        $companyId = (int) app(\App\Shared\Infrastructure\Tenancy\TenantContext::class)->companyId();

        $rootHeader = $this->resolveRootHeader($companyId, $productId, $referenceDate, $versionNumber);

        if (! $rootHeader) {
            return [
                'root_bom_header' => null,
                'exploded_materials' => [],
                'aggregated_quantities' => [],
                'has_cycle' => false,
            ];
        }

        $rows = DB::select(
            <<<'SQL'
            WITH RECURSIVE bom_tree AS (
                SELECT
                    bh.id AS bom_header_id,
                    bh.version_number AS bom_version_number,
                    bh.product_id AS parent_product_id,
                    bi.component_product_id,
                    bi.line_no,
                    bi.quantity_per,
                    bi.quantity_per AS quantity_required,
                    bi.quantity_per AS quantity_accumulated,
                    1 AS level,
                    CAST(CONCAT(bh.product_id, ',', bi.component_product_id) AS CHAR(4000)) AS path,
                    0 AS is_cycle
                FROM bom_headers bh
                INNER JOIN bom_items bi ON bi.bom_header_id = bh.id
                WHERE bh.id = :root_header_id

                UNION ALL

                SELECT
                    child_bh.id AS bom_header_id,
                    child_bh.version_number AS bom_version_number,
                    bt.component_product_id AS parent_product_id,
                    child_bi.component_product_id,
                    child_bi.line_no,
                    child_bi.quantity_per,
                    child_bi.quantity_per AS quantity_required,
                    (bt.quantity_accumulated * child_bi.quantity_per) AS quantity_accumulated,
                    bt.level + 1 AS level,
                    CONCAT(bt.path, ',', child_bi.component_product_id) AS path,
                    IF(FIND_IN_SET(child_bi.component_product_id, bt.path) > 0, 1, 0) AS is_cycle
                FROM bom_tree bt
                INNER JOIN bom_headers child_bh ON child_bh.id = (
                    SELECT bh2.id
                    FROM bom_headers bh2
                    WHERE bh2.company_id = :company_id
                        AND bh2.product_id = bt.component_product_id
                        AND bh2.status = 'APPROVED'
                        AND bh2.effective_from <= :reference_date
                        AND (bh2.effective_to IS NULL OR bh2.effective_to >= :reference_date)
                    ORDER BY bh2.effective_from DESC, bh2.version_number DESC
                    LIMIT 1
                )
                INNER JOIN bom_items child_bi ON child_bi.bom_header_id = child_bh.id
                WHERE bt.is_cycle = 0
                    AND bt.level < :max_depth
            )
            SELECT
                bom_header_id,
                bom_version_number,
                parent_product_id,
                component_product_id,
                line_no,
                quantity_per,
                quantity_required,
                quantity_accumulated,
                level,
                path,
                is_cycle
            FROM bom_tree
            ORDER BY level, parent_product_id, line_no
            SQL,
            [
                'root_header_id' => $rootHeader->id,
                'company_id' => $companyId,
                'reference_date' => $referenceDate,
                'max_depth' => $maxDepth,
            ]
        );

        $exploded = array_map(static function (object $row): array {
            return [
                'bom_header_id' => (int) $row->bom_header_id,
                'bom_version_number' => (int) $row->bom_version_number,
                'parent_product_id' => (int) $row->parent_product_id,
                'component_product_id' => (int) $row->component_product_id,
                'line_no' => (int) $row->line_no,
                'quantity_per' => (float) $row->quantity_per,
                'quantity_required' => (float) $row->quantity_required,
                'quantity_accumulated' => (float) $row->quantity_accumulated,
                'level' => (int) $row->level,
                'path' => (string) $row->path,
                'is_cycle' => (bool) $row->is_cycle,
            ];
        }, $rows);

        $aggregated = [];

        foreach ($exploded as $item) {
            if ($item['is_cycle']) {
                continue;
            }

            $componentId = $item['component_product_id'];
            $aggregated[$componentId] = ($aggregated[$componentId] ?? 0) + $item['quantity_accumulated'];
        }

        $aggregatedRows = [];

        foreach ($aggregated as $componentId => $qty) {
            $aggregatedRows[] = [
                'component_product_id' => (int) $componentId,
                'aggregated_quantity' => round((float) $qty, 6),
            ];
        }

        usort($aggregatedRows, static fn (array $a, array $b): int => $a['component_product_id'] <=> $b['component_product_id']);

        $hasCycle = count(array_filter($exploded, static fn (array $item): bool => $item['is_cycle'])) > 0;

        return [
            'root_bom_header' => [
                'id' => (int) $rootHeader->id,
                'product_id' => (int) $rootHeader->product_id,
                'version_number' => (int) $rootHeader->version_number,
                'status' => (string) $rootHeader->status,
                'effective_from' => (string) $rootHeader->effective_from,
                'effective_to' => $rootHeader->effective_to ? (string) $rootHeader->effective_to : null,
            ],
            'exploded_materials' => $exploded,
            'aggregated_quantities' => $aggregatedRows,
            'has_cycle' => $hasCycle,
        ];
    }

    private function resolveRootHeader(
        int $companyId,
        int $productId,
        string $referenceDate,
        ?int $versionNumber
    ): ?object {
        if ($versionNumber !== null) {
            return DB::table('bom_headers')
                ->select(['id', 'product_id', 'version_number', 'status', 'effective_from', 'effective_to'])
                ->where('company_id', $companyId)
                ->where('product_id', $productId)
                ->where('version_number', $versionNumber)
                ->first();
        }

        return DB::table('bom_headers')
            ->select(['id', 'product_id', 'version_number', 'status', 'effective_from', 'effective_to'])
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $referenceDate)
            ->where(static function ($query) use ($referenceDate): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $referenceDate);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('version_number')
            ->first();
    }
}
