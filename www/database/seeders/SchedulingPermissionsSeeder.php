<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class SchedulingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read Work Centers', 'slug' => 'work-centers.read', 'module' => 'production_mrp'],
            ['name' => 'Create Work Centers', 'slug' => 'work-centers.create', 'module' => 'production_mrp'],
            ['name' => 'Update Work Centers', 'slug' => 'work-centers.update', 'module' => 'production_mrp'],
            ['name' => 'Delete Work Centers', 'slug' => 'work-centers.delete', 'module' => 'production_mrp'],
            ['name' => 'Create Work Center Shifts', 'slug' => 'work-centers.shifts.create', 'module' => 'production_mrp'],
            ['name' => 'Read Production Calendar', 'slug' => 'production-calendar.read', 'module' => 'production_mrp'],
            ['name' => 'Update Production Calendar', 'slug' => 'production-calendar.update', 'module' => 'production_mrp'],
            ['name' => 'Generate Production Calendar', 'slug' => 'production-calendar.generate', 'module' => 'production_mrp'],
            ['name' => 'Run Production Scheduling', 'slug' => 'production-scheduling.run', 'module' => 'production_mrp'],
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
