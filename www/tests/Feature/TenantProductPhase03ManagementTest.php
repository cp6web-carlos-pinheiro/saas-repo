<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Trial;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomItem;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Product\Infrastructure\Persistence\Models\ProductVersion;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantProductPhase03ManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_supports_extended_attributes_and_lifecycle(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $unit = Unit::query()->create([
            'company_id' => $company->id,
            'code' => 'UN',
            'name' => 'Unidade',
            'description' => null,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'metadata' => null,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('products.store'), [
                'sku' => 'P-F03-001',
                'description' => 'Produto Fase 03',
                'product_type' => 'FG',
                'unit_id' => $unit->id,
                'safety_stock' => 5,
                'lead_time_days' => 3,
                'lot_control' => '1',
                'serial_control' => '0',
                'is_active' => '1',
                'lifecycle_status' => 'PHASE_OUT',
                'alternate_uoms_json' => json_encode([
                    ['uom' => 'CX', 'factor' => 10],
                ], JSON_THROW_ON_ERROR),
                'technical_attributes_json' => json_encode(['weight_kg' => 2.3], JSON_THROW_ON_ERROR),
                'commercial_attributes_json' => json_encode(['price_table' => 'A'], JSON_THROW_ON_ERROR),
                'image_urls_json' => json_encode(['https://cdn.example.test/p-f03-001.png'], JSON_THROW_ON_ERROR),
                'attachment_urls_json' => json_encode(['https://cdn.example.test/p-f03-001.pdf'], JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::query()
            ->where('company_id', $company->id)
            ->where('sku', 'P-F03-001')
            ->firstOrFail();

        $this->assertSame('PHASE_OUT', $product->lifecycle_status);
        $this->assertSame('A', (string) ($product->commercial_attributes['price_table'] ?? ''));
        $this->assertSame('https://cdn.example.test/p-f03-001.png', (string) ($product->image_urls[0] ?? ''));
    }

    public function test_product_version_normalizes_variants_and_kits_payload(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $finished = $this->createProduct($company, 'P-VAR-001', 'Produto Variavel', 'FG', 'UN');
        $componentA = $this->createProduct($company, 'C-MAT-001', 'Componente A', 'RAW', 'KG');
        $componentB = $this->createProduct($company, 'C-MAT-002', 'Componente B', 'RAW', 'M');

        $payload = [
            'technical' => ['drawing' => 'A-100'],
            'variants' => [
                'axes' => [
                    'color' => ['Red', 'Blue'],
                    'size' => ['P', 'M'],
                    'model' => [],
                ],
            ],
            'kits' => [
                ['product_id' => $componentA->id, 'quantity' => 2, 'explode_mode' => 'FULL'],
                ['product_id' => $componentB->id, 'quantity' => 1.5, 'explode_mode' => 'OPTIONAL'],
            ],
        ];

        $this->actingAs($user, 'web')
            ->post(route('products.versions.store', $finished), [
                'effective_from' => now()->toDateString(),
                'effective_to' => now()->addYear()->toDateString(),
                'compatibility_rule' => 'FULL',
                'change_summary' => 'Primeira versão com variáveis e kit',
                'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
            ])
            ->assertSessionHasNoErrors();

        $version = ProductVersion::query()
            ->where('company_id', $company->id)
            ->where('product_id', $finished->id)
            ->latest('id')
            ->firstOrFail();

        $variants = $version->payload['variants']['items'] ?? [];
        $kits = $version->payload['kits'] ?? [];

        $this->assertCount(4, $variants);
        $this->assertSame('P-VAR-001-RED-P', $variants[0]['sku']);
        $this->assertCount(2, $kits);
        $this->assertSame('C-MAT-001', $kits[0]['sku']);
    }

    public function test_product_version_rejects_invalid_kit_component(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $finished = $this->createProduct($company, 'P-KIT-SELF', 'Produto Kit', 'FG', 'UN');

        $payload = [
            'variants' => ['axes' => ['color' => ['Black']]],
            'kits' => [
                ['product_id' => $finished->id, 'quantity' => 1, 'explode_mode' => 'FULL'],
            ],
        ];

        $this->actingAs($user, 'web')
            ->from(route('products.versions.create', $finished))
            ->post(route('products.versions.store', $finished), [
                'effective_from' => now()->toDateString(),
                'effective_to' => now()->addYear()->toDateString(),
                'compatibility_rule' => 'FULL',
                'change_summary' => 'Tentativa inválida',
                'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect(route('products.versions.create', $finished))
            ->assertSessionHasErrors('payload_json');
    }

    public function test_approved_bom_versions_cannot_overlap(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $product = $this->createProduct($company, 'BOM-P-001', 'Produto BOM', 'FG', 'UN');
        $component = $this->createProduct($company, 'BOM-C-001', 'Componente BOM', 'RAW', 'UN');

        $this->actingAs($user, 'web')
            ->post(route('bom.material-lists.store'), [
                'product_id' => $product->id,
                'status' => 'APPROVED',
                'effective_from' => '2026-01-01',
                'effective_to' => '2026-12-31',
                'description' => 'Revisão 1',
                'items' => [
                    [
                        'component_product_id' => $component->id,
                        'quantity_per' => 1,
                        'uom' => 'UN',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($user, 'web')
            ->from(route('bom.material-lists.create'))
            ->post(route('bom.material-lists.store'), [
                'product_id' => $product->id,
                'status' => 'APPROVED',
                'effective_from' => '2026-06-01',
                'effective_to' => '2027-02-28',
                'description' => 'Revisão 2',
                'items' => [
                    [
                        'component_product_id' => $component->id,
                        'quantity_per' => 1,
                        'uom' => 'UN',
                    ],
                ],
            ])
            ->assertRedirect(route('bom.material-lists.create'))
            ->assertSessionHasErrors('effective_from');
    }

    public function test_bom_item_always_uses_component_default_unit_even_with_different_payload_unit(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $finishedProduct = $this->createProduct($company, 'BOM-UOM-PARENT', 'Produto pai', 'FG', 'UN');
        $componentProduct = $this->createProduct($company, 'BOM-UOM-COMP', 'Componente com KG', 'RAW', 'KG');
        $differentUnit = Unit::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'M',
            ],
            [
                'name' => 'Metro',
                'description' => null,
                'is_active' => true,
                'metadata' => null,
            ],
        );

        $this->actingAs($user, 'web')
            ->post(route('bom.material-lists.store'), [
                'product_id' => $finishedProduct->id,
                'status' => 'DRAFT',
                'items' => [
                    [
                        'component_product_id' => $componentProduct->id,
                        'quantity_per' => 2.5,
                        'unit_id' => $differentUnit->id,
                    ],
                ],
            ])
            ->assertRedirect();

        $bom = BomHeader::query()
            ->where('company_id', $company->id)
            ->where('product_id', $finishedProduct->id)
            ->latest('id')
            ->firstOrFail();

        $item = BomItem::query()
            ->where('company_id', $company->id)
            ->where('bom_header_id', $bom->id)
            ->firstOrFail();

        $this->assertSame((int) $componentProduct->unit_id, (int) $item->unit_id);
        $this->assertSame('KG', (string) $item->uom);
    }

    public function test_purchase_receipt_requires_lot_for_controlled_product(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'code' => 'PL-001',
            'name' => 'Planta 1',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'code' => 'WH-001',
            'name' => 'Armazém 1',
            'is_active' => true,
        ]);

        $controlledProduct = $this->createProduct($company, 'LOT-REQ-001', 'Produto Rastreado', 'RAW', 'UN', true);

        $this->actingAs($user, 'web')
            ->from(route('purchasing.receipts.create'))
            ->post(route('purchasing.receipts.store'), [
                'receipt_date' => now()->toDateString(),
                'status' => 'DRAFT',
                'items' => [[
                    'purchase_order_line_id' => null,
                    'product_id' => $controlledProduct->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity_received' => 3,
                    'lot_number' => null,
                    'notes' => null,
                ]],
            ])
            ->assertRedirect(route('purchasing.receipts.create'))
            ->assertSessionHasErrors('items.0.lot_number');
    }

    public function test_product_creation_requires_unit_id_consistency(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $this->actingAs($user, 'web')
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'sku' => 'P-NO-UOM-ID',
                'description' => 'Produto sem unidade',
                'product_type' => 'FG',
                'safety_stock' => 0,
                'lead_time_days' => 0,
                'lot_control' => '0',
                'serial_control' => '0',
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('unit_id');

        $this->assertDatabaseMissing('products', [
            'company_id' => $company->id,
            'sku' => 'P-NO-UOM-ID',
        ]);
    }

    public function test_production_order_creation_without_bom_returns_domain_error_instead_of_fatal(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $product = $this->createProduct($company, 'PO-NO-BOM-001', 'Produto sem BOM aprovado', 'FG', 'UN');

        $response = $this->actingAs($user, 'web')
            ->from(route('production.orders.create'))
            ->post(route('production.orders.store'), [
                'product_id' => $product->id,
                'quantity_planned' => 5,
            ])
            ->assertRedirect(route('production.orders.create'));

        $errors = $response->baseResponse->getSession()->get('errors');
        $productMessages = $errors->get('product_id');
        $productionMessages = $errors->get('production');

        $hasMissingBomMessage = in_array(__('messages.production_order_missing_bom_version'), $productMessages, true);
        $hasMysqlMessage = in_array(__('messages.production_order_mysql_required'), $productionMessages, true);

        $this->assertTrue($hasMissingBomMessage || $hasMysqlMessage);
        $this->assertFalse(in_array(__('messages.production_order_create_failed'), $productionMessages, true));
    }

    public function test_production_order_product_search_lists_only_products_with_approved_effective_bom(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $productWithBom = $this->createProduct($company, 'PO-BOM-001', 'Produto com BOM', 'FG', 'UN');
        $productWithoutBom = $this->createProduct($company, 'PO-NOBOM-001', 'Produto sem BOM', 'FG', 'UN');
        $productWithDraftBom = $this->createProduct($company, 'PO-DRAFT-001', 'Produto com BOM rascunho', 'FG', 'UN');
        $productWithExpiredBom = $this->createProduct($company, 'PO-OLD-001', 'Produto com BOM expirado', 'FG', 'UN');

        BomHeader::query()->create([
            'company_id' => $company->id,
            'product_id' => $productWithBom->id,
            'version_number' => 1,
            'status' => 'APPROVED',
            'effective_from' => now()->toDateString(),
            'effective_to' => null,
            'description' => 'BOM base',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        BomHeader::query()->create([
            'company_id' => $company->id,
            'product_id' => $productWithDraftBom->id,
            'version_number' => 1,
            'status' => 'DRAFT',
            'effective_from' => now()->toDateString(),
            'effective_to' => null,
            'description' => 'BOM rascunho',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        BomHeader::query()->create([
            'company_id' => $company->id,
            'product_id' => $productWithExpiredBom->id,
            'version_number' => 1,
            'status' => 'APPROVED',
            'effective_from' => now()->subDays(30)->toDateString(),
            'effective_to' => now()->subDay()->toDateString(),
            'description' => 'BOM expirado',
            'approved_by' => $user->id,
            'approved_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson(route('production.products.search', ['q' => 'PO-']));

        $response->assertOk();

        $results = $response->json('results');
        $texts = array_map(static fn (array $row): string => (string) ($row['text'] ?? ''), is_array($results) ? $results : []);

        $this->assertTrue(collect($texts)->contains(fn (string $text): bool => str_contains($text, 'PO-BOM-001')));
        $this->assertFalse(collect($texts)->contains(fn (string $text): bool => str_contains($text, 'PO-NOBOM-001')));
        $this->assertFalse(collect($texts)->contains(fn (string $text): bool => str_contains($text, 'PO-DRAFT-001')));
        $this->assertFalse(collect($texts)->contains(fn (string $text): bool => str_contains($text, 'PO-OLD-001')));

        $this->assertDatabaseHas('products', [
            'company_id' => $company->id,
            'id' => $productWithoutBom->id,
        ]);

        $this->assertDatabaseHas('products', [
            'company_id' => $company->id,
            'id' => $productWithDraftBom->id,
        ]);

        $this->assertDatabaseHas('products', [
            'company_id' => $company->id,
            'id' => $productWithExpiredBom->id,
        ]);
    }

    /**
     * @return array{company: Company, user: User}
     */
    private function contextWithRole(string $roleSlug): array
    {
        $company = Company::query()->create([
            'name' => 'Atlas Components',
            'code' => 'ATL',
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'name' => 'Master User',
            'email' => $roleSlug.'@atlas.test',
            'password' => 'Strong!Pass123',
            'current_company_id' => $company->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);

        $role = Role::query()->withoutGlobalScope('tenant')->create([
            'company_id' => $company->id,
            'name' => ucfirst($roleSlug),
            'slug' => $roleSlug,
        ]);

        $user->roles()->attach($role->id, ['company_id' => $company->id]);

        Trial::query()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'trial_start_date' => now()->subDay(),
            'trial_end_date' => now()->addDays(10),
            'status' => 'active',
            'is_expired' => false,
        ]);

        return compact('company', 'user');
    }

    private function createProduct(Company $company, string $sku, string $description, string $type, string $uom, bool $lotControl = false): Product
    {
        $unitCode = mb_strtoupper(trim($uom));

        $unit = Unit::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'code' => $unitCode,
            ],
            [
                'name' => $unitCode,
                'description' => null,
                'is_active' => true,
                'metadata' => null,
            ],
        );

        return Product::query()->create([
            'company_id' => $company->id,
            'sku' => $sku,
            'description' => $description,
            'product_type' => $type,
            'uom' => $unitCode,
            'unit_id' => $unit->id,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => $lotControl,
            'serial_control' => false,
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
        ]);
    }
}
