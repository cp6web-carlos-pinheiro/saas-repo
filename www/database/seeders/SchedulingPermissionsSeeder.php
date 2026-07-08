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
            ['name' => 'Read Work Centers', 'slug' => 'work-centers.read', 'module' => 'scheduling'],
            ['name' => 'Create Work Centers', 'slug' => 'work-centers.create', 'module' => 'scheduling'],
            ['name' => 'Update Work Centers', 'slug' => 'work-centers.update', 'module' => 'scheduling'],
            ['name' => 'Delete Work Centers', 'slug' => 'work-centers.delete', 'module' => 'scheduling'],
            ['name' => 'Create Work Center Shifts', 'slug' => 'work-centers.shifts.create', 'module' => 'scheduling'],
            ['name' => 'Read Production Calendar', 'slug' => 'production-calendar.read', 'module' => 'scheduling'],
            ['name' => 'Update Production Calendar', 'slug' => 'production-calendar.update', 'module' => 'scheduling'],
            ['name' => 'Generate Production Calendar', 'slug' => 'production-calendar.generate', 'module' => 'scheduling'],
            ['name' => 'Run Production Scheduling', 'slug' => 'production-scheduling.run', 'module' => 'scheduling'],
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
