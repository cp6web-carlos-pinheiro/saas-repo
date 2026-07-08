<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class RoutingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read Routing Versions', 'slug' => 'routing-versions.read', 'module' => 'routing'],
            ['name' => 'Create Routing Versions', 'slug' => 'routing-versions.create', 'module' => 'routing'],
            ['name' => 'Approve Routing Versions', 'slug' => 'routing-versions.approve', 'module' => 'routing'],
            ['name' => 'Obsolete Routing Versions', 'slug' => 'routing-versions.obsolete', 'module' => 'routing'],
            ['name' => 'Read Routing Operations', 'slug' => 'routing-operations.read', 'module' => 'routing'],
            ['name' => 'Create Routing Operations', 'slug' => 'routing-operations.create', 'module' => 'routing'],
            ['name' => 'Update Routing Operations', 'slug' => 'routing-operations.update', 'module' => 'routing'],
            ['name' => 'Delete Routing Operations', 'slug' => 'routing-operations.delete', 'module' => 'routing'],
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
    }
}
