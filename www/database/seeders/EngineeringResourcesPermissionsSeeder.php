<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class EngineeringResourcesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read Production Resources', 'slug' => 'production-resources.read'],
            ['name' => 'Create Production Resources', 'slug' => 'production-resources.create'],
            ['name' => 'Update Production Resources', 'slug' => 'production-resources.update'],
            ['name' => 'Delete Production Resources', 'slug' => 'production-resources.delete'],
            ['name' => 'Read Work Center Hour Rates', 'slug' => 'work-center-hour-rates.read'],
            ['name' => 'Create Work Center Hour Rates', 'slug' => 'work-center-hour-rates.create'],
            ['name' => 'Read Routing Standard Times', 'slug' => 'routing-standard-times.read'],
            ['name' => 'Create Routing Standard Times', 'slug' => 'routing-standard-times.create'],
            ['name' => 'Update Routing Standard Times', 'slug' => 'routing-standard-times.update'],
            ['name' => 'Approve Routing Standard Times', 'slug' => 'routing-standard-times.approve'],
            ['name' => 'Obsolete Routing Standard Times', 'slug' => 'routing-standard-times.obsolete'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'module' => 'production_mrp']
            );
        }
    }
}
