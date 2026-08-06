<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class MesExecutionPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'mes-operations.read', 'mes-operations.execute', 'mes-operations.report', 'mes-operations.correct',
            'mes-quality.report', 'mes-quality.rework', 'production-orders.consumption.reverse',
        ] as $slug) {
            Permission::query()->updateOrCreate(['slug' => $slug], ['name' => ucwords(str_replace(['-', '.'], ' ', $slug)), 'module' => 'mes']);
        }
    }
}
