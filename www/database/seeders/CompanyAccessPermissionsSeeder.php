<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class CompanyAccessPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read company users', 'slug' => 'company-access.users.read', 'module' => 'users'],
            ['name' => 'Create company users', 'slug' => 'company-access.users.create', 'module' => 'users'],
            ['name' => 'Update company users', 'slug' => 'company-access.users.update', 'module' => 'users'],
            ['name' => 'Delete company users', 'slug' => 'company-access.users.delete', 'module' => 'users'],
            ['name' => 'Read RBAC console', 'slug' => 'company-access.rbac.read', 'module' => 'users'],
            ['name' => 'Create roles', 'slug' => 'company-access.roles.create', 'module' => 'users'],
            ['name' => 'Update roles', 'slug' => 'company-access.roles.update', 'module' => 'users'],
            ['name' => 'Delete roles', 'slug' => 'company-access.roles.delete', 'module' => 'users'],
            ['name' => 'Read dashboard', 'slug' => 'company-access.dashboard.read', 'module' => 'users'],
            ['name' => 'Read billing subscription', 'slug' => 'company-access.billing.read', 'module' => 'users'],
            ['name' => 'Update billing subscription', 'slug' => 'company-access.billing.update', 'module' => 'users'],
            ['name' => 'Read reports', 'slug' => 'reports.read', 'module' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                ]
            );
        }

        Permission::query()
            ->whereIn('slug', [
                'identity.users.read',
                'identity.users.create',
                'identity.users.update',
                'identity.users.delete',
            ])
            ->delete();
    }
}
