<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Production\Application\Services\MaterialConsumptionService;
use App\Modules\Production\Application\Services\ProductionOrderService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOperationOutput;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Sales\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Presentation\Exceptions\DomainException;
use App\Support\Duration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ProductionOrderController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'production-orders.read';

    private const CREATE_PERMISSION = 'production-orders.create';

    private const RELEASE_PERMISSION = 'production-orders.release';

    private const PARTIAL_PERMISSION = 'production-orders.partial';

    private const COMPLETE_PERMISSION = 'production-orders.complete';

    private const CONSUMPTION_CREATE_PERMISSION = 'production-orders.consumption.create';

    public function __construct(
        private readonly ProductionOrderService $orderService,
        private readonly MaterialConsumptionService $consumptionService,
    ) {}

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'order_number');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($status !== '' && ! in_array($status, ['DRAFT', 'RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED', 'COMPLETED', 'CANCELLED'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['order_number', 'sales_order', 'product', 'warehouse', 'quantity_planned', 'quantity_produced', 'quantity_scrapped', 'status'], true), 404);

        $ordersQuery = ProductionOrder::query()
            ->with(['product:id,description,sku', 'warehouse:id,name,code'])
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('product', static fn (Builder $productQuery) => $productQuery->where('description', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
                });
            });

        if ($sort === 'sales_order') {
            $ordersQuery->orderBy('source_reference_id', $direction);
        } elseif ($sort === 'product') {
            $ordersQuery->orderBy(Product::query()->select('sku')->whereColumn('products.id', 'production_orders.product_id'), $direction);
        } elseif ($sort === 'warehouse') {
            $ordersQuery->orderBy(Warehouse::query()->select('name')->whereColumn('warehouses.id', 'production_orders.warehouse_id'), $direction);
        } else {
            $ordersQuery->orderBy($sort, $direction);
        }

        $orders = $ordersQuery
            ->orderBy('id', $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.production.orders.search', compact('orders', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);
        $referenceDate = now()->toDateString();

        $selectedProductId = (int) ($request->old('product_id') ?? $request->query('product_id', 0));
        $selectedProduct = $selectedProductId > 0
            ? Product::query()
                ->where('is_active', true)
                ->whereHas('bomHeaders', fn (Builder $builder) => $this->applyEligibleBomFilter($builder, $referenceDate))
                ->find($selectedProductId, ['id', 'sku', 'description'])
            : null;
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);
        $saleId = (int) $request->query('sale_id', 0);
        $saleLineId = (int) $request->query('sale_line_id', 0);
        $saleLine = $saleId > 0 && $saleLineId > 0
            ? SaleLine::query()->whereKey($saleLineId)->where('sale_id', $saleId)->first()
            : null;
        $creationContext = $saleLine !== null && (int) $saleLine->product_id > 0
            ? [
                'sale_id' => $saleId,
                'sale_line_id' => $saleLineId,
                'root_product_id' => (int) $saleLine->product_id,
                'dependency_level' => max(0, (int) $request->query('dependency_level', 0)),
            ]
            : null;
        $initialValues = [
            'warehouse_id' => (int) $request->query('warehouse_id', 0),
            'quantity_planned' => max(0.001, (float) $request->query('quantity_planned', 1)),
            'scheduled_start_date' => (string) $request->query('scheduled_start_date', ''),
            'scheduled_end_date' => (string) $request->query('scheduled_end_date', ''),
        ];

        return view('client.production.orders.form', compact('selectedProduct', 'warehouses', 'company', 'creationContext', 'initialValues'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'quantity_planned' => ['required', 'numeric', 'gt:0'],
            'scheduled_start_date' => ['nullable', 'date'],
            'scheduled_end_date' => ['nullable', 'date', 'after_or_equal:scheduled_start_date'],
            'sale_id' => ['nullable', 'integer'],
            'sale_line_id' => ['nullable', 'integer'],
            'dependency_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $saleLine = null;

        if ((int) ($data['sale_id'] ?? 0) > 0 || (int) ($data['sale_line_id'] ?? 0) > 0) {
            $saleLine = SaleLine::query()
                ->whereKey((int) ($data['sale_line_id'] ?? 0))
                ->where('sale_id', (int) ($data['sale_id'] ?? 0))
                ->first();

            if (! $saleLine instanceof SaleLine) {
                return redirect()->back()->withInput()->withErrors([
                    'product_id' => __('production.orders.invalid_sale_context'),
                ]);
            }
        }

        try {
            if ($saleLine instanceof SaleLine) {
                $created = $this->orderService->createForSale(array_merge($data, [
                    'source_reference_id' => (int) $data['sale_id'],
                    'source_reference_type' => 'sale',
                    'metadata' => [
                        'sale_id' => (int) $data['sale_id'],
                        'sale_line_id' => (int) $saleLine->id,
                        'root_product_id' => (int) $saleLine->product_id,
                        'dependency_level' => (int) ($data['dependency_level'] ?? 0),
                        'allocation_scope' => sprintf('sale:%d', (int) $data['sale_id']),
                    ],
                ]), $request->user()?->id);
            } else {
                $created = $this->orderService->createManual($data, $request->user()?->id);
            }
        } catch (DomainException $exception) {
            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->withErrors($this->productionOrderCreateErrorBag($exception));
        }

        $orderId = (int) ($created['id'] ?? 0);

        if ($orderId <= 0) {
            return redirect()->route('production.orders.index')->withErrors([
                'production' => __('messages.production_order_create_failed'),
            ]);
        }

        return redirect()->route('production.orders.show', $orderId)->with('status', __('production.orders.created'));
    }

    /**
     * @return array<string, string>
     */
    private function productionOrderCreateErrorBag(DomainException $exception): array
    {
        $message = $exception->getMessage();
        $messageLower = mb_strtolower($message);
        $details = $exception->details();

        if ($details !== []) {
            foreach ($details as $field => $fieldMessages) {
                if (is_array($fieldMessages) && $fieldMessages !== []) {
                    return [(string) $field => (string) $fieldMessages[0]];
                }

                return [(string) $field => (string) $fieldMessages];
            }
        }

        if (
            $exception->status() === 404
            || str_contains($message, 'No BOM version found for product and reference date')
        ) {
            return ['product_id' => __('messages.production_order_missing_bom_version')];
        }

        if (str_contains($message, 'BOM explosion recursive CTE is implemented for MySQL driver.')) {
            return ['production' => __('messages.production_order_mysql_required')];
        }

        if (str_contains($message, 'Tenant context is required to freeze BOM snapshot')) {
            return ['production' => __('messages.production_order_tenant_context_required')];
        }

        if (str_contains($messageLower, 'warehouse')) {
            return ['warehouse_id' => $message !== '' ? $message : __('messages.production_order_create_failed')];
        }

        if (str_contains($messageLower, 'product')) {
            return ['product_id' => $message !== '' ? $message : __('messages.production_order_create_failed')];
        }

        return ['production' => $message !== '' ? $message : __('messages.production_order_create_failed')];
    }

    public function show(Request $request, ProductionOrder $order): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $order->load([
            'product:id,sku,description',
            'warehouse:id,code,name',
            'outputs' => static fn ($query) => $query->orderByDesc('id')->limit(50),
            'materialConsumptions' => static fn ($query) => $query->orderByDesc('id')->limit(50),
            'materialConsumptions.product:id,sku,description',
            'materialConsumptions.warehouse:id,code,name',
            'snapshot.bomSnapshot.items.componentProduct:id,sku,description',
        ]);

        $consumedByProduct = $order->materialConsumptions
            ->filter(static fn ($consumption) => ! $consumption->reversed_by_movement_id)
            ->groupBy('product_id')
            ->map(static fn ($items) => round($items->sum('quantity_consumed'), 6));
        $plannedMaterials = collect($order->snapshot?->bomSnapshot?->items ?? [])
            ->filter(static fn ($item) => $item->componentProduct)
            ->groupBy('component_product_id')
            ->map(static function ($items) use ($consumedByProduct): array {
                $item = $items->first();
                $planned = (float) $items->sum('quantity_required');
                $consumed = (float) ($consumedByProduct[(int) $item->component_product_id] ?? 0);

                return ['component' => $item, 'planned_quantity' => $planned, 'consumed_quantity' => $consumed, 'remaining_quantity' => max(0, round($planned - $consumed, 6))];
            })->values();

        $additionalConsumptions = $order->materialConsumptions
            ->filter(static fn ($consumption) => (bool) data_get($consumption->metadata, 'is_unplanned'))
            ->groupBy('product_id')
            ->map(static fn ($items) => ['product' => $items->first()->product, 'consumed_quantity' => round($items->sum('quantity_consumed'), 6)])
            ->values();

        $selectedConsumptionProductId = (int) ($request->old('product_id') ?? 0);
        $selectedConsumptionProduct = $selectedConsumptionProductId > 0
            ? Product::query()->where('is_active', true)->find($selectedConsumptionProductId, ['id', 'sku', 'description'])
            : null;
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('client.production.orders.show', compact('order', 'selectedConsumptionProduct', 'warehouses', 'company', 'plannedMaterials', 'additionalConsumptions'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, [
            self::READ_PERMISSION,
            self::CREATE_PERMISSION,
            self::PARTIAL_PERMISSION,
            self::CONSUMPTION_CREATE_PERMISSION,
            'routing-versions.read',
            'routing-versions.create',
            'routing-versions.update',
        ]);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $referenceDate = now()->toDateString();

        $query = Product::query()
            ->where('is_active', true)
            ->select(['id', 'sku', 'description'])
            ->orderBy('sku');

        if (! $request->boolean('all')) {
            $query->whereHas('bomHeaders', fn (Builder $builder) => $this->applyEligibleBomFilter($builder, $referenceDate));
        }

        if ($term !== '') {
            $query->where(function (Builder $inner) use ($term): void {
                $inner
                    ->where('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate($perPage, ['id', 'sku', 'description'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(fn (Product $product): array => [
                'id' => $product->id,
                'text' => sprintf('%s - %s', $product->sku, $product->description ?? '—'),
            ])->values(),
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    private function applyEligibleBomFilter(Builder $query, string $referenceDate): void
    {
        $query
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $referenceDate)
            ->where(static function (Builder $builder) use ($referenceDate): void {
                $builder
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $referenceDate);
            });
    }

    public function release(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RELEASE_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $this->orderService->release((int) $order->id, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', __('production.orders.released'));
    }

    public function complete(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::COMPLETE_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $this->orderService->complete((int) $order->id, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', __('production.orders.completed'));
    }

    public function recordOutput(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PARTIAL_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $request->merge([
            'setup_time_minutes' => Duration::minutesFromInput($request->input('setup_time_minutes')),
            'process_time_minutes' => Duration::minutesFromInput($request->input('process_time_minutes')),
        ]);

        $data = $request->validate([
            'quantity_completed' => ['required', 'numeric', 'min:0'],
            'quantity_scrapped' => ['nullable', 'numeric', 'min:0'],
            'operation_no' => ['nullable', 'integer', 'min:1'],
            'work_center_id' => ['nullable', 'integer'],
            'setup_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'process_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'inspection_status' => ['nullable', 'string', 'in:APPROVED,REJECTED,PENDING'],
            'inspection_notes' => ['nullable', 'string', 'max:2000'],
            'lot_number' => ['nullable', 'string', 'max:120'],
        ]);

        $data['quantity_scrapped'] = (float) ($data['quantity_scrapped'] ?? 0);
        $data['setup_time_minutes'] = (float) ($data['setup_time_minutes'] ?? 0);
        $data['process_time_minutes'] = (float) ($data['process_time_minutes'] ?? 0);
        $data['inspection_status'] = (string) ($data['inspection_status'] ?? 'APPROVED');

        if ((float) $data['quantity_completed'] <= 0 && (float) $data['quantity_scrapped'] <= 0) {
            return redirect()->route('production.orders.show', $order)->withErrors(['output' => __('production.orders.posting_required')]);
        }

        $this->orderService->partialProduction((int) $order->id, $data, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', __('production.orders.posting_created'));
    }

    public function recordConsumption(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CONSUMPTION_CREATE_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'quantity_consumed' => ['required', 'numeric', 'gt:0'],
            'quantity_scrapped' => ['nullable', 'numeric', 'min:0'],
            'reference_bom_component_id' => ['nullable', 'integer'],
            'allow_unplanned' => ['nullable', 'boolean'],
            'lot_number' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['allow_unplanned'] = (bool) ($data['allow_unplanned'] ?? false);

        $this->consumptionService->record((int) $order->id, $data, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', __('production.orders.consumption_created'));
    }

    public function updateInspection(Request $request, ProductionOrder $order, ProductionOperationOutput $output): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PARTIAL_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);
        abort_unless((int) $output->production_order_id === (int) $order->id, 404);

        $data = $request->validate([
            'inspection_status' => ['required', 'string', 'in:APPROVED,REJECTED,PENDING'],
            'inspection_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $output->inspection_status = Str::upper((string) $data['inspection_status']);
        $output->inspection_notes = $data['inspection_notes'] ?? null;
        $output->inspected_at = now();
        $output->save();

        return redirect()->route('production.orders.show', $order)->with('status', __('production.orders.inspection_updated'));
    }
}
