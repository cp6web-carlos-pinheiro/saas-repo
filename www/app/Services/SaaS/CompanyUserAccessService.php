<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CompanyUserAccessService
{
    public const COMPANY_ACCESS_MODULE = 'company_access';

    public const ADMINISTRATOR_PROFILE = 'administrator';

    public const CUSTOM_PROFILE = 'custom';

    private const ROLE_SLUG_PREFIX = 'user-access-';

    /**
     * @var array<int, string>
     */
    private const COMPANY_ADMIN_ROLE_SLUGS = ['admin', 'account-master'];

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
        $availableModules = $this->modules()->keys()->all();
        $selectedModules = $isCompanyAdministrator || $profile === self::ADMINISTRATOR_PROFILE
            ? $availableModules
            : array_values(array_intersect($availableModules, array_unique($modules)));

        $role = Role::query()->withoutGlobalScope('tenant')->updateOrCreate(
            [
                'company_id' => $company->id,
                'slug' => $this->roleSlug($user, $profile, $isCompanyAdministrator),
            ],
            [
                'name' => Str::limit($isCompanyAdministrator || $profile === self::ADMINISTRATOR_PROFILE ? 'Administrator: '.$user->name : 'Custom access: '.$user->name, 120, ''),
            ]
        );

        $permissionIds = Permission::query()
            ->whereIn('module', $selectedModules)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);

        $user->companies()->syncWithoutDetaching([
            $company->id => ['is_default' => $user->current_company_id === null || $user->current_company_id === $company->id],
        ]);

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
        $role = $user->roles()
            ->withoutGlobalScope('tenant')
            ->wherePivot('company_id', $company->id)
            ->where('roles.slug', 'like', $this->roleSlugPrefix($user).'%')
            ->first();

        if ($role === null) {
            return ['profile' => self::CUSTOM_PROFILE, 'modules' => []];
        }

        $roleModules = $role->permissions()->orderBy('module')->pluck('module')->unique()->values()->all();

        $isAdministrator = str_ends_with((string) $role->slug, '-'.self::ADMINISTRATOR_PROFILE);

        return [
            'profile' => $isAdministrator ? self::ADMINISTRATOR_PROFILE : self::CUSTOM_PROFILE,
            'modules' => $roleModules,
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
        if ($this->isCompanyAdministrator($user, $company)) {
            return $this->modules()->keys()->all();
        }

        return $user->roles()
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
    }

    public function isCompanyAdministrator(User $user, Company $company): bool
    {
        return $user->roles()
            ->withoutGlobalScope('tenant')
            ->wherePivot('company_id', $company->id)
            ->where(function ($query) {
                $query->whereIn('roles.slug', self::COMPANY_ADMIN_ROLE_SLUGS)
                    ->orWhere('roles.slug', 'like', self::ROLE_SLUG_PREFIX.'%-'.self::ADMINISTRATOR_PROFILE);
            })
            ->exists();
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

        return $this->roleSlugPrefix($user).$effectiveProfile;
    }

    private function roleSlugPrefix(User $user): string
    {
        return self::ROLE_SLUG_PREFIX.$user->id.'-';
    }
}
