<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Production\Application\Services\MaterialConsumptionService;
use App\Modules\Production\Application\Services\ProductionOrderService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOutput;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
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
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));

        if ($status !== '' && ! in_array($status, ['DRAFT', 'RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED', 'COMPLETED', 'CANCELLED'], true)) {
            $status = '';
        }

        $orders = ProductionOrder::query()
            ->with(['product:id,description,sku', 'warehouse:id,name,code'])
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('product', static fn (Builder $productQuery) => $productQuery->where('description', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('client.production.orders.search', compact('orders', 'search', 'status', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $products = Product::query()->where('is_active', true)->orderBy('description')->get(['id', 'sku', 'description']);
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('client.production.orders.form', compact('products', 'warehouses', 'company'));
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
        ]);

        $created = $this->orderService->createManual($data, $request->user()?->id);
        $orderId = (int) ($created['id'] ?? 0);

        if ($orderId <= 0) {
            return redirect()->route('production.orders.index')->withErrors(['production' => 'Nao foi possivel criar a ordem de producao.']);
        }

        return redirect()->route('production.orders.show', $orderId)->with('status', 'Ordem de producao criada com sucesso.');
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
        ]);

        $products = Product::query()->where('is_active', true)->orderBy('description')->get(['id', 'sku', 'description']);
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('client.production.orders.show', compact('order', 'products', 'warehouses', 'company'));
    }

    public function release(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RELEASE_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $this->orderService->release((int) $order->id, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', 'Ordem liberada para execucao.');
    }

    public function complete(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::COMPLETE_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

        $this->orderService->complete((int) $order->id, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', 'Ordem concluida com sucesso.');
    }

    public function recordOutput(Request $request, ProductionOrder $order): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PARTIAL_PERMISSION, $company->id);
        abort_unless((int) $order->company_id === (int) $company->id, 404);

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
            return redirect()->route('production.orders.show', $order)->withErrors(['output' => 'Informe quantidade produzida ou refugada.']);
        }

        $this->orderService->partialProduction((int) $order->id, $data, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', 'Apontamento registrado com sucesso.');
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
            'lot_number' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->consumptionService->record((int) $order->id, $data, $request->user()?->id);

        return redirect()->route('production.orders.show', $order)->with('status', 'Consumo registrado com sucesso.');
    }

    public function updateInspection(Request $request, ProductionOrder $order, ProductionOrderOutput $output): RedirectResponse
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

        return redirect()->route('production.orders.show', $order)->with('status', 'Checkpoint de inspecao atualizado.');
    }
}
