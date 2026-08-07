<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Routing\Application\Services\RoutingService;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersion;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Support\Duration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductionRoutingController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'routing-versions.read';
    private const CREATE_PERMISSION = 'routing-versions.create';
    private const UPDATE_PERMISSION = 'routing-versions.update';
    private const APPROVE_PERMISSION = 'routing-versions.approve';
    private const OPERATION_CREATE_PERMISSION = 'routing-operations.create';

    public function __construct(private readonly RoutingService $service)
    {
    }

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));

        if ($status !== '' && ! in_array($status, ['DRAFT', 'APPROVED', 'OBSOLETE'], true)) {
            $status = '';
        }

        $versions = RoutingVersion::query()
            ->with(['product:id,sku,description'])
            ->withCount('operations')
            ->when($status !== '', static fn ($query) => $query->where('status', $status))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('version_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('product', static fn ($productQuery) => $productQuery
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('client.production.routing.search', compact('company', 'versions', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $selectedProductId = (int) ($request->old('product_id') ?? 0);
        $selectedProduct = $selectedProductId > 0
            ? Product::query()->where('is_active', true)->find($selectedProductId, ['id', 'sku', 'description'])
            : null;

        return view('client.production.routing.form', [
            'company' => $company,
            'version' => null,
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'version_number' => ['required', 'integer', 'min:1'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $created = $this->service->createVersion([
            ...$data,
            'status' => 'DRAFT',
        ]);

        return redirect()->route('production.routing.show', (int) ($created['id'] ?? 0))->with('status', 'Versao de roteamento criada.');
    }

    public function show(Request $request, RoutingVersion $version): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        abort_unless((int) $version->company_id === (int) $company->id, 404);

        $version->load(['product:id,sku,description', 'operations.workCenter:id,code,name']);
        $workCenters = WorkCenter::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);

        return view('client.production.routing.show', compact('company', 'version', 'workCenters'));
    }

    public function edit(Request $request, RoutingVersion $version): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, [self::UPDATE_PERMISSION, self::CREATE_PERMISSION]);
        abort_unless((int) $version->company_id === (int) $company->id, 404);

        $selectedProductId = (int) ($request->old('product_id') ?? $version->product_id ?? 0);
        $selectedProduct = $selectedProductId > 0
            ? Product::query()->where('is_active', true)->find($selectedProductId, ['id', 'sku', 'description'])
            : null;

        return view('client.production.routing.form', [
            'company' => $company,
            'version' => $version,
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function update(Request $request, RoutingVersion $version): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, [self::UPDATE_PERMISSION, self::CREATE_PERMISSION]);
        abort_unless((int) $version->company_id === (int) $company->id, 404);

        if ($version->status !== 'DRAFT') {
            return redirect()->route('production.routing.show', $version)->withErrors(['routing' => 'Apenas versoes DRAFT podem ser editadas.']);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'version_number' => ['required', 'integer', 'min:1'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $version->fill($data);
        $version->save();

        return redirect()->route('production.routing.show', $version)->with('status', 'Versao de roteamento atualizada.');
    }

    public function storeOperation(Request $request, RoutingVersion $version): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::OPERATION_CREATE_PERMISSION, $company->id);
        abort_unless((int) $version->company_id === (int) $company->id, 404);

        $request->merge([
            'setup_time_minutes' => Duration::minutesFromInput($request->input('setup_time_minutes')),
            'runtime_minutes' => Duration::minutesFromInput($request->input('runtime_minutes')),
            'queue_time_minutes' => Duration::minutesFromInput($request->input('queue_time_minutes')),
            'move_time_minutes' => Duration::minutesFromInput($request->input('move_time_minutes')),
        ]);

        $data = $request->validate([
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'operation_no' => ['required', 'integer', 'min:1'],
            'operation_code' => ['required', 'string', 'max:50'],
            'operation_name' => ['required', 'string', 'max:150'],
            'sequence' => ['required', 'integer', 'min:1'],
            'setup_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'runtime_minutes' => ['nullable', 'numeric', 'min:0'],
            'queue_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'move_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'is_outsourced' => ['nullable', 'boolean'],
        ]);

        $this->service->addOperation((int) $version->id, $data);

        return redirect()->route('production.routing.show', $version)->with('status', 'Operacao adicionada ao roteamento.');
    }

    public function approve(Request $request, RoutingVersion $version): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::APPROVE_PERMISSION, $company->id);
        abort_unless((int) $version->company_id === (int) $company->id, 404);

        $data = $request->validate([
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        $this->service->approveVersion((int) $version->id, $data, $request->user()?->id);

        return redirect()->route('production.routing.show', $version)->with('status', 'Roteamento aprovado com sucesso.');
    }
}
