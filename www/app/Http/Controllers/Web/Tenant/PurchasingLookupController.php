<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrderLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PurchasingLookupController extends Controller
{
    use HandlesTenantAuthorization;

    private const PERMISSIONS = [
        'purchasing.requisitions.read',
        'purchasing.requisitions.create',
        'purchasing.requisitions.update',
        'purchasing.quotations.read',
        'purchasing.quotations.create',
        'purchasing.quotations.update',
        'purchasing.orders.read',
        'purchasing.orders.create',
        'purchasing.orders.update',
        'purchasing.receipts.read',
        'purchasing.receipts.create',
        'purchasing.receipts.update',
    ];

    public function suppliers(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, self::PERMISSIONS);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $query = Supplier::query()
            ->where('company_id', $company->id)
            ->select(['id', 'code', 'name'])
            ->orderBy('name');

        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $nested->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate(20, ['id', 'code', 'name'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(static fn (Supplier $supplier): array => [
                'id' => $supplier->id,
                'text' => sprintf('%s - %s', $supplier->code, $supplier->name),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function requisitions(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, self::PERMISSIONS);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $query = PurchaseRequisition::query()
            ->where('company_id', $company->id)
            ->select(['id', 'requisition_number', 'status'])
            ->orderByDesc('id');

        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $nested->where('requisition_number', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate(20, ['id', 'requisition_number', 'status'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(static fn (PurchaseRequisition $requisition): array => [
                'id' => $requisition->id,
                'text' => sprintf('%s [%s]', $requisition->requisition_number, $requisition->status),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, self::PERMISSIONS);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $query = PurchaseOrder::query()
            ->where('company_id', $company->id)
            ->select(['id', 'purchase_order_number', 'status'])
            ->orderByDesc('id');

        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $nested->where('purchase_order_number', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate(20, ['id', 'purchase_order_number', 'status'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(static fn (PurchaseOrder $order): array => [
                'id' => $order->id,
                'text' => sprintf('%s [%s]', $order->purchase_order_number, $order->status),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function warehouses(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, self::PERMISSIONS);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $query = Warehouse::query()
            ->where('company_id', $company->id)
            ->select(['id', 'code', 'name'])
            ->orderBy('code');

        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $nested->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate(20, ['id', 'code', 'name'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(static fn (Warehouse $warehouse): array => [
                'id' => $warehouse->id,
                'text' => sprintf('%s - %s', $warehouse->code, $warehouse->name),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, self::PERMISSIONS);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $query = Product::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->select(['id', 'sku', 'description'])
            ->orderBy('sku');

        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $nested->where('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate(20, ['id', 'sku', 'description'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(static fn (Product $product): array => [
                'id' => $product->id,
                'text' => sprintf('%s - %s', $product->sku, $product->description ?? '—'),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function orderLines(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, self::PERMISSIONS);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $orderId = (int) $request->query('order_id', 0);

        $query = PurchaseOrderLine::query()
            ->where('company_id', $company->id)
            ->with('product:id,sku')
            ->orderByDesc('id');

        if ($orderId > 0) {
            $query->where('purchase_order_id', $orderId);
        }

        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $addedCondition = false;

                if (ctype_digit($term)) {
                    $nested->where('id', (int) $term);
                    $addedCondition = true;
                }

                $method = $addedCondition ? 'orWhereHas' : 'whereHas';

                $nested->{$method}('product', static fn ($productQuery) => $productQuery->where('sku', 'like', "%{$term}%"));
            });
        }

        $paginator = $query->paginate(20, ['id', 'purchase_order_id', 'product_id', 'quantity_ordered', 'quantity_received'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(static fn (PurchaseOrderLine $line): array => [
                'id' => $line->id,
                'text' => sprintf('#%d - %s (%s)', $line->id, $line->product?->sku ?? 'SKU', number_format((float) $line->quantity_ordered, 6, ',', '.')),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }
}
