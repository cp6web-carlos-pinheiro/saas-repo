<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WarehouseController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'inventory.warehouses.read';

    private const CREATE_PERMISSION = 'inventory.warehouses.create';

    private const UPDATE_PERMISSION = 'inventory.warehouses.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchId = ctype_digit($search) ? (int) $search : null;
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['name', 'code', 'is_active', 'created_at'], true), 404);

        $warehouses = Warehouse::query()
            ->with('plant:id,name,code')
            ->where('company_id', $company->id)
            ->when($status !== '', static fn (Builder $query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search, $searchId): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");

                    if ($searchId !== null) {
                        $nested->orWhere('id', $searchId);
                    }
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.inventory.warehouses.search', compact('warehouses', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.inventory.warehouses.form', [
            'warehouse' => null,
            'company' => $company,
            'plants' => $this->plantOptions($company),
        ]);
    }

    public function show(Request $request, Warehouse $warehouse): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        abort_unless((int) $warehouse->company_id === (int) $company->id, 404);

        $warehouse->load('plant:id,name,code');

        return view('client.inventory.warehouses.show', compact('warehouse', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateWarehouse($request, $company);

        $warehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => (int) $data['plant_id'],
            'name' => (string) $data['name'],
            'code' => $this->generateWarehouseCode($company->id, (string) $data['name']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $audit->record(
            'tenant_inventory_warehouse.created',
            context: [
                'warehouse_id' => $warehouse->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.warehouses.index')->with('status', __('warehouse.created'));
    }

    public function edit(Request $request, Warehouse $warehouse): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $warehouse->company_id === (int) $company->id, 404);

        return view('client.inventory.warehouses.form', [
            'warehouse' => $warehouse,
            'company' => $company,
            'plants' => $this->plantOptions($company),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $warehouse->company_id === (int) $company->id, 404);

        $data = $this->validateWarehouse($request, $company, $warehouse);

        $warehouse->fill([
            'plant_id' => (int) $data['plant_id'],
            'name' => (string) $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $warehouse->save();

        $audit->record(
            'tenant_inventory_warehouse.updated',
            context: [
                'warehouse_id' => $warehouse->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.warehouses.index')->with('status', __('warehouse.updated'));
    }

    public function destroy(Request $request, Warehouse $warehouse, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $warehouse->company_id === (int) $company->id, 404);

        $warehouseId = $warehouse->id;
        $warehouseName = $warehouse->name;
        $warehouse->delete();

        $audit->record(
            'tenant_inventory_warehouse.removed',
            context: [
                'warehouse_id' => $warehouseId,
                'warehouse_name' => $warehouseName,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.warehouses.index')->with('status', __('warehouse.removed'));
    }

    /**
     * @return array{name: string, plant_id: int|string, is_active?: bool}
     */
    private function validateWarehouse(Request $request, Company $company, ?Warehouse $warehouse = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'plant_id' => [
                'required',
                'integer',
                Rule::exists('plants', 'id')->where(static fn ($query) => $query->where('company_id', $company->id)),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function generateWarehouseCode(int $companyId, string $name): string
    {
        $base = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->value();

        if ($base === '') {
            $base = 'WAREHOUSE';
        }

        $base = mb_substr($base, 0, 44);
        $candidate = $base;
        $counter = 2;

        while (Warehouse::query()->where('company_id', $companyId)->where('code', $candidate)->exists()) {
            $suffix = '-'.$counter;
            $candidate = mb_substr($base, 0, max(1, 50 - mb_strlen($suffix))).$suffix;
            $counter++;
        }

        return $candidate;
    }

    /**
     * @return array<int, string>
     */
    private function plantOptions(Company $company): array
    {
        return Plant::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->mapWithKeys(static fn (Plant $plant): array => [$plant->id => sprintf('%s - %s', $plant->code, $plant->name)])
            ->all();
    }
}
