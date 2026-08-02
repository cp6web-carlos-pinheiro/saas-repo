<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RbacGovernanceService
{
    /**
     * @var array<int, array{left: string, right: string, message: string}>
     */
    private const SOD_CONFLICTS = [
        [
            'left' => 'purchasing.requisitions.create',
            'right' => 'purchasing.orders.approve',
            'message' => 'Não combine criação de requisição com aprovação de pedido de compra na mesma role.',
        ],
        [
            'left' => 'eco.approve',
            'right' => 'eco.implement',
            'message' => 'Não combine aprovação e implementação de ECO na mesma role.',
        ],
    ];

    private const ADMIN_ROLE_SLUGS = ['master', 'admin', 'account-master'];

    private const ADMIN_GUARD_PERMISSION = 'company-access.rbac.manage';

    /**
     * @param  array<int, string>  $permissionSlugs
     */
    public function assertNoSegregationConflict(array $permissionSlugs): void
    {
        $selected = array_values(array_unique($permissionSlugs));

        foreach (self::SOD_CONFLICTS as $conflict) {
            if (in_array($conflict['left'], $selected, true) && in_array($conflict['right'], $selected, true)) {
                throw ValidationException::withMessages([
                    'permission_ids' => [$conflict['message']],
                ]);
            }
        }
    }

    /**
     * @param  array<int, string>  $permissionSlugs
     * @return array<int, string>
     */
    public function invalidPermissionNaming(array $permissionSlugs): array
    {
        $invalid = [];

        foreach ($permissionSlugs as $slug) {
            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*(?:\.[a-z0-9]+(?:-[a-z0-9]+)*){1,2}$/', $slug)) {
                $invalid[] = $slug;
            }
        }

        return array_values(array_unique($invalid));
    }

    /**
     * @param  array<int, string>  $futurePermissionSlugs
     * @param  array<int, int>|null  $futureRoleUserIds
     */
    public function assertAdministrativeContinuity(Role $role, Company $company, array $futurePermissionSlugs, ?array $futureRoleUserIds = null): void
    {
        $roleWillBeAdministrative = $this->roleProvidesAdministrativeAccess($role->slug, $futurePermissionSlugs);

        $otherAdminUsers = $this->administrativeUserIds($company->id, excludeRoleId: $role->id);

        if (! $roleWillBeAdministrative) {
            if ($otherAdminUsers === []) {
                throw ValidationException::withMessages([
                    'role' => ['Não é permitido remover a última role administrativa ativa da empresa.'],
                ]);
            }

            return;
        }

        $targetUsers = $futureRoleUserIds ?? $this->roleUserIds($role->id, $company->id);

        if ($targetUsers === [] && $otherAdminUsers === []) {
            throw ValidationException::withMessages([
                'user_ids' => ['A role administrativa precisa permanecer atribuída a pelo menos um usuário ativo da empresa.'],
            ]);
        }
    }

    private function roleProvidesAdministrativeAccess(string $roleSlug, array $permissionSlugs): bool
    {
        if (in_array($roleSlug, self::ADMIN_ROLE_SLUGS, true)) {
            return true;
        }

        return in_array(self::ADMIN_GUARD_PERMISSION, $permissionSlugs, true);
    }

    /**
     * @return array<int, int>
     */
    private function administrativeUserIds(int $companyId, ?int $excludeRoleId = null): array
    {
        $query = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('permission_role', 'permission_role.role_id', '=', 'roles.id')
            ->leftJoin('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.company_id', $companyId)
            ->where(function ($builder): void {
                $builder->whereIn('roles.slug', self::ADMIN_ROLE_SLUGS)
                    ->orWhere('permissions.slug', self::ADMIN_GUARD_PERMISSION);
            });

        if ($excludeRoleId !== null) {
            $query->where('roles.id', '!=', $excludeRoleId);
        }

        return $query->pluck('role_user.user_id')->unique()->map(static fn ($id): int => (int) $id)->values()->all();
    }

    /**
     * @return array<int, int>
     */
    private function roleUserIds(int $roleId, int $companyId): array
    {
        return DB::table('role_user')
            ->where('role_id', $roleId)
            ->where('company_id', $companyId)
            ->pluck('user_id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();
    }
}
