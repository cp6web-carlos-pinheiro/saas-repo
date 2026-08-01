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
            ['name' => 'Read company users', 'slug' => 'company-access.users.read', 'module' => 'company_access'],
            ['name' => 'Create company users', 'slug' => 'company-access.users.create', 'module' => 'company_access'],
            ['name' => 'Update company users', 'slug' => 'company-access.users.update', 'module' => 'company_access'],
            ['name' => 'Delete company users', 'slug' => 'company-access.users.delete', 'module' => 'company_access'],
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
