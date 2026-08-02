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
            ['name' => 'Read Routing Versions', 'slug' => 'routing-versions.read', 'module' => 'production_mrp'],
            ['name' => 'Create Routing Versions', 'slug' => 'routing-versions.create', 'module' => 'production_mrp'],
            ['name' => 'Approve Routing Versions', 'slug' => 'routing-versions.approve', 'module' => 'production_mrp'],
            ['name' => 'Obsolete Routing Versions', 'slug' => 'routing-versions.obsolete', 'module' => 'production_mrp'],
            ['name' => 'Read Routing Operations', 'slug' => 'routing-operations.read', 'module' => 'production_mrp'],
            ['name' => 'Create Routing Operations', 'slug' => 'routing-operations.create', 'module' => 'production_mrp'],
            ['name' => 'Update Routing Operations', 'slug' => 'routing-operations.update', 'module' => 'production_mrp'],
            ['name' => 'Delete Routing Operations', 'slug' => 'routing-operations.delete', 'module' => 'production_mrp'],
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
