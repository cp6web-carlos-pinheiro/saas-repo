<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Product\Infrastructure\Persistence\Models\ProductVersion;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantProductPhase03ManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_supports_extended_attributes_and_lifecycle(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $this->actingAs($user, 'web')
            ->post(route('products.store'), [
                'sku' => 'P-F03-001',
                'description' => 'Produto Fase 03',
                'product_type' => 'FG',
                'uom' => 'UN',
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

        $user->companies()->attach($company->id, ['is_default' => true]);

        $role = Role::query()->withoutGlobalScope('tenant')->create([
            'company_id' => $company->id,
            'name' => ucfirst($roleSlug),
            'slug' => $roleSlug,
        ]);

        $user->roles()->attach($role->id, ['company_id' => $company->id]);

        $organization = Organization::query()->create([
            'company_id' => $company->id,
            'name' => 'Atlas Components',
            'slug' => 'atlas-components',
            'timezone' => 'UTC',
        ]);

        Trial::query()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'trial_start_date' => now()->subDay(),
            'trial_end_date' => now()->addDays(10),
            'status' => 'active',
            'is_expired' => false,
        ]);

        return compact('company', 'user');
    }

    private function createProduct(Company $company, string $sku, string $description, string $type, string $uom, bool $lotControl = false): Product
    {
        return Product::query()->create([
            'company_id' => $company->id,
            'sku' => $sku,
            'description' => $description,
            'product_type' => $type,
            'uom' => $uom,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => $lotControl,
            'serial_control' => false,
            'is_active' => true,
            'lifecycle_status' => 'ACTIVE',
        ]);
    }
}
