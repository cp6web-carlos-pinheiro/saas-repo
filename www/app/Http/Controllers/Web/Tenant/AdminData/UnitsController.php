<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant\AdminData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class UnitsController extends Controller
{
    use HandlesTenantAuthorization;

    private const PERMISSION = 'admin-data.units';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.read', $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['id', 'name', 'code', 'company_id', 'is_active', 'created_at'], true), 404);

        $units = Unit::query()
            ->when($status !== '', static fn (Builder $query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.admin-data.units.search', compact('units', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.create', $company->id);

        return view('client.admin-data.units.form', [
            'unit' => null,
            'company' => $company,
        ]);
    }

    public function show(Request $request, Unit $unit): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.read', $company->id);

        abort_unless($this->belongsToTenantOrIsGlobal($unit, $company->id), 404);

        return view('client.admin-data.units.show', compact('unit', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.create', $company->id);

        $data = $this->validateUnit($request, $company);

        $unit = Unit::query()->create([
            'company_id' => $company->id,
            'code' => mb_strtoupper((string) $data['code']),
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
            'metadata' => null,
        ]);

        $audit->record(
            'tenant_master_data.units.created',
            context: [
                'record_id' => $unit->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.units.index')->with('status', __('admin_data_units.created'));
    }

    public function edit(Request $request, Unit $unit): View|RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.update', $company->id);

        abort_unless($this->belongsToTenantOrIsGlobal($unit, $company->id), 404);

        if ($this->isGlobal($unit)) {
            return redirect()
                ->route('admin-data.units.show', $unit)
                ->withErrors(['record' => __('admin_data_units.global_readonly')]);
        }

        return view('client.admin-data.units.form', compact('unit', 'company'));
    }

    public function update(Request $request, Unit $unit, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.update', $company->id);

        abort_unless($this->belongsToTenantOrIsGlobal($unit, $company->id), 404);

        if ($this->isGlobal($unit)) {
            return redirect()
                ->route('admin-data.units.show', $unit)
                ->withErrors(['record' => __('admin_data_units.global_readonly')]);
        }

        $data = $this->validateUnit($request, $company, $unit);

        $unit->fill([
            'code' => mb_strtoupper((string) $data['code']),
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_by' => $request->user()?->id,
            'metadata' => null,
        ]);
        $unit->save();

        $audit->record(
            'tenant_master_data.units.updated',
            context: [
                'record_id' => $unit->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.units.index')->with('status', __('admin_data_units.updated'));
    }

    public function destroy(Request $request, Unit $unit, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.update', $company->id);

        abort_unless($this->belongsToTenantOrIsGlobal($unit, $company->id), 404);

        if ($this->isGlobal($unit)) {
            return redirect()
                ->route('admin-data.units.show', $unit)
                ->withErrors(['record' => __('admin_data_units.global_readonly')]);
        }

        if ($this->hasDependencies($unit)) {
            return redirect()->route('admin-data.units.show', $unit)->withErrors(['record' => __('admin_data_units.remove_blocked')]);
        }

        $recordId = $unit->id;
        $recordCode = $unit->code;
        $unit->delete();

        $audit->record(
            'tenant_master_data.units.removed',
            context: [
                'record_id' => $recordId,
                'record_code' => $recordCode,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.units.index')->with('status', __('admin_data_units.removed'));
    }

    /**
     * @return array{code: string, name: string, description?: string|null, is_active?: bool}
     */
    private function validateUnit(Request $request, Company $company, ?Unit $unit = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')
                    ->where(static fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($unit?->id),
            ],
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('units', 'name')
                    ->where(static fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($unit?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function hasDependencies(Unit $unit): bool
    {
        return \DB::table('products')->where('company_id', (int) $unit->company_id)->where('unit_id', (int) $unit->id)->exists();
    }

    private function belongsToTenantOrIsGlobal(Unit $unit, int $companyId): bool
    {
        if ($unit->company_id === null) {
            return true;
        }

        return (int) $unit->company_id === $companyId;
    }

    private function isGlobal(Unit $unit): bool
    {
        return $unit->company_id === null;
    }
}
