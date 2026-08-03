<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\PermissionUserOverride;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use App\Services\SaaS\RbacGovernanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class RbacConsoleController extends Controller
{
    private const READ_PERMISSION = 'company-access.rbac.read';

    private const ROLES_CREATE_PERMISSION = 'company-access.roles.create';

    private const ROLES_UPDATE_PERMISSION = 'company-access.roles.update';

    private const ROLES_DELETE_PERMISSION = 'company-access.roles.delete';

    private const OVERRIDES_UPDATE_PERMISSION = 'company-access.overrides.update';

    public function roles(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $roles = Role::query()
            ->withCount('permissions')
            ->withCount([
                'users as users_count' => static fn (Builder $query) => $query->where('role_user.company_id', $company->id),
            ])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('client.rbac.roles', [
            'company' => $company,
            'roles' => $roles,
        ]);
    }

    public function createRole(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::ROLES_CREATE_PERMISSION, $company->id);

        return view('client.rbac.role-form', $this->roleFormData($company));
    }

    public function storeRole(Request $request, RbacGovernanceService $governance, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::ROLES_CREATE_PERMISSION, $company->id);

        $data = $this->validateRole($request, $company);
        $this->assertRoleSlugNotReserved((string) $data['slug']);
        $permissionSlugs = Permission::query()->whereIn('id', $data['permission_ids'])->pluck('slug')->all();

        $governance->assertNoSegregationConflict($permissionSlugs);
        $invalidSlugs = $governance->invalidPermissionNaming($permissionSlugs);

        if ($invalidSlugs !== []) {
            throw ValidationException::withMessages([
                'permission_ids' => ['Foram encontradas permissões com naming fora do padrão: '.implode(', ', $invalidSlugs)],
            ]);
        }

        $role = DB::transaction(function () use ($company, $data): Role {
            $role = Role::query()->create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permission_ids']);

            return $role;
        });

        $this->recordRbacAudit($audit, $request, 'rbac.role.created', $company->id, [
            'role_id' => $role->id,
            'role_slug' => $role->slug,
            'permission_ids' => $data['permission_ids'],
            'user_ids' => [],
            'module' => 'users',
        ]);

        return redirect()->route('company-access.rbac.roles.show', $role)->with('status', __('rbac.role_created'));
    }

    public function showRole(Request $request, Role $role): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $role = $this->companyRoleOrFail($company, $role->id);
        $role->load(['permissions:id,name,slug,module', 'users' => static fn ($query) => $query->where('role_user.company_id', $company->id)->orderBy('name')]);

        $assignedUserIds = $role->users->pluck('id')->all();

        $overrideStats = PermissionUserOverride::query()
            ->where('company_id', $company->id)
            ->whereIn('user_id', $assignedUserIds)
            ->get()
            ->groupBy('user_id')
            ->map(static function ($items): array {
                return [
                    'allow' => $items->where('is_allowed', true)->count(),
                    'deny' => $items->where('is_allowed', false)->count(),
                ];
            });

        return view('client.rbac.role-show', [
            'company' => $company,
            'role' => $role,
            'overrideStats' => $overrideStats,
        ]);
    }

    public function editRole(Request $request, Role $role): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::ROLES_UPDATE_PERMISSION, $company->id);

        $role = $this->companyRoleOrFail($company, $role->id);

        abort_if($this->isAdministratorProfileRole($role), 403, __('rbac.master_role_locked'));

        return view('client.rbac.role-form', $this->roleFormData($company, $role));
    }

    public function updateRole(Request $request, Role $role, RbacGovernanceService $governance, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::ROLES_UPDATE_PERMISSION, $company->id);

        $role = $this->companyRoleOrFail($company, $role->id);

        if ($this->isAdministratorProfileRole($role)) {
            return redirect()->route('company-access.rbac.roles.show', $role)
                ->withErrors(['role' => __('rbac.master_role_locked')]);
        }

        $data = $this->validateRole($request, $company, $role);
        $this->assertRoleSlugNotReserved((string) $data['slug'], $role);
        $permissionSlugs = Permission::query()->whereIn('id', $data['permission_ids'])->pluck('slug')->all();
        $userIds = DB::table('role_user')
            ->where('role_id', $role->id)
            ->where('company_id', $company->id)
            ->pluck('user_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $governance->assertNoSegregationConflict($permissionSlugs);
        $governance->assertAdministrativeContinuity($role, $company, $permissionSlugs, $userIds);

        $invalidSlugs = $governance->invalidPermissionNaming($permissionSlugs);

        if ($invalidSlugs !== []) {
            throw ValidationException::withMessages([
                'permission_ids' => ['Foram encontradas permissões com naming fora do padrão: '.implode(', ', $invalidSlugs)],
            ]);
        }

        $before = $this->roleSnapshot($role, $company->id);

        DB::transaction(function () use ($role, $data): void {
            $role->fill([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);
            $role->save();

            $role->permissions()->sync($data['permission_ids']);
        });

        $after = $this->roleSnapshot($role->fresh(['permissions', 'users']), $company->id);

        $this->recordRbacAudit($audit, $request, 'rbac.role.updated', $company->id, [
            'role_id' => $role->id,
            'before' => $before,
            'after' => $after,
            'module' => 'users',
        ]);

        return redirect()->route('company-access.rbac.roles.show', $role)->with('status', __('rbac.role_updated'));
    }

    public function destroyRole(Request $request, Role $role, RbacGovernanceService $governance, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::ROLES_DELETE_PERMISSION, $company->id);

        $role = $this->companyRoleOrFail($company, $role->id);

        if ($this->isAdministratorProfileRole($role)) {
            return redirect()->route('company-access.rbac.roles.show', $role)
                ->withErrors(['role' => __('rbac.master_role_locked')]);
        }

        $permissionSlugs = $role->permissions()->pluck('slug')->all();

        $governance->assertAdministrativeContinuity($role, $company, []);

        $roleName = $role->name;
        $roleId = $role->id;

        DB::transaction(function () use ($role, $company): void {
            DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('company_id', $company->id)
                ->delete();

            $role->delete();
        });

        $this->recordRbacAudit($audit, $request, 'rbac.role.deleted', $company->id, [
            'role_id' => $roleId,
            'role_name' => $roleName,
            'permission_slugs' => $permissionSlugs,
            'module' => 'users',
        ]);

        return redirect()->route('company-access.rbac.roles.index')->with('status', __('rbac.role_deleted'));
    }

    public function editUserOverrides(Request $request, User $user): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::OVERRIDES_UPDATE_PERMISSION, $company->id);

        $user = $this->companyUserOrFail($company, $user->id);

        $permissionsByModule = Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module');

        $overrideMap = PermissionUserOverride::query()
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('permission_id');

        $inheritedPermissions = $user->roles()
            ->wherePivot('company_id', $company->id)
            ->with('permissions:id,slug')
            ->get()
            ->flatMap(static fn (Role $role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values()
            ->all();

        return view('client.rbac.user-overrides-form', [
            'company' => $company,
            'customer' => $user,
            'permissionsByModule' => $permissionsByModule,
            'overrideMap' => $overrideMap,
            'inheritedPermissions' => $inheritedPermissions,
        ]);
    }

    public function updateUserOverrides(Request $request, User $user, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::OVERRIDES_UPDATE_PERMISSION, $company->id);

        $user = $this->companyUserOrFail($company, $user->id);

        $data = $request->validate([
            'overrides' => ['nullable', 'array'],
            'overrides.*.state' => ['required_with:overrides', Rule::in(['inherit', 'allow', 'deny'])],
            'overrides.*.reason' => ['nullable', 'string', 'max:255'],
        ]);

        $requestedOverrides = $data['overrides'] ?? [];
        $permissionIds = array_map(static fn ($id): int => (int) $id, array_keys($requestedOverrides));
        $knownPermissionIds = Permission::query()->whereIn('id', $permissionIds)->pluck('id')->all();

        $unknownPermissionIds = array_diff($permissionIds, $knownPermissionIds);

        if ($unknownPermissionIds !== []) {
            throw ValidationException::withMessages([
                'overrides' => ['Foram enviadas permissões inválidas para override.'],
            ]);
        }

        $before = PermissionUserOverride::query()
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->get()
            ->map(static fn (PermissionUserOverride $override): array => [
                'permission_id' => $override->permission_id,
                'is_allowed' => $override->is_allowed,
                'reason' => $override->reason,
            ])
            ->values()
            ->all();

        DB::transaction(function () use ($company, $user, $requestedOverrides, $request): void {
            PermissionUserOverride::query()
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->delete();

            foreach ($requestedOverrides as $permissionId => $override) {
                $state = (string) ($override['state'] ?? 'inherit');

                if ($state === 'inherit') {
                    continue;
                }

                PermissionUserOverride::query()->create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'permission_id' => (int) $permissionId,
                    'is_allowed' => $state === 'allow',
                    'reason' => trim((string) ($override['reason'] ?? '')) ?: null,
                    'created_by_user_id' => $request->user()?->id,
                ]);
            }
        });

        $after = PermissionUserOverride::query()
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->get()
            ->map(static fn (PermissionUserOverride $override): array => [
                'permission_id' => $override->permission_id,
                'is_allowed' => $override->is_allowed,
                'reason' => $override->reason,
            ])
            ->values()
            ->all();

        $this->recordRbacAudit($audit, $request, 'rbac.user-overrides.updated', $company->id, [
            'target_user_id' => $user->id,
            'before' => $before,
            'after' => $after,
            'module' => 'users',
        ]);

        $roleId = (int) $request->input('role_id');

        if ($roleId > 0) {
            return redirect()->route('company-access.rbac.roles.show', ['role' => $roleId])->with('status', __('rbac.overrides_updated'));
        }

        return redirect()->route('company-access.rbac.roles.index')->with('status', __('rbac.overrides_updated'));
    }

    private function activeCompanyFrom(Request $request): Company
    {
        $companyId = (int) ($request->user()?->current_company_id ?? 0);

        abort_unless($companyId > 0, 404);

        return Company::query()->findOrFail($companyId);
    }

    private function ensurePermission(Request $request, string $permission, int $companyId): void
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $company = Company::query()->findOrFail($companyId);

        if (app(CompanyUserAccessService::class)->isCompanyAdministrator($user, $company)) {
            return;
        }

        abort_unless($user->hasPermission($permission, $companyId), 403);
    }

    private function companyRoleOrFail(Company $company, int $roleId): Role
    {
        return Role::query()->where('company_id', $company->id)->whereKey($roleId)->firstOrFail();
    }

    private function companyUserOrFail(Company $company, int $userId): User
    {
        return User::query()
            ->whereKey($userId)
            ->whereHas('companies', static fn (Builder $query) => $query->where('companies.id', $company->id))
            ->firstOrFail();
    }

    /**
     * @return array<int, int>
     */
    private function validatedCompanyUsers(Company $company, array $userIds): array
    {
        $normalized = collect($userIds)
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [];
        }

        $allowedIds = User::query()
            ->whereHas('companies', static fn (Builder $query) => $query->where('companies.id', $company->id))
            ->whereIn('users.id', $normalized->all())
            ->pluck('users.id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        if (count($allowedIds) !== $normalized->count()) {
            throw ValidationException::withMessages([
                'user_ids' => ['Foram informados usuários que não pertencem à empresa ativa.'],
            ]);
        }

        return $allowedIds;
    }

    private function syncRoleUsers(Role $role, Company $company, array $userIds): void
    {
        DB::table('role_user')
            ->where('role_id', $role->id)
            ->where('company_id', $company->id)
            ->delete();

        if ($userIds === []) {
            return;
        }

        $role->users()->attach(
            collect($userIds)->mapWithKeys(static fn (int $id): array => [$id => ['company_id' => $company->id]])->all()
        );
    }

    /**
     * @return array{name: string, slug: string, description: string|null, permission_ids: array<int, int>}
     */
    private function validateRole(Request $request, Company $company, ?Role $role = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'slug')->where('company_id', $company->id)->ignore($role),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['required', 'integer', Rule::exists('permissions', 'id')],
        ];

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    private function roleFormData(Company $company, ?Role $role = null): array
    {
        $permissionsByModule = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $selectedPermissionIds = $role?->permissions()->pluck('permissions.id')->map(static fn ($id): int => (int) $id)->all() ?? [];

        return [
            'company' => $company,
            'role' => $role,
            'permissionsByModule' => $permissionsByModule,
            'selectedPermissionIds' => $selectedPermissionIds,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roleSnapshot(Role $role, int $companyId): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'permission_slugs' => $role->permissions()->orderBy('slug')->pluck('slug')->all(),
            'user_ids' => DB::table('role_user')->where('role_id', $role->id)->where('company_id', $companyId)->pluck('user_id')->all(),
        ];
    }

    private function recordRbacAudit(AuditLogService $audit, Request $request, string $event, int $companyId, array $context): void
    {
        $audit->record(
            $event,
            context: array_merge($context, [
                'company_id' => $companyId,
            ]),
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    private function isAdministratorProfileRole(Role $role): bool
    {
        return app(CompanyUserAccessService::class)->isAdministratorRoleSlug((string) $role->slug);
    }

    private function assertRoleSlugNotReserved(string $slug, ?Role $existingRole = null): void
    {
        $normalizedSlug = trim($slug);
        $access = app(CompanyUserAccessService::class);

        if ($existingRole !== null && $normalizedSlug === (string) $existingRole->slug) {
            return;
        }

        if ($access->isAdministratorRoleSlug($normalizedSlug)) {
            throw ValidationException::withMessages([
                'slug' => [__('rbac.master_role_locked')],
            ]);
        }
    }

}
