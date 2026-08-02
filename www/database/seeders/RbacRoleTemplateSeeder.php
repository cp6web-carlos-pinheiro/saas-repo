<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\RoleTemplate;
use App\Modules\Identity\Infrastructure\Persistence\Models\RoleTemplateVersion;
use Illuminate\Database\Seeder;

final class RbacRoleTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'purchasing',
                'name' => 'Compras',
                'module_focus' => 'purchasing',
                'permissions' => [
                    'purchasing.suppliers.read',
                    'purchasing.requisitions.read',
                    'purchasing.requisitions.create',
                    'purchasing.orders.read',
                ],
            ],
            [
                'key' => 'pcp',
                'name' => 'PCP',
                'module_focus' => 'production_mrp',
                'permissions' => [
                    'mrp.plan',
                    'production-scheduling.run',
                    'production-calendar.read',
                    'production-orders.read',
                    'inventory.read',
                    'bom.explode',
                ],
            ],
            [
                'key' => 'quality',
                'name' => 'Qualidade',
                'module_focus' => 'audit',
                'permissions' => [
                    'genealogy.trace',
                    'inventory.lots.read',
                    'inventory.lots.trace',
                    'inventory.serials.read',
                    'inventory.serials.trace',
                    'eco.read',
                    'eco.impact.read',
                ],
            ],
            [
                'key' => 'financial',
                'name' => 'Financeiro',
                'module_focus' => 'financial',
                'permissions' => [
                    'financial.read',
                    'reports.read',
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $template = RoleTemplate::query()->updateOrCreate(
                ['key' => $templateData['key']],
                [
                    'name' => $templateData['name'],
                    'module_focus' => $templateData['module_focus'],
                    'is_active' => true,
                    'current_version' => 1,
                ]
            );

            RoleTemplateVersion::query()->updateOrCreate(
                [
                    'role_template_id' => $template->id,
                    'version' => 1,
                ],
                [
                    'display_name' => $templateData['name'].' v1',
                    'permissions' => $templateData['permissions'],
                    'notes' => 'Versão inicial seed.',
                    'published_at' => now(),
                ]
            );
        }
    }
}
