<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PlantController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'inventory.plants.read';

    private const CREATE_PERMISSION = 'inventory.plants.create';

    private const UPDATE_PERMISSION = 'inventory.plants.update';

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

        abort_unless(in_array($sort, ['name', 'code', 'timezone', 'is_active', 'created_at'], true), 404);

        $plants = Plant::query()
            ->where('company_id', $company->id)
            ->when($status !== '', static fn (Builder $query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search, $searchId): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('timezone', 'like', "%{$search}%");

                    if ($searchId !== null) {
                        $nested->orWhere('id', $searchId);
                    }
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.inventory.plants.search', compact('plants', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.inventory.plants.form', [
            'plant' => null,
            'company' => $company,
        ]);
    }

    public function show(Request $request, Plant $plant): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        abort_unless((int) $plant->company_id === (int) $company->id, 404);

        return view('client.inventory.plants.show', compact('plant', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validatePlant($request, $company);

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => (string) $data['name'],
            'code' => mb_strtoupper((string) $data['code']),
            'timezone' => (string) $data['timezone'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $audit->record(
            'tenant_inventory_plant.created',
            context: [
                'plant_id' => $plant->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.plants.index')->with('status', __('plant.created'));
    }

    public function edit(Request $request, Plant $plant): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $plant->company_id === (int) $company->id, 404);

        return view('client.inventory.plants.form', compact('plant', 'company'));
    }

    public function update(Request $request, Plant $plant, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $plant->company_id === (int) $company->id, 404);

        $data = $this->validatePlant($request, $company, $plant);

        $plant->fill([
            'name' => (string) $data['name'],
            'code' => mb_strtoupper((string) $data['code']),
            'timezone' => (string) $data['timezone'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $plant->save();

        $audit->record(
            'tenant_inventory_plant.updated',
            context: [
                'plant_id' => $plant->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.plants.index')->with('status', __('plant.updated'));
    }

    public function destroy(Request $request, Plant $plant, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $plant->company_id === (int) $company->id, 404);

        $plantId = $plant->id;
        $plantName = $plant->name;
        $plant->delete();

        $audit->record(
            'tenant_inventory_plant.removed',
            context: [
                'plant_id' => $plantId,
                'plant_name' => $plantName,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.plants.index')->with('status', __('plant.removed'));
    }

    /**
     * @return array{name: string, code: string, timezone: string, is_active?: bool}
     */
    private function validatePlant(Request $request, Company $company, ?Plant $plant = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('plants', 'code')
                    ->where(static fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($plant?->id),
            ],
            'timezone' => ['required', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
