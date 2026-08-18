<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CompanyUserAccessService
{
    public const COMPANY_ACCESS_MODULE = 'users';

    public const ADMINISTRATOR_PROFILE = 'administrator';

    public const CUSTOM_PROFILE = 'custom';

    private const ROLE_SLUG_PREFIX = 'user-access-';

    private const PRIMARY_COMPANY_ADMIN_ROLE_SLUG = 'master';

    /**
     * @var array<string, string>
     */
    private const MODULE_ALIASES = [];

    /**
     * @var array<int, string>
     */
    private const COMPANY_ADMIN_ROLE_SLUGS = ['master'];

    /**
     * @var array<int, string>
     */
    private const LEGACY_ADMIN_ROLE_SLUGS = ['admin', 'account-master'];

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    public function modules(): Collection
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    /**
     * @param  array<int, string>  $modules
     */
    public function sync(User $user, Company $company, string $profile, array $modules, bool $isCompanyAdministrator): void
    {
        $isAdministratorProfile = $isCompanyAdministrator || $profile === self::ADMINISTRATOR_PROFILE;
        $availableModules = $this->modules()->keys()->all();
        $selectedModules = $isAdministratorProfile
            ? $availableModules
            : array_values(array_intersect($availableModules, array_unique($modules)));

        $role = Role::query()->withoutGlobalScope('tenant')->updateOrCreate(
            [
                'company_id' => $company->id,
                'slug' => $this->roleSlug($user, $profile, $isCompanyAdministrator),
            ],
            [
                'name' => $isAdministratorProfile
                    ? 'Master'
                    : Str::limit('Custom access: '.$user->name, 120, ''),
            ]
        );

        $permissionIds = Permission::query()
            ->whereIn('module', $selectedModules)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);

        $user->companies()->syncWithoutDetaching([$company->id]);

        $user->forceFill(['current_company_id' => $company->id])->save();

        DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->delete();

        $user->roles()->attach($role->id, ['company_id' => $company->id]);
    }

    /**
     * @return EloquentCollection<int, Role>
     */
    public function assignableRolesFor(Company $company): EloquentCollection
    {
        return Role::query()
            ->withoutGlobalScope('tenant')
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get();
    }

    public function administratorRoleFor(Company $company): ?Role
    {
        return Role::query()
            ->withoutGlobalScope('tenant')
            ->where('company_id', $company->id)
            ->whereIn('slug', array_merge(self::COMPANY_ADMIN_ROLE_SLUGS, self::LEGACY_ADMIN_ROLE_SLUGS))
            ->orderBy('id')
            ->first();
    }

    public function assignExistingRole(User $user, Company $company, Role $role): void
    {
        $user->companies()->syncWithoutDetaching([$company->id]);

        $user->forceFill(['current_company_id' => $company->id])->save();

        DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->delete();

        $user->roles()->attach($role->id, ['company_id' => $company->id]);
    }

    /**
     * @return array{profile: string, modules: array<int, string>}
     */
    public function accessFor(User $user, Company $company): array
    {
        $assignedRole = $user->roles()
            ->withoutGlobalScope('tenant')
            ->wherePivot('company_id', $company->id)
            ->first();

        if ($this->isCompanyAdministrator($user, $company)) {
            return [
                'profile' => self::ADMINISTRATOR_PROFILE,
                'modules' => $this->appendModuleAliases($this->modules()->keys()->all()),
                'role_id' => $assignedRole?->id,
                'role_name' => $assignedRole?->name,
                'role_slug' => $assignedRole?->slug,
            ];
        }

        if ($assignedRole === null) {
            return ['profile' => self::CUSTOM_PROFILE, 'modules' => []];
        }

        $roleModules = $assignedRole->permissions()->orderBy('module')->pluck('module')->unique()->values()->all();

        return [
            'profile' => self::CUSTOM_PROFILE,
            'modules' => $roleModules,
            'role_id' => $assignedRole->id,
            'role_name' => $assignedRole->name,
            'role_slug' => $assignedRole->slug,
        ];
    }

    public function isFirstCompanyUser(User $user, Company $company): bool
    {
        return (int) $company->users()
            ->orderBy('company_user.created_at')
            ->value('users.id') === $user->id;
    }

    public function hasModuleAccess(User $user, Company $company, string $module): bool
    {
        $module = $this->resolveModuleAlias($module);

        return $user->roles()
            ->withoutGlobalScope('tenant')
            ->wherePivot('company_id', $company->id)
            ->whereHas('permissions', static fn ($query) => $query->where('module', $module))
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function accessibleModules(User $user, Company $company): array
    {
        $accessibleModules = $this->isCompanyAdministrator($user, $company)
            ? $this->modules()->keys()->all()
            : $user->roles()
                ->withoutGlobalScope('tenant')
                ->wherePivot('company_id', $company->id)
                ->with('permissions')
                ->get()
                ->flatMap(static fn (Role $role): array => $role->permissions
                    ->pluck('module')
                    ->filter(static fn (string $module): bool => $module !== '')
                    ->unique()
                    ->values()
                    ->all())
                ->values()
                ->all();

        return $this->appendModuleAliases($accessibleModules);
    }

    public function isCompanyAdministrator(User $user, Company $company): bool
    {
        return $user->roles()
            ->withoutGlobalScope('tenant')
            ->wherePivot('company_id', $company->id)
            ->where(function ($query): void {
                $query->whereIn('roles.slug', array_merge(self::COMPANY_ADMIN_ROLE_SLUGS, self::LEGACY_ADMIN_ROLE_SLUGS))
                    ->orWhere('roles.slug', 'like', self::ROLE_SLUG_PREFIX.'%-'.self::ADMINISTRATOR_PROFILE);
            })
            ->exists();
    }

    public function isAdministratorRoleSlug(string $roleSlug): bool
    {
        return in_array($roleSlug, array_merge(self::COMPANY_ADMIN_ROLE_SLUGS, self::LEGACY_ADMIN_ROLE_SLUGS), true)
            || (bool) preg_match('/^'.preg_quote(self::ROLE_SLUG_PREFIX, '/').'\d+-'.self::ADMINISTRATOR_PROFILE.'$/', $roleSlug);
    }

    public function countActiveCompanyAdministrators(Company $company, ?int $excludeUserId = null): int
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('companies', static fn ($query) => $query->where('companies.id', $company->id))
            ->whereHas('roles', function ($query) use ($company): void {
                $query->withoutGlobalScope('tenant')
                    ->where('role_user.company_id', $company->id)
                    ->where(function ($inner): void {
                        $inner->whereIn('roles.slug', array_merge(self::COMPANY_ADMIN_ROLE_SLUGS, self::LEGACY_ADMIN_ROLE_SLUGS))
                            ->orWhere('roles.slug', 'like', self::ROLE_SLUG_PREFIX.'%-'.self::ADMINISTRATOR_PROFILE);
                    });
            })
            ->when($excludeUserId !== null, static fn ($query) => $query->where('users.id', '!=', $excludeUserId))
            ->count();
    }

    public function canManageCompanyAccess(User $user, Company $company): bool
    {
        return $this->isCompanyAdministrator($user, $company)
            || $this->hasModuleAccess($user, $company, self::COMPANY_ACCESS_MODULE);
    }

    private function roleSlug(User $user, string $profile, bool $isCompanyAdministrator): string
    {
        $effectiveProfile = $isCompanyAdministrator || $profile === self::ADMINISTRATOR_PROFILE
            ? self::ADMINISTRATOR_PROFILE
            : self::CUSTOM_PROFILE;

        if ($effectiveProfile === self::ADMINISTRATOR_PROFILE) {
            return self::PRIMARY_COMPANY_ADMIN_ROLE_SLUG;
        }

        return $this->roleSlugPrefix($user).self::CUSTOM_PROFILE;
    }

    private function roleSlugPrefix(User $user): string
    {
        return self::ROLE_SLUG_PREFIX.$user->id.'-';
    }

    private function resolveModuleAlias(string $module): string
    {
        return self::MODULE_ALIASES[$module] ?? $module;
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, string>
     */
    private function appendModuleAliases(array $modules): array
    {
        foreach (self::MODULE_ALIASES as $alias => $sourceModule) {
            if (in_array($sourceModule, $modules, true) && ! in_array($alias, $modules, true)) {
                $insertAfter = array_search($sourceModule, $modules, true);

                if ($insertAfter === false) {
                    $modules[] = $alias;

                    continue;
                }

                array_splice($modules, $insertAfter + 1, 0, [$alias]);
            }
        }

        return $modules;
    }
}
