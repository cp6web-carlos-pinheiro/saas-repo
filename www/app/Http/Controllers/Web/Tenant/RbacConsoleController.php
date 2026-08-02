<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SaaS\AuditLog;
use App\Modules\Identity\Infrastructure\Persistence\Models\CompanyRoleTemplateVersion;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\PermissionUserOverride;
use App\Modules\Identity\Infrastructure\Persistence\Models\RbacChangeRequest;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\RoleTemplate;
use App\Modules\Identity\Infrastructure\Persistence\Models\RoleTemplateVersion;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use App\Services\SaaS\RbacGovernanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class RbacConsoleController extends Controller
{
    private const READ_PERMISSION = 'company-access.rbac.read';

    private const MANAGE_PERMISSION = 'company-access.rbac.manage';

    private const ROLES_CREATE_PERMISSION = 'company-access.roles.create';

    private const ROLES_UPDATE_PERMISSION = 'company-access.roles.update';

    private const ROLES_DELETE_PERMISSION = 'company-access.roles.delete';

    private const OVERRIDES_UPDATE_PERMISSION = 'company-access.overrides.update';

    private const TEMPLATES_MANAGE_PERMISSION = 'company-access.templates.manage';

    private const AUDIT_READ_PERMISSION = 'company-access.audit.read';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $rolesCount = Role::query()->count();
        $templatesCount = RoleTemplate::query()->count();
        $pendingRequestsCount = RbacChangeRequest::query()
            ->where('company_id', $company->id)
            ->where('status', 'pending')
            ->count();
        $historyCount = AuditLog::query()
            ->where('event', 'like', 'rbac.%')
            ->count();

        $invalidPermissionSlugs = app(RbacGovernanceService::class)
            ->invalidPermissionNaming(Permission::query()->pluck('slug')->all());
        $tenantRouteCoverage = $this->tenantRouteCoverage();

        return view('client.rbac.console', [
            'company' => $company,
            'rolesCount' => $rolesCount,
            'templatesCount' => $templatesCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'historyCount' => $historyCount,
            'invalidPermissionSlugs' => $invalidPermissionSlugs,
            'tenantRouteCoverage' => $tenantRouteCoverage,
        ]);
    }

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

    public function templates(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $templates = RoleTemplate::query()
            ->with(['versions' => static fn ($query) => $query->orderByDesc('version')->limit(1)])
            ->orderBy('name')
            ->get();

        $templateApplications = CompanyRoleTemplateVersion::query()
            ->where('company_id', $company->id)
            ->with(['role:id,name', 'template:id,key,name'])
            ->get()
            ->keyBy('role_template_id');

        return view('client.rbac.templates', [
            'company' => $company,
            'templates' => $templates,
            'templateApplications' => $templateApplications,
            'canManageTemplates' => $this->canManageTemplates($request, $company->id),
        ]);
    }

    public function approvals(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $pendingChangeRequests = RbacChangeRequest::query()
            ->where('company_id', $company->id)
            ->where('status', 'pending')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('client.rbac.approvals', [
            'company' => $company,
            'pendingChangeRequests' => $pendingChangeRequests,
            'canApproveChanges' => $this->canManageChanges($request, $company->id),
        ]);
    }

    public function history(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::AUDIT_READ_PERMISSION, $company->id);

        $auditFilters = [
            'actor_user_id' => (int) $request->query('actor_user_id', 0),
            'module' => trim((string) $request->query('module', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
        ];

        $history = AuditLog::query()
            ->where('event', 'like', 'rbac.%')
            ->when($auditFilters['actor_user_id'] > 0, static fn ($query) => $query->where('user_id', $auditFilters['actor_user_id']))
            ->when($auditFilters['module'] !== '', static fn ($query) => $query->where('context->module', $auditFilters['module']))
            ->when($auditFilters['from'] !== '', static fn ($query) => $query->whereDate('occurred_at', '>=', $auditFilters['from']))
            ->when($auditFilters['to'] !== '', static fn ($query) => $query->whereDate('occurred_at', '<=', $auditFilters['to']))
            ->latest('occurred_at')
            ->paginate(12, ['*'], 'history_page')
            ->withQueryString();

        $users = User::query()
            ->whereHas('companies', static fn (Builder $query) => $query->where('companies.id', $company->id))
            ->orderBy('name')
            ->get(['users.id', 'users.name']);

        $availableModules = Permission::query()->orderBy('module')->distinct()->pluck('module');

        return view('client.rbac.history', [
            'company' => $company,
            'history' => $history,
            'auditFilters' => $auditFilters,
            'users' => $users,
            'availableModules' => $availableModules,
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

        $userIds = $this->validatedCompanyUsers($company, $data['user_ids'] ?? []);

        if ((bool) ($data['requires_approval'] ?? false)) {
            $changeRequest = RbacChangeRequest::query()->create([
                'company_id' => $company->id,
                'requested_by_user_id' => $request->user()?->id,
                'status' => 'pending',
                'change_type' => 'role_create',
                'payload' => [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'permission_ids' => $data['permission_ids'],
                    'user_ids' => $userIds,
                ],
                'reason' => $data['approval_reason'] ?? null,
            ]);

            $this->recordRbacAudit($audit, $request, 'rbac.change-request.created', $company->id, [
                'change_request_id' => $changeRequest->id,
                'change_type' => 'role_create',
                'module' => 'users',
            ]);

            return redirect()->route('company-access.rbac.approvals.index')->with('status', __('rbac.change_request_created'));
        }

        $role = DB::transaction(function () use ($company, $data, $userIds): Role {
            $role = Role::query()->create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permission_ids']);
            $this->syncRoleUsers($role, $company, $userIds);

            return $role;
        });

        $this->recordRbacAudit($audit, $request, 'rbac.role.created', $company->id, [
            'role_id' => $role->id,
            'role_slug' => $role->slug,
            'permission_ids' => $data['permission_ids'],
            'user_ids' => $userIds,
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
        $userIds = $this->validatedCompanyUsers($company, $data['user_ids'] ?? []);

        $governance->assertNoSegregationConflict($permissionSlugs);
        $governance->assertAdministrativeContinuity($role, $company, $permissionSlugs, $userIds);

        $invalidSlugs = $governance->invalidPermissionNaming($permissionSlugs);

        if ($invalidSlugs !== []) {
            throw ValidationException::withMessages([
                'permission_ids' => ['Foram encontradas permissões com naming fora do padrão: '.implode(', ', $invalidSlugs)],
            ]);
        }

        if ((bool) ($data['requires_approval'] ?? false)) {
            $changeRequest = RbacChangeRequest::query()->create([
                'company_id' => $company->id,
                'requested_by_user_id' => $request->user()?->id,
                'status' => 'pending',
                'change_type' => 'role_update',
                'payload' => [
                    'role_id' => $role->id,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'permission_ids' => $data['permission_ids'],
                    'user_ids' => $userIds,
                ],
                'reason' => $data['approval_reason'] ?? null,
            ]);

            $this->recordRbacAudit($audit, $request, 'rbac.change-request.created', $company->id, [
                'change_request_id' => $changeRequest->id,
                'change_type' => 'role_update',
                'role_id' => $role->id,
                'module' => 'users',
            ]);

            return redirect()->route('company-access.rbac.approvals.index')->with('status', __('rbac.change_request_created'));
        }

        $before = $this->roleSnapshot($role, $company->id);

        DB::transaction(function () use ($company, $role, $data, $userIds): void {
            $role->fill([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);
            $role->save();

            $role->permissions()->sync($data['permission_ids']);
            $this->syncRoleUsers($role, $company, $userIds);
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

        if ((bool) $request->boolean('requires_approval')) {
            $changeRequest = RbacChangeRequest::query()->create([
                'company_id' => $company->id,
                'requested_by_user_id' => $request->user()?->id,
                'status' => 'pending',
                'change_type' => 'role_delete',
                'payload' => [
                    'role_id' => $role->id,
                ],
                'reason' => trim((string) $request->input('approval_reason')),
            ]);

            $this->recordRbacAudit($audit, $request, 'rbac.change-request.created', $company->id, [
                'change_request_id' => $changeRequest->id,
                'change_type' => 'role_delete',
                'role_id' => $role->id,
                'module' => 'users',
            ]);

            return redirect()->route('company-access.rbac.approvals.index')->with('status', __('rbac.change_request_created'));
        }

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

    public function publishTemplateVersion(Request $request, RoleTemplate $template, RbacGovernanceService $governance, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::TEMPLATES_MANAGE_PERMISSION, $company->id);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['required', 'integer', Rule::exists('permissions', 'id')],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $permissionSlugs = Permission::query()->whereIn('id', $data['permission_ids'])->pluck('slug')->all();

        $governance->assertNoSegregationConflict($permissionSlugs);

        $nextVersion = ((int) $template->versions()->max('version')) + 1;

        $version = RoleTemplateVersion::query()->create([
            'role_template_id' => $template->id,
            'version' => $nextVersion,
            'display_name' => $data['display_name'],
            'permissions' => $permissionSlugs,
            'notes' => $data['notes'] ?? null,
            'published_by_user_id' => $request->user()?->id,
            'published_at' => now(),
        ]);

        $template->forceFill(['current_version' => $version->version])->save();

        $this->recordRbacAudit($audit, $request, 'rbac.template.version-published', $company->id, [
            'template_id' => $template->id,
            'template_key' => $template->key,
            'version' => $version->version,
            'module' => $template->module_focus ?? 'users',
        ]);

        return redirect()->route('company-access.rbac.templates.index')->with('status', __('rbac.template_version_published'));
    }

    public function applyTemplateVersion(Request $request, RoleTemplate $template, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::TEMPLATES_MANAGE_PERMISSION, $company->id);

        $data = $request->validate([
            'version' => ['nullable', 'integer', 'min:1'],
            'role_name' => ['nullable', 'string', 'max:120'],
            'role_slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
        ]);

        $versionNumber = (int) ($data['version'] ?? $template->current_version);

        $version = RoleTemplateVersion::query()
            ->where('role_template_id', $template->id)
            ->where('version', $versionNumber)
            ->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('slug', $version->permissions ?? [])
            ->pluck('id')
            ->all();

        $role = Role::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'slug' => $data['role_slug'] ?? 'template-'.$template->key,
            ],
            [
                'name' => $data['role_name'] ?? $version->display_name,
                'description' => 'Role gerada a partir do template '.$template->name,
            ]
        );

        $role->permissions()->sync($permissionIds);

        CompanyRoleTemplateVersion::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'role_template_id' => $template->id,
            ],
            [
                'role_id' => $role->id,
                'applied_version' => $version->version,
                'applied_by_user_id' => $request->user()?->id,
                'applied_at' => now(),
            ]
        );

        $this->recordRbacAudit($audit, $request, 'rbac.template.applied', $company->id, [
            'template_id' => $template->id,
            'template_key' => $template->key,
            'version' => $version->version,
            'role_id' => $role->id,
            'module' => $template->module_focus ?? 'users',
        ]);

        return redirect()->route('company-access.rbac.roles.show', $role)->with('status', __('rbac.template_applied'));
    }

    public function approveChangeRequest(Request $request, RbacChangeRequest $changeRequest, RbacGovernanceService $governance, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::MANAGE_PERMISSION, $company->id);

        $changeRequest = RbacChangeRequest::query()
            ->where('company_id', $company->id)
            ->whereKey($changeRequest->id)
            ->firstOrFail();

        abort_unless($changeRequest->status === 'pending', 422);

        DB::transaction(function () use ($changeRequest, $company, $request, $governance): void {
            $this->applyChangeRequestPayload($changeRequest, $company, $governance);

            $changeRequest->forceFill([
                'status' => 'approved',
                'approved_by_user_id' => $request->user()?->id,
                'review_notes' => trim((string) $request->input('review_notes')) ?: null,
                'reviewed_at' => now(),
            ])->save();
        });

        $this->recordRbacAudit($audit, $request, 'rbac.change-request.approved', $company->id, [
            'change_request_id' => $changeRequest->id,
            'change_type' => $changeRequest->change_type,
            'module' => 'users',
        ]);

        return redirect()->route('company-access.rbac.approvals.index')->with('status', __('rbac.change_request_approved'));
    }

    public function rejectChangeRequest(Request $request, RbacChangeRequest $changeRequest, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::MANAGE_PERMISSION, $company->id);

        $changeRequest = RbacChangeRequest::query()
            ->where('company_id', $company->id)
            ->whereKey($changeRequest->id)
            ->firstOrFail();

        abort_unless($changeRequest->status === 'pending', 422);

        $changeRequest->forceFill([
            'status' => 'rejected',
            'approved_by_user_id' => $request->user()?->id,
            'review_notes' => trim((string) $request->input('review_notes')) ?: null,
            'reviewed_at' => now(),
        ])->save();

        $this->recordRbacAudit($audit, $request, 'rbac.change-request.rejected', $company->id, [
            'change_request_id' => $changeRequest->id,
            'change_type' => $changeRequest->change_type,
            'module' => 'users',
        ]);

        return redirect()->route('company-access.rbac.approvals.index')->with('status', __('rbac.change_request_rejected'));
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

    private function canManageTemplates(Request $request, int $companyId): bool
    {
        $user = $request->user();

        return $user instanceof User && $user->hasPermission(self::TEMPLATES_MANAGE_PERMISSION, $companyId);
    }

    private function canManageChanges(Request $request, int $companyId): bool
    {
        $user = $request->user();

        return $user instanceof User && $user->hasPermission(self::MANAGE_PERMISSION, $companyId);
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
     * @return array{name: string, slug: string, description: string|null, permission_ids: array<int, int>, user_ids: array<int, int>, requires_approval?: bool, approval_reason?: string|null}
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
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'integer'],
            'requires_approval' => ['nullable', 'boolean'],
            'approval_reason' => ['nullable', 'string', 'max:1000'],
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

        $users = User::query()
            ->whereHas('companies', static fn (Builder $query) => $query->where('companies.id', $company->id))
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        $selectedPermissionIds = $role?->permissions()->pluck('permissions.id')->map(static fn ($id): int => (int) $id)->all() ?? [];
        $selectedUserIds = $role !== null
            ? DB::table('role_user')->where('role_id', $role->id)->where('company_id', $company->id)->pluck('user_id')->map(static fn ($id): int => (int) $id)->all()
            : [];

        return [
            'company' => $company,
            'role' => $role,
            'permissionsByModule' => $permissionsByModule,
            'users' => $users,
            'selectedPermissionIds' => $selectedPermissionIds,
            'selectedUserIds' => $selectedUserIds,
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

    private function applyChangeRequestPayload(RbacChangeRequest $changeRequest, Company $company, RbacGovernanceService $governance): void
    {
        $payload = (array) $changeRequest->payload;
        $type = $changeRequest->change_type;

        if ($type === 'role_create') {
            $roleSlug = (string) Arr::get($payload, 'slug');

            if (app(CompanyUserAccessService::class)->isAdministratorRoleSlug($roleSlug)) {
                throw ValidationException::withMessages([
                    'slug' => [__('rbac.master_role_locked')],
                ]);
            }

            $role = Role::query()->create([
                'company_id' => $company->id,
                'name' => (string) Arr::get($payload, 'name'),
                'slug' => $roleSlug,
                'description' => Arr::get($payload, 'description'),
            ]);

            $permissionIds = collect(Arr::get($payload, 'permission_ids', []))->map(static fn ($id): int => (int) $id)->all();
            $role->permissions()->sync($permissionIds);
            $this->syncRoleUsers($role, $company, collect(Arr::get($payload, 'user_ids', []))->map(static fn ($id): int => (int) $id)->all());

            return;
        }

        if ($type === 'role_update') {
            $role = $this->companyRoleOrFail($company, (int) Arr::get($payload, 'role_id'));

            if ($this->isAdministratorProfileRole($role)) {
                throw ValidationException::withMessages([
                    'role' => [__('rbac.master_role_locked')],
                ]);
            }

            $permissionIds = collect(Arr::get($payload, 'permission_ids', []))->map(static fn ($id): int => (int) $id)->all();
            $permissionSlugs = Permission::query()->whereIn('id', $permissionIds)->pluck('slug')->all();
            $userIds = collect(Arr::get($payload, 'user_ids', []))->map(static fn ($id): int => (int) $id)->all();

            $governance->assertNoSegregationConflict($permissionSlugs);
            $governance->assertAdministrativeContinuity($role, $company, $permissionSlugs, $userIds);

            $role->fill([
                'name' => (string) Arr::get($payload, 'name', $role->name),
                'slug' => (string) Arr::get($payload, 'slug', $role->slug),
                'description' => Arr::get($payload, 'description'),
            ]);
            $role->save();

            $role->permissions()->sync($permissionIds);
            $this->syncRoleUsers($role, $company, $userIds);

            return;
        }

        if ($type === 'role_delete') {
            $role = $this->companyRoleOrFail($company, (int) Arr::get($payload, 'role_id'));

            if ($this->isAdministratorProfileRole($role)) {
                throw ValidationException::withMessages([
                    'role' => [__('rbac.master_role_locked')],
                ]);
            }

            $governance->assertAdministrativeContinuity($role, $company, []);

            DB::table('role_user')
                ->where('role_id', $role->id)
                ->where('company_id', $company->id)
                ->delete();

            $role->delete();

            return;
        }

        throw ValidationException::withMessages([
            'change_request' => ['Tipo de solicitação RBAC não suportado: '.$type],
        ]);
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

    /**
     * @return array{total: int, protected: int, missing: int, missing_routes: array<int, string>}
     */
    private function tenantRouteCoverage(): array
    {
        $tenantPrefixes = [
            'dashboard',
            'company-access',
            'purchasing',
            'bom',
            'products',
            'billing',
        ];

        $tenantRoutes = collect(Route::getRoutes())
            ->filter(static function ($route) use ($tenantPrefixes): bool {
                $uri = (string) $route->uri();

                foreach ($tenantPrefixes as $prefix) {
                    if ($uri === $prefix || str_starts_with($uri, $prefix.'/')) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        $protected = 0;
        $missingRoutes = [];

        foreach ($tenantRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $uri = (string) $route->uri();
            $actionName = (string) ($route->getActionName() ?? '');

            $usesCheckPermission = collect($middleware)->contains(static fn ($item): bool => str_contains((string) $item, 'CheckPermission'));
            $usesCompanyAccessPrefix = str_starts_with($uri, 'company-access/');
            $usesSupplierControllerGuard = str_starts_with($uri, 'purchasing/suppliers')
                && str_contains($actionName, 'SupplierController');

            if ($usesCheckPermission || $usesCompanyAccessPrefix || $usesSupplierControllerGuard) {
                $protected++;

                continue;
            }

            $missingRoutes[] = $route->methods()[0].' '.$uri;
        }

        return [
            'total' => $tenantRoutes->count(),
            'protected' => $protected,
            'missing' => count($missingRoutes),
            'missing_routes' => array_slice($missingRoutes, 0, 15),
        ];
    }
}
