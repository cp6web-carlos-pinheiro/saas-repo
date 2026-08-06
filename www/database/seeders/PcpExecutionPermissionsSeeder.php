<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class PcpExecutionPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'production-order-operations.read', 'production-order-operations.plan',
            'production-schedules.read', 'production-schedules.create', 'production-schedules.publish', 'production-schedules.cancel', 'production-schedules.compare',
            'mrp-runs.read', 'mrp-suggestions.read', 'mrp-suggestions.approve', 'mrp-suggestions.reject', 'mrp-suggestions.convert',
        ] as $slug) {
            Permission::query()->updateOrCreate(['slug' => $slug], ['name' => ucwords(str_replace(['-', '.'], [' ', ' '], $slug)), 'module' => 'production_mrp']);
        }
    }
}
