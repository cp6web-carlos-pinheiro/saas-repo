<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Branch;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class BranchController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'admin-data.branches.read';

    private const CREATE_PERMISSION = 'admin-data.branches.create';

    private const UPDATE_PERMISSION = 'admin-data.branches.update';

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

        $branches = Branch::query()
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

        return view('client.inventory.branches.search', compact('branches', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.inventory.branches.form', [
            'branch' => null,
            'company' => $company,
        ]);
    }

    public function show(Request $request, Branch $branch): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        abort_unless((int) $branch->company_id === (int) $company->id, 404);

        $branch->loadCount('plants');

        return view('client.inventory.branches.show', compact('branch', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateBranch($request, $company);

        $branch = Branch::query()->create([
            'company_id' => $company->id,
            'name' => (string) $data['name'],
            'code' => mb_strtoupper((string) $data['code']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $audit->record(
            'tenant_inventory_branch.created',
            context: [
                'branch_id' => $branch->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.branches.index')->with('status', __('branch.created'));
    }

    public function edit(Request $request, Branch $branch): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $branch->company_id === (int) $company->id, 404);

        return view('client.inventory.branches.form', compact('branch', 'company'));
    }

    public function update(Request $request, Branch $branch, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $branch->company_id === (int) $company->id, 404);

        $data = $this->validateBranch($request, $company, $branch);

        $branch->fill([
            'name' => (string) $data['name'],
            'code' => mb_strtoupper((string) $data['code']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $branch->save();

        $audit->record(
            'tenant_inventory_branch.updated',
            context: [
                'branch_id' => $branch->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.branches.index')->with('status', __('branch.updated'));
    }

    public function destroy(Request $request, Branch $branch, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        abort_unless((int) $branch->company_id === (int) $company->id, 404);

        if ($branch->plants()->exists()) {
            return redirect()->route('inventory.branches.show', $branch)->withErrors(['branch' => __('branch.remove_blocked')]);
        }

        $branchId = $branch->id;
        $branchCode = $branch->code;
        $branch->delete();

        $audit->record(
            'tenant_inventory_branch.removed',
            context: [
                'branch_id' => $branchId,
                'branch_code' => $branchCode,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('inventory.branches.index')->with('status', __('branch.removed'));
    }

    /**
     * @return array{name: string, code: string, is_active?: bool}
     */
    private function validateBranch(Request $request, Company $company, ?Branch $branch = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where(static fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($branch?->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
