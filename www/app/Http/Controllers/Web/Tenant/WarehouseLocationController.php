<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Modules\Tenant\Infrastructure\Persistence\Models\WarehouseLocation;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WarehouseLocationController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'admin-data.warehouse-locations.read';

    private const CREATE_PERMISSION = 'admin-data.warehouse-locations.create';

    private const UPDATE_PERMISSION = 'admin-data.warehouse-locations.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['name', 'code', 'is_active', 'created_at'], true), 404);

        $locations = WarehouseLocation::query()
            ->with('warehouse:id,name,code')
            ->where('company_id', $company->id)
            ->when($status !== '', static fn (Builder $query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.inventory.warehouse-locations.search', compact('locations', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.inventory.warehouse-locations.form', [
            'location' => null,
            'company' => $company,
            'warehouses' => $this->warehouseOptions($company),
        ]);
    }

    public function show(Request $request, WarehouseLocation $location): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        abort_unless((int) $location->company_id === (int) $company->id, 404);

        $location->load('warehouse:id,name,code');

        return view('client.inventory.warehouse-locations.show', compact('location', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateLocation($request, $company);

        $location = WarehouseLocation::query()->create([
            'company_id' => $company->id,
            'warehouse_id' => (int) $data['warehouse_id'],
            'name' => (string) $data['name'],
            'code' => mb_strtoupper((string) $data['code']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $audit->record(
            'tenant_inventory_warehouse_location.created',
            context: [
                'warehouse_location_id' => $location->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.warehouse-locations.index')->with('status', __('warehouse_location.created'));
    }

    public function edit(Request $request, WarehouseLocation $location): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $location->company_id === (int) $company->id, 404);

        return view('client.inventory.warehouse-locations.form', [
            'location' => $location,
            'company' => $company,
            'warehouses' => $this->warehouseOptions($company),
        ]);
    }

    public function update(Request $request, WarehouseLocation $location, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $location->company_id === (int) $company->id, 404);

        $data = $this->validateLocation($request, $company, $location);

        $location->fill([
            'warehouse_id' => (int) $data['warehouse_id'],
            'name' => (string) $data['name'],
            'code' => mb_strtoupper((string) $data['code']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $location->save();

        $audit->record(
            'tenant_inventory_warehouse_location.updated',
            context: [
                'warehouse_location_id' => $location->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.warehouse-locations.index')->with('status', __('warehouse_location.updated'));
    }

    public function destroy(Request $request, WarehouseLocation $location, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $location->company_id === (int) $company->id, 404);

        $locationId = $location->id;
        $locationCode = $location->code;
        $location->delete();

        $audit->record(
            'tenant_inventory_warehouse_location.removed',
            context: [
                'warehouse_location_id' => $locationId,
                'warehouse_location_code' => $locationCode,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.warehouse-locations.index')->with('status', __('warehouse_location.removed'));
    }

    /**
     * @return array{name: string, code: string, warehouse_id: int|string, is_active?: bool}
     */
    private function validateLocation(Request $request, Company $company, ?WarehouseLocation $location = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouse_locations', 'code')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('warehouse_id', (int) $request->input('warehouse_id')))
                    ->ignore($location?->id),
            ],
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(static fn ($query) => $query->where('company_id', $company->id)),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function warehouseOptions(Company $company): array
    {
        return Warehouse::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->mapWithKeys(static fn (Warehouse $warehouse): array => [$warehouse->id => sprintf('%s - %s', $warehouse->code, $warehouse->name)])
            ->all();
    }
}
