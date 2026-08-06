<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class BoatManufacturingDemoSeeder extends Seeder
{
    private const COMPANY_ID = 3;

    private const DEMO_DATE = '2026-08-06';

    private int $userId;

    /** @var array<string, int> */
    private array $id = [];

    public function run(): void
    {
        if (! DB::table('companies')->where('id', self::COMPANY_ID)->exists()) {
            throw new RuntimeException('Company 3 must exist before running BoatManufacturingDemoSeeder.');
        }

        $this->userId = (int) DB::table('company_user')
            ->where('company_id', self::COMPANY_ID)
            ->orderBy('user_id')
            ->value('user_id');

        if ($this->userId <= 0) {
            throw new RuntimeException('Company 3 must have at least one user.');
        }

        DB::transaction(function (): void {
            $this->seedFoundation();
            $this->seedEngineering();
            $this->seedCommercial();
            $this->seedInventory();
            $this->seedProduction();
            $this->seedPlanningAndAnalytics();
        });

        $this->assertEveryTenantTableHasData();
    }

    private function seedFoundation(): void
    {
        $this->id['unit_un'] = $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => 'UN'], [
            'name' => 'Unidade', 'is_active' => true,
        ]);
        $this->id['unit_kg'] = $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => 'KG'], [
            'name' => 'Quilograma', 'is_active' => true,
        ]);
        $this->id['unit_m2'] = $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => 'M2'], [
            'name' => 'Metro quadrado', 'is_active' => true,
        ]);

        $this->id['category_boat'] = $this->upsertId('product_categories', ['company_id' => self::COMPANY_ID, 'code' => 'EMBARCACOES'], [
            'name' => 'Embarcações', 'description' => 'Barcos e conjuntos navais acabados', 'is_active' => true,
        ]);
        $this->id['category_material'] = $this->upsertId('product_categories', ['company_id' => self::COMPANY_ID, 'code' => 'MATERIAIS-NAVAIS'], [
            'name' => 'Materiais navais', 'description' => 'Matérias-primas e componentes para construção naval', 'is_active' => true,
        ]);
        $this->id['brand'] = $this->upsertId('product_brands', ['company_id' => self::COMPANY_ID, 'code' => 'OCEANCRAFT'], [
            'name' => 'OceanCraft', 'description' => 'Linha demonstrativa de embarcações', 'is_active' => true,
        ]);

        $this->id['plant'] = $this->upsertId('plants', ['company_id' => self::COMPANY_ID, 'code' => 'ESTALEIRO-SC'], [
            'name' => 'Estaleiro Santa Catarina', 'timezone' => 'America/Sao_Paulo', 'is_active' => true,
        ]);
        $this->id['warehouse_raw'] = $this->upsertId('warehouses', ['company_id' => self::COMPANY_ID, 'code' => 'MP-NAVAL'], [
            'plant_id' => $this->id['plant'], 'name' => 'Matérias-primas navais', 'is_active' => true,
        ]);
        $this->id['warehouse_fg'] = $this->upsertId('warehouses', ['company_id' => self::COMPANY_ID, 'code' => 'PA-BARCOS'], [
            'plant_id' => $this->id['plant'], 'name' => 'Embarcações acabadas', 'is_active' => true,
        ]);

        $this->upsertId('account_invitations', ['company_id' => self::COMPANY_ID, 'email' => 'engenharia.demo@oceancraft.local'], [
            'invited_by_user_id' => $this->userId, 'name' => 'Engenheiro Naval Demo', 'role_slug' => 'company-engineer',
            'token' => hash('sha512', 'boat-demo-company-3-invitation'), 'expires_at' => now()->addDays(7),
            'meta' => $this->json(['scenario' => 'boat-manufacturing-demo']),
        ]);
        $this->upsertId('audit_logs', ['company_id' => self::COMPANY_ID, 'event' => 'demo.boat_manufacturing.seeded'], [
            'user_id' => $this->userId, 'severity' => 'info',
            'context' => $this->json(['industry' => 'boat_manufacturing']), 'occurred_at' => now(),
            'ip_address' => '127.0.0.1', 'user_agent' => 'BoatManufacturingDemoSeeder',
        ]);
    }

    private function seedEngineering(): void
    {
        $products = [
            'boat' => ['BOAT-280-SPORT', 'Lancha OceanCraft 280 Sport', 'FG', $this->id['unit_un'], true, true, $this->id['category_boat']],
            'hull' => ['HULL-280-WIP', 'Casco laminado 28 pés', 'WIP', $this->id['unit_un'], true, false, $this->id['category_material']],
            'deck' => ['DECK-280-WIP', 'Convés modular 28 pés', 'WIP', $this->id['unit_un'], true, false, $this->id['category_material']],
            'engine' => ['ENGINE-300-HP', 'Motor de popa 300 HP', 'RAW', $this->id['unit_un'], false, true, $this->id['category_material']],
            'resin' => ['RESIN-MARINE-KG', 'Resina poliéster naval', 'RAW', $this->id['unit_kg'], true, false, $this->id['category_material']],
            'fiber' => ['FIBER-600-M2', 'Manta de fibra de vidro 600 g/m²', 'RAW', $this->id['unit_m2'], true, false, $this->id['category_material']],
            'seat' => ['SEAT-NAUTIC', 'Banco náutico estofado', 'RAW', $this->id['unit_un'], false, false, $this->id['category_material']],
            'electric' => ['ELEC-PANEL-12V', 'Painel elétrico marítimo 12 V', 'RAW', $this->id['unit_un'], false, true, $this->id['category_material']],
            'paint' => ['GELCOAT-WHITE-KG', 'Gelcoat naval branco', 'CONSUMABLE', $this->id['unit_kg'], true, false, $this->id['category_material']],
            'reinforcement' => ['TRANSOM-REINF-300', 'Reforço estrutural de espelho de popa', 'RAW', $this->id['unit_un'], false, false, $this->id['category_material']],
            'windshield' => ['WINDSHIELD-280', 'Para-brisa curvo OceanCraft 280', 'RAW', $this->id['unit_un'], false, false, $this->id['category_material']],
        ];

        foreach ($products as $key => [$sku, $description, $type, $unitId, $lot, $serial, $categoryId]) {
            $this->id[$key] = $this->upsertId('products', ['company_id' => self::COMPANY_ID, 'sku' => $sku], [
                'description' => $description, 'product_type' => $type, 'unit_id' => $unitId,
                'category_id' => $categoryId, 'brand_id' => $key === 'boat' ? $this->id['brand'] : null,
                'safety_stock' => in_array($key, ['resin', 'fiber'], true) ? 100 : 0,
                'lead_time_days' => $key === 'engine' ? 30 : 5, 'lot_control' => $lot, 'serial_control' => $serial,
                'is_active' => true, 'lifecycle_status' => 'ACTIVE',
                'technical_attributes' => $this->json(['marine_grade' => true, 'demo' => true]),
            ]);
            $this->upsertId('product_versions', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id[$key], 'version_number' => 1], [
                'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
                'compatibility_rule' => 'BACKWARD', 'change_summary' => 'Versão inicial para cenário demonstrativo de barcos',
                'payload' => $this->json(['sku' => $sku, 'description' => $description, 'marine_grade' => true]),
                'created_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
            ]);
        }

        $centers = [
            'cut' => ['CORTE-CNC', 'Corte CNC e preparação', 'MACHINE', 16],
            'lamination' => ['LAMINACAO', 'Laminação de casco e convés', 'LINE', 16],
            'assembly' => ['MONTAGEM', 'Montagem naval', 'LINE', 16],
            'finish' => ['ACABAMENTO', 'Acabamento e pintura', 'LINE', 16],
            'quality' => ['TESTES', 'Inspeção e testes de água', 'LINE', 8],
        ];
        foreach ($centers as $key => [$code, $name, $type, $capacity]) {
            $this->id['wc_'.$key] = $this->upsertId('work_centers', ['company_id' => self::COMPANY_ID, 'code' => $code], [
                'plant_id' => $this->id['plant'], 'name' => $name, 'resource_type' => $type,
                'capacity_per_day' => $capacity, 'efficiency_factor' => 92, 'is_active' => true,
            ]);
            $this->upsertId('work_center_shifts', ['company_id' => self::COMPANY_ID, 'work_center_id' => $this->id['wc_'.$key], 'name' => 'Turno diurno'], [
                'shift_start' => '07:30:00', 'shift_end' => '16:30:00', 'capacity_hours' => 8, 'is_active' => true,
            ]);
            $this->upsertId('work_center_hour_rates', ['company_id' => self::COMPANY_ID, 'work_center_id' => $this->id['wc_'.$key], 'effective_from' => now()->startOfYear()->toDateString()], [
                'hourly_rate' => 180 + ($capacity * 5), 'currency' => 'BRL', 'status' => 'ACTIVE',
                'approved_by' => $this->userId, 'approved_at' => now()->startOfYear(),
                'change_reason' => 'Custo padrão do cenário de fabricação naval',
            ]);
        }

        $resources = [
            'cnc' => ['CNC-NAVAL-01', 'Router CNC Naval 5 eixos', 'MACHINE', 'cut'],
            'mold' => ['MOLDE-CASCO-280', 'Molde de casco OceanCraft 280', 'TOOL', 'lamination'],
            'line' => ['LINHA-MONT-01', 'Linha de montagem naval 01', 'LINE', 'assembly'],
            'booth' => ['CABINE-PINT-01', 'Cabine de pintura naval', 'EQUIPMENT', 'finish'],
            'tank' => ['TANQUE-TESTE-01', 'Tanque de teste de estanqueidade', 'EQUIPMENT', 'quality'],
        ];
        foreach ($resources as $key => [$code, $name, $type, $center]) {
            $this->id['resource_'.$key] = $this->upsertId('production_resources', ['company_id' => self::COMPANY_ID, 'code' => $code], [
                'plant_id' => $this->id['plant'], 'work_center_id' => $this->id['wc_'.$center],
                'name' => $name, 'resource_type' => $type, 'status' => 'ACTIVE', 'capacity_per_day' => 8,
                'efficiency_factor' => 95, 'effective_from' => now()->subYear()->toDateString(),
                'metadata' => $this->json(['manufacturer' => 'Demo Marine Equipment']),
            ]);
        }

        $this->id['bom'] = $this->upsertId('bom_headers', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['boat'], 'version_number' => 1], [
            'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
            'description' => 'Estrutura padrão da lancha OceanCraft 280 Sport',
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
        ]);
        $bomComponents = [['hull', 1, 1], ['deck', 2, 1], ['engine', 3, 1], ['seat', 4, 6], ['electric', 5, 1], ['windshield', 6, 1]];
        foreach ($bomComponents as [$component, $line, $quantity]) {
            $this->upsertId('bom_items', ['bom_header_id' => $this->id['bom'], 'line_no' => $line], [
                'company_id' => self::COMPANY_ID, 'component_product_id' => $this->id[$component],
                'unit_id' => DB::table('products')->where('id', $this->id[$component])->value('unit_id'),
                'quantity_per' => $quantity,
            ]);
        }

        $substructures = [
            'bom_hull' => ['hull', 'Subestrutura: casco laminado 28 pés', [
                ['resin', 1, 120], ['fiber', 2, 250], ['paint', 3, 18], ['reinforcement', 4, 1],
            ]],
            'bom_deck' => ['deck', 'Subestrutura: convés modular 28 pés', [
                ['resin', 1, 35], ['fiber', 2, 85], ['electric', 3, 1], ['seat', 4, 6], ['windshield', 5, 1],
            ]],
        ];
        foreach ($substructures as $bomKey => [$product, $description, $components]) {
            $this->id[$bomKey] = $this->upsertId('bom_headers', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id[$product], 'version_number' => 1], [
                'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
                'description' => $description, 'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
            ]);
            foreach ($components as [$component, $line, $quantity]) {
                $this->upsertId('bom_items', ['bom_header_id' => $this->id[$bomKey], 'line_no' => $line], [
                    'company_id' => self::COMPANY_ID, 'component_product_id' => $this->id[$component],
                    'unit_id' => DB::table('products')->where('id', $this->id[$component])->value('unit_id'),
                    'quantity_per' => $quantity,
                ]);
            }
        }

        $this->id['routing'] = $this->upsertId('routing_versions', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['boat'], 'version_number' => 1], [
            'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
            'description' => 'Roteiro padrão de construção da OceanCraft 280',
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
        ]);
        $operations = [
            ['cut', 10, 'CORTE', 'Corte de reforços e painéis', 1, 45, 180, 30, 15, 'cnc'],
            ['lamination', 20, 'LAMINAR', 'Laminação do casco', 2, 120, 960, 240, 30, 'mold'],
            ['assembly', 30, 'MONTAR', 'Montagem estrutural e motorização', 3, 90, 720, 120, 30, 'line'],
            ['finish', 40, 'ACABAR', 'Gelcoat, pintura e acabamento', 4, 60, 480, 120, 20, 'booth'],
            ['quality', 50, 'TESTAR', 'Inspeção final e teste de água', 5, 30, 180, 30, 10, 'tank'],
        ];
        foreach ($operations as [$center, $number, $code, $name, $sequence, $setup, $runtime, $queue, $move, $resource]) {
            $opId = $this->upsertId('routing_operations', ['routing_version_id' => $this->id['routing'], 'operation_no' => $number], [
                'company_id' => self::COMPANY_ID, 'work_center_id' => $this->id['wc_'.$center],
                'operation_code' => $code, 'operation_name' => $name, 'sequence' => $sequence,
                'setup_time_minutes' => $setup, 'runtime_minutes' => $runtime,
                'queue_time_minutes' => $queue, 'move_time_minutes' => $move, 'is_outsourced' => false,
            ]);
            $this->id['routing_op_'.$sequence] = $opId;
            $this->id['std_'.$sequence] = $this->upsertId('routing_operation_standard_times', ['company_id' => self::COMPANY_ID, 'routing_operation_id' => $opId, 'version_number' => 1], [
                'status' => 'APPROVED', 'time_basis' => 'PER_PROCESS', 'setup_scope' => $sequence === 1 ? 'ROUTING' : 'OPERATION',
                'base_quantity' => 1, 'setup_time_minutes' => $setup, 'runtime_minutes' => $runtime,
                'queue_time_minutes' => $queue, 'move_time_minutes' => $move, 'efficiency_factor' => 100,
                'yield_factor' => 100, 'effective_from' => now()->subMonths(3)->toDateString(),
                'created_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
                'change_reason' => 'Tempos homologados para demonstração naval',
            ]);
            $this->id['op_resource_'.$sequence] = $this->id['resource_'.$resource];
        }

        $hash = hash('sha256', 'boat-demo-routing-v1');
        $this->id['routing_snapshot'] = $this->upsertId('routing_version_snapshots', ['company_id' => self::COMPANY_ID, 'routing_version_id' => $this->id['routing']], [
            'product_id' => $this->id['boat'], 'version_number' => 1, 'status' => 'APPROVED',
            'effective_from' => now()->subMonths(3)->toDateString(), 'description' => 'Snapshot do roteiro naval v1',
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3), 'frozen_at' => now()->subMonths(3),
            'snapshot_hash' => $hash, 'created_by' => $this->userId,
        ]);
        foreach ($operations as $operation) {
            [$center, $number, $code, $name, $sequence, $setup, $runtime, $queue, $move] = $operation;
            $this->upsertId('routing_operation_snapshots', ['routing_version_snapshot_id' => $this->id['routing_snapshot'], 'operation_no' => $number], [
                'company_id' => self::COMPANY_ID, 'routing_version_id' => $this->id['routing'],
                'standard_time_id' => $this->id['std_'.$sequence], 'standard_time_version' => 1,
                'work_center_id' => $this->id['wc_'.$center], 'operation_code' => $code,
                'operation_name' => $name, 'sequence' => $sequence, 'setup_time_minutes' => $setup,
                'runtime_minutes' => $runtime, 'queue_time_minutes' => $queue, 'move_time_minutes' => $move,
                'is_outsourced' => false,
            ]);
        }

        $this->id['eco'] = $this->upsertId('engineering_change_orders', ['company_id' => self::COMPANY_ID, 'eco_number' => 'ECO-BOAT-0001'], [
            'title' => 'Reforço do espelho de popa para motor 300 HP',
            'description' => 'Atualização demonstrativa de engenharia para reforço estrutural.',
            'status' => 'IMPLEMENTED', 'effective_from' => now()->subMonth()->toDateString(),
            'requested_by' => $this->userId, 'submitted_by' => $this->userId, 'submitted_at' => now()->subMonths(2),
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(2)->addDay(),
            'implemented_by' => $this->userId, 'implemented_at' => now()->subMonth(),
            'impact_summary' => $this->json(['open_orders' => 2, 'risk' => 'LOW']),
        ]);
        $this->upsertId('engineering_change_order_lines', ['company_id' => self::COMPANY_ID, 'engineering_change_order_id' => $this->id['eco'], 'target_domain' => 'BOM', 'target_entity_id' => $this->id['bom']], [
            'change_type' => 'VERSION_CHANGE', 'from_version_number' => 1, 'to_version_number' => 1,
            'effective_from' => now()->subMonth()->toDateString(), 'impact_level' => 'MEDIUM',
            'change_summary' => 'Reforço estrutural homologado no conjunto do casco.',
        ]);
    }

    private function seedCommercial(): void
    {
        $this->id['supplier'] = $this->upsertId('suppliers', ['company_id' => self::COMPANY_ID, 'code' => 'SUP-MARINE-01'], [
            'name' => 'Marine Power Brasil', 'person_type' => 'PJ',
            'email' => 'vendas@marinepower.demo', 'phone' => '+55 47 3333-1000', 'status' => 'ACTIVE',
            'default_lead_time_days' => 30, 'payment_terms' => '30/60 dias',
        ]);
        $this->upsertId('supplier_products', ['company_id' => self::COMPANY_ID, 'supplier_id' => $this->id['supplier'], 'product_id' => $this->id['engine']], [
            'supplier_sku' => 'MP-OUTBOARD-300', 'moq' => 1, 'lead_time_days' => 30,
            'unit_price' => 118000, 'is_preferred' => true, 'is_active' => true,
        ]);
        $this->id['supplier_composites'] = $this->upsertId('suppliers', ['company_id' => self::COMPANY_ID, 'code' => 'SUP-COMPOSITES-01'], [
            'name' => 'Compósitos do Atlântico', 'email' => 'comercial@compositos.demo', 'phone' => '+55 48 3333-1100',
            'status' => 'ACTIVE', 'default_lead_time_days' => 12, 'payment_terms' => '28 dias',
        ]);
        $this->id['supplier_outfitting'] = $this->upsertId('suppliers', ['company_id' => self::COMPANY_ID, 'code' => 'SUP-NAUTIC-01'], [
            'name' => 'Náutica Equipamentos Sul', 'email' => 'vendas@nauticaequipamentos.demo', 'phone' => '+55 48 3333-1200',
            'status' => 'ACTIVE', 'default_lead_time_days' => 15, 'payment_terms' => '30 dias',
        ]);
        foreach ([
            ['supplier_composites', 'resin', 'CA-RES-01', 26], ['supplier_composites', 'fiber', 'CA-FIB-600', 19],
            ['supplier_composites', 'paint', 'CA-GEL-W', 42], ['supplier_composites', 'reinforcement', 'CA-REF-300', 1800],
            ['supplier_outfitting', 'seat', 'NES-SEAT-01', 2400], ['supplier_outfitting', 'electric', 'NES-PANEL-12', 3200],
            ['supplier_outfitting', 'windshield', 'NES-WIND-280', 7800],
        ] as [$supplierKey, $product, $supplierSku, $price]) {
            $this->upsertId('supplier_products', ['company_id' => self::COMPANY_ID, 'supplier_id' => $this->id[$supplierKey], 'product_id' => $this->id[$product]], [
                'supplier_sku' => $supplierSku, 'moq' => 1, 'lead_time_days' => $supplierKey === 'supplier_composites' ? 12 : 15,
                'unit_price' => $price, 'is_preferred' => true, 'is_active' => true,
            ]);
        }

        $this->id['requisition'] = $this->upsertId('purchase_requisitions', ['company_id' => self::COMPANY_ID, 'requisition_number' => 'REQ-BOAT-0001'], [
            'status' => 'APPROVED', 'required_date' => now()->addDays(20)->toDateString(), 'source_type' => 'MRP',
            'requested_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subDays(10),
            'notes' => 'Motor para próxima lancha OceanCraft 280.',
        ]);
        $this->id['requisition_line'] = $this->upsertId('purchase_requisition_lines', ['company_id' => self::COMPANY_ID, 'purchase_requisition_id' => $this->id['requisition'], 'product_id' => $this->id['engine']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'supplier_id' => $this->id['supplier'],
            'suggested_quantity' => 1, 'requested_quantity' => 1, 'moq_applied' => 1, 'lead_time_days' => 30,
            'need_by_date' => now()->addDays(20)->toDateString(), 'order_date' => now()->subDays(10)->toDateString(),
            'status' => 'CONVERTED', 'source_requirement_key' => 'DEMO-BOAT-ENGINE-REQ',
            'mrp_reference_date' => now()->subDays(10)->toDateString(),
        ]);
        $this->id['quotation'] = $this->upsertId('purchase_quotations', ['company_id' => self::COMPANY_ID, 'quotation_number' => 'COT-BOAT-0001'], [
            'purchase_requisition_id' => $this->id['requisition'], 'supplier_id' => $this->id['supplier'],
            'quotation_date' => now()->subDays(9)->toDateString(), 'valid_until' => now()->addDays(6)->toDateString(),
            'status' => 'APPROVED', 'received_by' => $this->userId, 'received_at' => now()->subDays(8),
            'approved_by' => $this->userId, 'approved_at' => now()->subDays(7), 'amount_cents' => 11800000,
        ]);
        $this->upsertId('purchase_quotation_lines', ['company_id' => self::COMPANY_ID, 'purchase_quotation_id' => $this->id['quotation'], 'product_id' => $this->id['engine']], [
            'purchase_requisition_line_id' => $this->id['requisition_line'], 'quantity' => 1, 'unit_price' => 118000,
            'notes' => 'Motor, comando eletrônico e hélice inclusos.',
        ]);
        $this->id['purchase_order'] = $this->upsertId('purchase_orders', ['company_id' => self::COMPANY_ID, 'purchase_order_number' => 'PC-BOAT-0001'], [
            'supplier_id' => $this->id['supplier'], 'purchase_requisition_id' => $this->id['requisition'],
            'status' => 'APPROVED', 'order_date' => now()->subDays(7)->toDateString(),
            'expected_delivery_date' => now()->addDays(20)->toDateString(), 'created_by' => $this->userId,
            'approved_by' => $this->userId, 'approved_at' => now()->subDays(7), 'notes' => 'Compra do conjunto propulsor.',
        ]);
        $this->id['purchase_order_line'] = $this->upsertId('purchase_order_lines', ['company_id' => self::COMPANY_ID, 'purchase_order_id' => $this->id['purchase_order'], 'product_id' => $this->id['engine']], [
            'purchase_requisition_line_id' => $this->id['requisition_line'], 'warehouse_id' => $this->id['warehouse_raw'],
            'quantity_ordered' => 1, 'quantity_received' => 1, 'unit_price' => 118000,
            'need_by_date' => now()->addDays(20)->toDateString(), 'promised_date' => now()->subDays(2)->toDateString(),
            'status' => 'RECEIVED',
        ]);
        $this->id['receipt'] = $this->upsertId('purchase_receipts', ['company_id' => self::COMPANY_ID, 'receipt_number' => 'REC-BOAT-0001'], [
            'purchase_order_id' => $this->id['purchase_order'], 'supplier_id' => $this->id['supplier'],
            'receipt_date' => now()->subDays(2)->toDateString(), 'status' => 'POSTED',
            'posted_by' => $this->userId, 'posted_at' => now()->subDays(2), 'notes' => 'Motor recebido e inspecionado.',
        ]);

        foreach ([
            ['REQ-BOAT-0002', 'PC-BOAT-0002', 'supplier_composites', [['resin', 500, 26], ['fiber', 900, 19], ['paint', 120, 42], ['reinforcement', 3, 1800]]],
            ['REQ-BOAT-0003', 'PC-BOAT-0003', 'supplier_outfitting', [['seat', 24, 2400], ['electric', 4, 3200], ['windshield', 2, 7800]]],
        ] as [$requisitionNumber, $purchaseOrderNumber, $supplierKey, $lines]) {
            $requisitionId = $this->upsertId('purchase_requisitions', ['company_id' => self::COMPANY_ID, 'requisition_number' => $requisitionNumber], [
                'status' => 'APPROVED', 'required_date' => now()->addDays(20)->toDateString(), 'source_type' => 'PRODUCTION',
                'requested_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subDays(10),
                'notes' => 'Reposição de componentes para fabricação das lanchas OceanCraft 280.',
            ]);
            $purchaseOrderId = $this->upsertId('purchase_orders', ['company_id' => self::COMPANY_ID, 'purchase_order_number' => $purchaseOrderNumber], [
                'supplier_id' => $this->id[$supplierKey], 'purchase_requisition_id' => $requisitionId, 'status' => 'APPROVED',
                'order_date' => now()->subDays(10)->toDateString(), 'expected_delivery_date' => now()->addDays(10)->toDateString(),
                'created_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subDays(9),
                'notes' => 'Compra demonstrativa de componentes da estrutura multinível.',
            ]);
            foreach ($lines as [$product, $quantity, $price]) {
                $lineId = $this->upsertId('purchase_requisition_lines', ['company_id' => self::COMPANY_ID, 'purchase_requisition_id' => $requisitionId, 'product_id' => $this->id[$product]], [
                    'warehouse_id' => $this->id['warehouse_raw'], 'supplier_id' => $this->id[$supplierKey],
                    'suggested_quantity' => $quantity, 'requested_quantity' => $quantity, 'moq_applied' => 1, 'lead_time_days' => 15,
                    'need_by_date' => now()->addDays(20)->toDateString(), 'order_date' => now()->subDays(10)->toDateString(), 'status' => 'CONVERTED',
                ]);
                $this->upsertId('purchase_order_lines', ['company_id' => self::COMPANY_ID, 'purchase_order_id' => $purchaseOrderId, 'product_id' => $this->id[$product]], [
                    'purchase_requisition_line_id' => $lineId, 'warehouse_id' => $this->id['warehouse_raw'],
                    'quantity_ordered' => $quantity, 'quantity_received' => 0, 'unit_price' => $price,
                    'need_by_date' => now()->addDays(20)->toDateString(), 'promised_date' => now()->addDays(10)->toDateString(), 'status' => 'OPEN',
                ]);
            }
        }

        $this->id['customer'] = $this->upsertId('customers', ['company_id' => self::COMPANY_ID, 'code' => 'CLI-MARINA-01'], [
            'name' => 'Marina Costa Azul', 'person_type' => 'PJ',
            'email' => 'compras@marinacostaazul.demo', 'phone' => '+55 48 3222-2000', 'status' => 'ACTIVE',
            'metadata' => $this->json(['segment' => 'marina', 'city' => 'Florianópolis']),
        ]);
        $this->id['sale'] = $this->upsertId('sales', ['company_id' => self::COMPANY_ID, 'customer_id' => $this->id['customer'], 'notes' => 'Lancha personalizada com kit de navegação costeira.'], [
            'sale_date' => now()->subDays(45)->toDateString(),
            'status' => 'CONFIRMED', 'confirmed_by' => $this->userId, 'confirmed_at' => now()->subDays(44),
            'operational_status' => 'DELIVERED', 'picking_by' => $this->userId, 'picking_at' => now()->subDays(5),
            'invoiced_by' => $this->userId, 'invoiced_at' => now()->subDays(4), 'shipped_by' => $this->userId,
            'shipped_at' => now()->subDays(3), 'delivered_by' => $this->userId, 'delivered_at' => now()->subDays(2),
            'subtotal_cents' => 68500000, 'discount_cents' => 1500000, 'amount_cents' => 67000000,
            'notes' => 'Lancha personalizada com kit de navegação costeira.',
        ]);
        $this->upsertId('sale_lines', ['company_id' => self::COMPANY_ID, 'sale_id' => $this->id['sale'], 'product_id' => $this->id['boat']], [
            'quantity' => 1, 'unit_price' => 670000, 'metadata' => $this->json(['color' => 'branco e azul']),
        ]);
        $this->id['components_sale'] = $this->upsertId('sales', ['company_id' => self::COMPANY_ID, 'customer_id' => $this->id['customer'], 'notes' => 'Venda demonstrativa de componentes náuticos para manutenção.'], [
            'sale_date' => now()->subDays(12)->toDateString(), 'status' => 'CONFIRMED', 'confirmed_by' => $this->userId,
            'confirmed_at' => now()->subDays(12), 'operational_status' => 'DELIVERED', 'picking_by' => $this->userId,
            'picking_at' => now()->subDays(11), 'invoiced_by' => $this->userId, 'invoiced_at' => now()->subDays(11),
            'shipped_by' => $this->userId, 'shipped_at' => now()->subDays(10), 'delivered_by' => $this->userId,
            'delivered_at' => now()->subDays(10), 'subtotal_cents' => 2966800, 'discount_cents' => 0, 'amount_cents' => 2966800,
            'notes' => 'Venda demonstrativa de componentes náuticos para manutenção.',
        ]);
        foreach ([['resin', 20, 36], ['fiber', 30, 28], ['paint', 5, 62], ['seat', 2, 3600], ['electric', 1, 4800], ['windshield', 1, 11200]] as [$product, $quantity, $price]) {
            $this->upsertId('sale_lines', ['company_id' => self::COMPANY_ID, 'sale_id' => $this->id['components_sale'], 'product_id' => $this->id[$product]], [
                'quantity' => $quantity, 'unit_price' => $price, 'metadata' => $this->json(['purpose' => 'manutenção náutica']),
            ]);
        }
    }

    private function seedInventory(): void
    {
        $this->id['receipt_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'boat_demo_initial_stock', 'reference_id' => 1], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'movement_type' => 'RECEIPT',
            'target_bucket' => 'AVAILABLE', 'quantity' => 500, 'allocation_strategy' => 'FIFO', 'lot_number' => 'RES-2026-0801',
            'reference_type' => 'boat_demo_initial_stock', 'reference_id' => 1, 'notes' => 'Estoque inicial de resina naval.',
            'movement_at' => now()->subDays(30), 'created_by' => $this->userId,
        ]);
        $this->id['engine_receipt_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'purchase_receipt', 'reference_id' => $this->id['receipt']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['engine'], 'movement_type' => 'RECEIPT',
            'target_bucket' => 'AVAILABLE', 'quantity' => 1, 'allocation_strategy' => 'SPECIFIC',
            'reference_type' => 'purchase_receipt', 'reference_id' => $this->id['receipt'], 'notes' => 'Recebimento do motor 300 HP.',
            'movement_at' => now()->subDays(2), 'created_by' => $this->userId,
        ]);
        $this->upsertId('purchase_receipt_lines', ['company_id' => self::COMPANY_ID, 'purchase_receipt_id' => $this->id['receipt'], 'product_id' => $this->id['engine']], [
            'purchase_order_line_id' => $this->id['purchase_order_line'], 'warehouse_id' => $this->id['warehouse_raw'],
            'quantity_received' => 1, 'stock_ledger_movement_id' => $this->id['engine_receipt_movement'], 'notes' => 'Serial conferido no recebimento.',
        ]);
        foreach ([['resin', 420, 20], ['fiber', 800, 50], ['engine', 1, 0], ['seat', 24, 6], ['electric', 4, 1], ['paint', 90, 5], ['boat', 1, 0]] as [$product, $available, $reserved]) {
            $warehouse = $product === 'boat' ? $this->id['warehouse_fg'] : $this->id['warehouse_raw'];
            $this->upsertId('inventory_balances', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $warehouse, 'product_id' => $this->id[$product]], [
                'qty_available' => $available, 'qty_reserved' => $reserved, 'qty_in_transit' => 0, 'qty_inspection' => 0,
                'last_movement_at' => now()->subDays(2),
            ]);
        }
        $this->id['resin_lot'] = $this->upsertId('inventory_lots', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'lot_number' => 'RES-2026-0801'], [
            'manufactured_at' => now()->subMonths(2)->toDateString(), 'expires_at' => now()->addYear()->toDateString(),
            'status' => 'ACTIVE', 'source_movement_id' => $this->id['receipt_movement'], 'metadata' => $this->json(['certificate' => 'CERT-RES-0801']),
        ]);
        $this->upsertId('inventory_serials', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['engine'], 'serial_number' => 'ENG300-DEMO-0001'], [
            'warehouse_id' => $this->id['warehouse_raw'], 'status' => 'ACTIVE', 'source_movement_id' => $this->id['engine_receipt_movement'],
            'metadata' => $this->json(['warranty_months' => 36]),
        ]);
        $this->upsertId('inventory_reservations', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['engine'], 'reference_type' => 'sale', 'reference_id' => $this->id['sale']], [
            'reservation_origin' => 'SALES_ORDER', 'priority' => 10, 'quantity' => 1, 'status' => 'RESERVED',
            'reserved_at' => now()->subDays(5), 'expires_at' => now()->addDays(10), 'created_by' => $this->userId,
        ]);
    }

    private function seedProduction(): void
    {
        $this->id['order'] = $this->upsertId('production_orders', ['company_id' => self::COMPANY_ID, 'order_number' => 'OP-BOAT-0001'], [
            'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'],
            'bom_header_id' => $this->id['bom'], 'bom_version_number' => 1, 'routing_version_id' => $this->id['routing'],
            'routing_version_number' => 1, 'source_type' => 'MRP', 'source_reference_type' => 'boat_demo_demand',
            'source_reference_id' => $this->id['sale'], 'status' => 'COMPLETED', 'quantity_planned' => 1,
            'quantity_produced' => 1, 'quantity_scrapped' => 0, 'scheduled_start_date' => now()->subDays(20)->toDateString(),
            'scheduled_end_date' => now()->subDays(3)->toDateString(), 'released_at' => now()->subDays(21),
            'started_at' => now()->subDays(20), 'completed_at' => now()->subDays(3),
            'created_by' => $this->userId, 'released_by' => $this->userId, 'completed_by' => $this->userId,
            'metadata' => $this->json(['customer' => 'Marina Costa Azul', 'model' => 'OceanCraft 280 Sport']),
        ]);
        $hash = hash('sha256', 'boat-demo-order-0001');
        $this->id['bom_snapshot'] = $this->upsertId('production_order_bom_snapshots', ['company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order']], [
            'product_id' => $this->id['boat'], 'production_order_quantity' => 1, 'reference_date' => now()->subDays(21)->toDateString(),
            'source_bom_header_id' => $this->id['bom'], 'source_bom_version_number' => 1, 'snapshot_hash' => $hash,
            'has_cycle' => false, 'frozen_at' => now()->subDays(21), 'created_by' => $this->userId,
        ]);
        $bomComponents = [['hull', 1, 1], ['deck', 2, 1], ['engine', 3, 1], ['seat', 4, 6], ['electric', 5, 1], ['paint', 6, 18]];
        foreach ($bomComponents as [$component, $line, $quantity]) {
            $this->upsertId('production_order_bom_item_snapshots', ['production_order_bom_snapshot_id' => $this->id['bom_snapshot'], 'level' => 1, 'parent_product_id' => $this->id['boat'], 'component_product_id' => $this->id[$component], 'line_no' => $line], [
                'company_id' => self::COMPANY_ID, 'source_bom_header_id' => $this->id['bom'], 'source_bom_version_number' => 1,
                'quantity_per' => $quantity, 'quantity_required' => $quantity,
                'quantity_accumulated' => $quantity, 'path' => $this->id['boat'].'>'.$this->id[$component], 'is_cycle' => false,
            ]);
        }
        $this->id['order_snapshot'] = $this->upsertId('production_order_snapshots', ['company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order']], [
            'product_id' => $this->id['boat'], 'bom_snapshot_id' => $this->id['bom_snapshot'], 'bom_header_id' => $this->id['bom'],
            'bom_version_number' => 1, 'routing_version_snapshot_id' => $this->id['routing_snapshot'],
            'routing_version_id' => $this->id['routing'], 'routing_version_number' => 1, 'quantity_planned' => 1,
            'quantity_scrapped_target' => 0, 'snapshot_hash' => $hash, 'frozen_at' => now()->subDays(21), 'frozen_by' => $this->userId,
        ]);

        $operationData = [
            ['cut', 10, 'CORTE', 'Corte de reforços e painéis', 1, 45, 180, 30, 15],
            ['lamination', 20, 'LAMINAR', 'Laminação do casco', 2, 120, 960, 240, 30],
            ['assembly', 30, 'MONTAR', 'Montagem estrutural e motorização', 3, 90, 720, 120, 30],
            ['finish', 40, 'ACABAR', 'Gelcoat, pintura e acabamento', 4, 60, 480, 120, 20],
            ['quality', 50, 'TESTAR', 'Inspeção final e teste de água', 5, 30, 180, 30, 10],
        ];
        foreach ($operationData as [$center, $number, $code, $name, $sequence, $setup, $runtime, $queue, $move]) {
            $snapshotId = $this->upsertId('production_order_routing_operation_snapshots', ['production_order_snapshot_id' => $this->id['order_snapshot'], 'sequence' => $sequence], [
                'company_id' => self::COMPANY_ID, 'routing_version_id' => $this->id['routing'],
                'standard_time_id' => $this->id['std_'.$sequence], 'standard_time_version' => 1,
                'work_center_id' => $this->id['wc_'.$center], 'operation_no' => $number, 'operation_code' => $code,
                'operation_name' => $name, 'setup_time_minutes' => $setup, 'runtime_minutes' => $runtime,
                'queue_time_minutes' => $queue, 'move_time_minutes' => $move, 'is_outsourced' => false,
            ]);
            $start = now()->subDays(20)->addDays(($sequence - 1) * 3);
            $end = $start->copy()->addMinutes($setup + $runtime + $queue + $move);
            $opId = $this->upsertId('production_order_operations', ['company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order'], 'sequence' => $sequence], [
                'production_order_routing_operation_snapshot_id' => $snapshotId, 'routing_operation_id' => $this->id['routing_op_'.$sequence],
                'standard_time_id' => $this->id['std_'.$sequence], 'standard_time_version' => 1,
                'operation_no' => $number, 'operation_code' => $code, 'operation_name' => $name,
                'work_center_id' => $this->id['wc_'.$center], 'production_resource_id' => $this->id['op_resource_'.$sequence],
                'actual_production_resource_id' => $this->id['op_resource_'.$sequence], 'operator_id' => $this->userId,
                'status' => 'COMPLETED', 'quantity_planned' => 1, 'setup_scope' => $sequence === 1 ? 'ROUTING' : 'OPERATION',
                'setup_time_minutes' => $setup, 'runtime_time_minutes' => $runtime, 'queue_time_minutes' => $queue,
                'move_time_minutes' => $move, 'productive_time_minutes' => $setup + $runtime,
                'lead_time_minutes' => $queue + $move, 'total_time_minutes' => $setup + $runtime + $queue + $move,
                'planned_start_at' => $start, 'planned_end_at' => $end, 'quantity_processed' => 1,
                'quantity_good' => 1, 'quantity_scrapped' => $sequence === 4 ? 0.02 : 0,
                'quantity_rework' => $sequence === 4 ? 0.02 : 0, 'actual_productive_minutes' => $setup + $runtime + ($sequence * 8),
                'actual_pause_minutes' => 15, 'actual_started_at' => $start, 'actual_completed_at' => $end->copy()->addMinutes(15),
                'calculation_metadata' => $this->json(['source' => 'boat-demo']),
            ]);
            $this->id['order_op_'.$sequence] = $opId;
            foreach ([['START', 0], ['PAUSE', 60], ['RESUME', 75], ['COMPLETE', $runtime]] as [$event, $minutes]) {
                $this->upsertId('production_operation_events', ['company_id' => self::COMPANY_ID, 'idempotency_key' => 'BOAT-DEMO-'.$opId.'-'.$event], [
                    'production_order_operation_id' => $opId, 'event_type' => $event, 'occurred_at' => $start->copy()->addMinutes($minutes),
                    'operator_id' => $this->userId, 'production_resource_id' => $this->id['op_resource_'.$sequence],
                    'reason_code' => $event === 'PAUSE' ? 'INTERVALO' : null, 'notes' => 'Evento demonstrativo de execução naval.',
                ]);
            }
            $outputKeys = [
                'company_id' => self::COMPANY_ID,
                'production_order_operation_id' => $opId,
                'notes' => 'Apontamento aprovado do cenário demonstrativo.',
            ];
            $this->removeDuplicateDemoRows('production_operation_outputs', $outputKeys);
            $this->upsertId('production_operation_outputs', $outputKeys, [
                'reported_at' => $end,
                'production_order_id' => $this->id['order'], 'work_center_id' => $this->id['wc_'.$center],
                'setup_time_minutes' => $setup, 'process_time_minutes' => $runtime,
                'quantity_good' => 1, 'quantity_scrapped' => $sequence === 4 ? 0.02 : 0,
                'quantity_rework' => $sequence === 4 ? 0.02 : 0, 'lot_number' => 'BOAT-280-2026-001',
                'inspection_status' => 'APPROVED', 'scrap_cause_code' => $sequence === 4 ? 'GELCOAT-BOLHA' : null,
                'destination' => $sequence === 5 ? 'FINISHED_GOODS' : 'NEXT_OPERATION',
                'inspected_at' => $end, 'inspection_notes' => 'Inspeção da operação aprovada.',
                'operator_id' => $this->userId, 'created_by' => $this->userId,
                'production_resource_id' => $this->id['op_resource_'.$sequence],
                'notes' => 'Apontamento aprovado do cenário demonstrativo.',
            ]);
        }

        $this->id['issue_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'production_order', 'reference_id' => $this->id['order']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'movement_type' => 'ISSUE',
            'source_bucket' => 'AVAILABLE', 'quantity' => 80, 'allocation_strategy' => 'FIFO', 'lot_number' => 'RES-2026-0801',
            'reference_type' => 'production_order', 'reference_id' => $this->id['order'], 'notes' => 'Consumo de resina na laminação.',
            'movement_at' => now()->subDays(17), 'created_by' => $this->userId,
        ]);
        $this->upsertId('stock_ledger_allocations', ['issue_movement_id' => $this->id['issue_movement'], 'receipt_movement_id' => $this->id['receipt_movement'], 'sequence_no' => 1], [
            'company_id' => self::COMPANY_ID, 'quantity' => 80,
        ]);
        $this->id['consumption'] = $this->upsertId('production_order_material_consumptions', ['company_id' => self::COMPANY_ID, 'idempotency_key' => 'BOAT-DEMO-CONS-RESIN'], [
            'production_order_id' => $this->id['order'], 'production_order_operation_id' => $this->id['order_op_2'],
            'product_id' => $this->id['resin'], 'warehouse_id' => $this->id['warehouse_raw'], 'lot_number' => 'RES-2026-0801',
            'quantity_consumed' => 80, 'quantity_scrapped' => 1.5, 'ledger_movement_id' => $this->id['issue_movement'],
            'reference_bom_component_id' => (string) $this->id['bom'], 'consumed_at' => now()->subDays(17),
            'operator_id' => $this->userId, 'notes' => 'Consumo real da laminação do casco.',
        ]);
        $this->id['reversal_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'material_consumption_reversal', 'reference_id' => $this->id['consumption']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'movement_type' => 'RECEIPT',
            'target_bucket' => 'AVAILABLE', 'quantity' => 2, 'lot_number' => 'RES-2026-0801',
            'reference_type' => 'material_consumption_reversal', 'reference_id' => $this->id['consumption'],
            'notes' => 'Estorno de sobra de resina não utilizada.', 'movement_at' => now()->subDays(16), 'created_by' => $this->userId,
        ]);
        DB::table('production_order_material_consumptions')->where('id', $this->id['consumption'])->update(['reversed_by_movement_id' => $this->id['reversal_movement']]);
        $this->upsertId('production_material_consumption_reversals', ['company_id' => self::COMPANY_ID, 'production_order_material_consumption_id' => $this->id['consumption']], [
            'original_ledger_movement_id' => $this->id['issue_movement'], 'reversal_ledger_movement_id' => $this->id['reversal_movement'],
            'quantity' => 2, 'reason' => 'Sobra de material retornada ao estoque', 'created_by' => $this->userId,
        ]);

        $this->id['quality'] = $this->upsertId('production_quality_records', ['company_id' => self::COMPANY_ID, 'production_order_operation_id' => $this->id['order_op_4'], 'record_type' => 'NON_CONFORMITY'], [
            'status' => 'CLOSED', 'quantity' => 0.02, 'cause_code' => 'GELCOAT-BOLHA', 'destination' => 'REWORK',
            'operator_id' => $this->userId, 'production_resource_id' => $this->id['resource_booth'],
            'notes' => 'Pequena bolha corrigida antes da inspeção final.',
        ]);
        $this->upsertId('production_rework_orders', ['company_id' => self::COMPANY_ID, 'source_production_order_operation_id' => $this->id['order_op_4']], [
            'rework_production_order_operation_id' => $this->id['order_op_4'], 'quantity' => 0.02, 'status' => 'COMPLETED',
            'reason_code' => 'GELCOAT-BOLHA', 'notes' => 'Lixamento e reaplicação localizada de gelcoat.',
            'created_by' => $this->userId, 'completed_at' => now()->subDays(5),
        ]);

        $this->id['finished_lot'] = $this->upsertId('inventory_lots', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $this->id['warehouse_fg'], 'product_id' => $this->id['boat'], 'lot_number' => 'BOAT-280-2026-001'], [
            'manufactured_at' => now()->subDays(3)->toDateString(), 'status' => 'ACTIVE', 'source_movement_id' => null,
            'metadata' => $this->json(['production_order' => 'OP-BOAT-0001']),
        ]);
        $this->upsertId('inventory_serials', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['boat'], 'serial_number' => 'HIN-BR-OCC2800001'], [
            'warehouse_id' => $this->id['warehouse_fg'], 'inventory_lot_id' => $this->id['finished_lot'], 'status' => 'SHIPPED',
            'metadata' => $this->json(['hin' => 'BR-OCC2800001', 'customer' => 'Marina Costa Azul']),
        ]);
        $materialNode = $this->upsertId('genealogy_nodes', ['company_id' => self::COMPANY_ID, 'node_type' => 'LOT', 'source_id' => $this->id['resin_lot']], [
            'source_reference' => 'RES-2026-0801', 'product_id' => $this->id['resin'], 'warehouse_id' => $this->id['warehouse_raw'],
        ]);
        $boatNode = $this->upsertId('genealogy_nodes', ['company_id' => self::COMPANY_ID, 'node_type' => 'PRODUCTION_ORDER', 'source_id' => $this->id['order']], [
            'source_reference' => 'OP-BOAT-0001', 'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'],
        ]);
        $this->upsertId('genealogy_relations', ['company_id' => self::COMPANY_ID, 'parent_node_id' => $materialNode, 'child_node_id' => $boatNode, 'relation_type' => 'CONSUMES'], [
            'quantity' => 78, 'uom' => 'KG', 'production_order_id' => $this->id['order'], 'stock_movement_id' => $this->id['issue_movement'],
        ]);
    }

    private function seedPlanningAndAnalytics(): void
    {
        foreach (range(0, 13) as $offset) {
            foreach (['wc_cut', 'wc_lamination', 'wc_assembly', 'wc_finish', 'wc_quality'] as $centerKey) {
                $date = Carbon::parse(self::DEMO_DATE)->addDays($offset);
                $this->upsertId('production_calendar_days', ['company_id' => self::COMPANY_ID, 'work_center_id' => $this->id[$centerKey], 'calendar_date' => $date->toDateString()], [
                    'is_working_day' => ! $date->isWeekend(), 'available_capacity' => $date->isWeekend() ? 0 : 8,
                    'notes' => $date->isWeekend() ? 'Fim de semana' : 'Calendário padrão do estaleiro',
                ]);
            }
        }
        $this->id['mrp_run'] = $this->upsertId('mrp_plan_runs', ['company_id' => self::COMPANY_ID, 'run_key' => 'BOAT-DEMO-MRP-001'], [
            'status' => 'COMPLETED', 'reference_date' => now()->toDateString(), 'planning_bucket' => 'daily',
            'priority_rule' => 'priority_due_date', 'request_payload' => $this->json(['demand' => [['sku' => 'BOAT-280-SPORT', 'quantity' => 2]]]),
            'result_summary' => $this->json(['purchase_count' => 1, 'production_count' => 1]), 'created_by' => $this->userId,
        ]);
        $purchaseSuggestion = $this->upsertId('mrp_suggestions', ['company_id' => self::COMPANY_ID, 'suggestion_key' => 'BOAT-DEMO-MRP-ENGINE'], [
            'mrp_plan_run_id' => $this->id['mrp_run'], 'suggestion_type' => 'PURCHASE', 'status' => 'CONVERTED',
            'product_id' => $this->id['engine'], 'warehouse_id' => $this->id['warehouse_raw'], 'original_quantity' => 1,
            'approved_quantity' => 1, 'need_by_date' => now()->addDays(20)->toDateString(),
            'release_date' => now()->subDays(10)->toDateString(), 'priority' => 10,
            'source_requirement_key' => 'DEMO-BOAT-ENGINE-REQ', 'purchase_requisition_id' => $this->id['requisition'],
            'decision_reason' => 'Motor necessário para atender carteira de barcos.', 'decided_by' => $this->userId,
            'decided_at' => now()->subDays(10), 'converted_at' => now()->subDays(10),
            'original_payload' => $this->json(['net_requirement' => 1]),
        ]);
        $productionSuggestion = $this->upsertId('mrp_suggestions', ['company_id' => self::COMPANY_ID, 'suggestion_key' => 'BOAT-DEMO-MRP-BOAT'], [
            'mrp_plan_run_id' => $this->id['mrp_run'], 'suggestion_type' => 'PRODUCTION', 'status' => 'CONVERTED',
            'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'], 'original_quantity' => 1,
            'approved_quantity' => 1, 'need_by_date' => now()->addDays(30)->toDateString(), 'release_date' => now()->toDateString(),
            'priority' => 20, 'bom_version_number' => 1, 'routing_version_id' => $this->id['routing'],
            'production_order_id' => $this->id['order'], 'decision_reason' => 'Demanda confirmada da Marina Costa Azul.',
            'decided_by' => $this->userId, 'decided_at' => now()->subDays(21), 'converted_at' => now()->subDays(21),
        ]);
        foreach ([[$purchaseSuggestion, 'PURCHASE_CONVERTED'], [$productionSuggestion, 'PRODUCTION_CONVERTED']] as [$suggestionId, $event]) {
            $this->upsertId('mrp_suggestion_events', ['company_id' => self::COMPANY_ID, 'mrp_suggestion_id' => $suggestionId, 'event_type' => $event], [
                'from_status' => 'APPROVED', 'to_status' => 'CONVERTED', 'created_by' => $this->userId,
                'reason' => 'Conversão automática do cenário demonstrativo.', 'payload' => $this->json(['demo' => true]),
            ]);
        }

        $this->id['schedule'] = $this->upsertId('production_schedules', ['company_id' => self::COMPANY_ID, 'schedule_number' => 'PROG-BOAT-0001'], [
            'plant_id' => $this->id['plant'], 'version_number' => 1, 'status' => 'PUBLISHED',
            'reference_date' => now()->subDays(21)->toDateString(), 'mode' => 'finite', 'direction' => 'forward',
            'sequencing_rule' => 'priority_due_date', 'parameters' => $this->json(['include_setup' => true]),
            'source_run_key' => 'BOAT-DEMO-MRP-001', 'created_by' => $this->userId,
            'approved_by' => $this->userId, 'approved_at' => now()->subDays(21),
            'published_by' => $this->userId, 'published_at' => now()->subDays(21),
            'change_reason' => 'Programa demonstrativo do estaleiro.',
        ]);
        foreach (range(1, 5) as $sequence) {
            $operation = DB::table('production_order_operations')->where('id', $this->id['order_op_'.$sequence])->first();
            $this->upsertId('production_schedule_lines', ['production_schedule_id' => $this->id['schedule'], 'production_order_operation_id' => $operation->id], [
                'company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order'],
                'work_center_id' => $operation->work_center_id, 'production_resource_id' => $operation->production_resource_id,
                'planned_start_at' => $operation->planned_start_at, 'planned_end_at' => $operation->planned_end_at,
                'total_time_minutes' => $operation->total_time_minutes, 'capacity_time_minutes' => $operation->productive_time_minutes,
                'lead_time_minutes' => $operation->lead_time_minutes, 'segments' => $this->json([['type' => 'productive', 'minutes' => $operation->productive_time_minutes]]),
                'status' => 'COMPLETED', 'metadata' => $this->json(['demo' => true]),
            ]);
        }
        $this->upsertId('manufacturing_analytics_recommendations', ['company_id' => self::COMPANY_ID, 'production_order_operation_id' => $this->id['order_op_2']], [
            'routing_operation_id' => $this->id['routing_op_2'], 'standard_time_id' => $this->id['std_2'],
            'standard_time_version' => 1, 'status' => 'INVESTIGATE', 'current_time_minutes' => 1080,
            'suggested_time_minutes' => 1020, 'sample_size' => 8,
            'statistics' => $this->json(['mean' => 1045, 'median' => 1020, 'p90' => 1100, 'outliers' => 1]),
            'filters' => $this->json(['product_id' => $this->id['boat'], 'work_center_id' => $this->id['wc_lamination']]),
            'decision_reason' => 'Avaliar redução após estabilização do processo de laminação.',
            'decided_by' => $this->userId, 'decided_at' => now()->subDay(),
        ]);
    }

    /** @param array<string, mixed> $keys @param array<string, mixed> $values */
    private function upsertId(string $table, array $keys, array $values): int
    {
        $timestamp = now();
        $exists = DB::table($table)->where($keys)->exists();
        if (Schema::hasColumn($table, 'updated_at')) {
            $values['updated_at'] = $timestamp;
        }
        if (! $exists && Schema::hasColumn($table, 'created_at')) {
            $values['created_at'] = $timestamp;
        }

        DB::table($table)->updateOrInsert($keys, $values);

        return (int) DB::table($table)->where($keys)->value('id');
    }

    /** @param array<string, mixed> $keys */
    private function removeDuplicateDemoRows(string $table, array $keys): void
    {
        $ids = DB::table($table)->where($keys)->orderBy('id')->pluck('id');

        if ($ids->count() > 1) {
            DB::table($table)->whereIn('id', $ids->slice(1)->all())->delete();
        }
    }

    /** @param array<string, mixed> $value */
    private function json(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function assertEveryTenantTableHasData(): void
    {
        $database = DB::getDatabaseName();
        $tables = DB::select(
            'SELECT TABLE_NAME AS table_name FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = ? ORDER BY TABLE_NAME',
            [$database, 'company_id']
        );
        $empty = [];
        foreach ($tables as $row) {
            $data = array_change_key_case((array) $row, CASE_LOWER);
            $table = (string) $data['table_name'];
            if (! DB::table($table)->where('company_id', self::COMPANY_ID)->exists()) {
                $empty[] = $table;
            }
        }

        if ($empty !== []) {
            throw new RuntimeException('Boat demo did not populate tenant tables: '.implode(', ', $empty));
        }
    }
}
