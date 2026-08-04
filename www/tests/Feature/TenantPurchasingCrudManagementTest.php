<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseFiscalEntry;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseFiscalEntryPosting;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseQuotation;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceipt;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantPurchasingCrudManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_administrator_can_create_and_list_basic_purchasing_cruds(): void
    {
        ['company' => $company, 'user' => $user, 'supplier' => $supplier, 'warehouse' => $warehouse, 'product' => $product] = $this->purchasingContext();

        $this->actingAs($user, 'web')
            ->post(route('purchasing.requisitions.store'), [
                'required_date' => '2026-08-10',
                'status' => 'DRAFT',
                'source_type' => 'manual',
                'notes' => 'Solicitação inicial.',
                'items' => [[
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'supplier_id' => $supplier->id,
                    'quantity' => '5',
                    'need_by_date' => '2026-08-10',
                    'order_date' => '2026-08-06',
                ]],
            ])
            ->assertRedirect(route('purchasing.requisitions.index'));

        $requisition = PurchaseRequisition::query()->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('purchasing.orders.store'), [
                'supplier_id' => $supplier->id,
                'purchase_requisition_id' => $requisition->id,
                'status' => 'DRAFT',
                'order_date' => '2026-08-03',
                'expected_delivery_date' => '2026-08-12',
                'notes' => 'Pedido inicial.',
                'items' => [[
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => '5',
                    'unit_price' => '30,00',
                    'need_by_date' => '2026-08-10',
                    'promised_date' => '2026-08-12',
                ]],
            ])
            ->assertRedirect(route('purchasing.orders.index'));

        $order = PurchaseOrder::query()->firstOrFail();
        $orderLine = $order->lines()->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('purchasing.quotations.store'), [
                'supplier_id' => $supplier->id,
                'purchase_requisition_id' => $requisition->id,
                'quotation_date' => '2026-08-03',
                'valid_until' => '2026-08-20',
                'status' => 'RECEIVED',
                'notes' => 'Cotação recebida.',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_price' => '30,00',
                    'notes' => 'Preço negociado.',
                ]],
            ])
            ->assertRedirect(route('purchasing.quotations.index'));

        $this->actingAs($user, 'web')
            ->post(route('purchasing.receipts.store'), [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $order->id,
                'receipt_date' => '2026-08-05',
                'status' => 'POSTED',
                'notes' => 'Recebimento parcial.',
                'items' => [[
                    'purchase_order_line_id' => $orderLine->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity_received' => '5',
                    'lot_number' => 'LOT-001',
                    'notes' => 'Recebido sem avarias.',
                ]],
            ])
            ->assertRedirect(route('purchasing.receipts.index'));

        $this->actingAs($user, 'web')
            ->post(route('purchasing.fiscal-entries.store'), [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $order->id,
                'document_number' => 'NF-123',
                'issue_date' => '2026-08-05',
                'entry_date' => '2026-08-06',
                'status' => 'POSTED',
                'amount' => '200,50',
                'notes' => 'Entrada fiscal lançada.',
            ])
            ->assertRedirect(route('purchasing.fiscal-entries.index'));

        $quotation = PurchaseQuotation::query()->firstOrFail();
        $receipt = PurchaseReceipt::query()->firstOrFail();
        $entry = PurchaseFiscalEntry::query()->firstOrFail();

        $this->assertDatabaseHas('purchase_requisitions', [
            'company_id' => $company->id,
            'id' => $requisition->id,
            'status' => 'DRAFT',
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'company_id' => $company->id,
            'id' => $order->id,
            'supplier_id' => $supplier->id,
            'status' => 'DRAFT',
        ]);

        $this->assertDatabaseHas('purchase_quotations', [
            'company_id' => $company->id,
            'id' => $quotation->id,
            'status' => 'RECEIVED',
            'amount_cents' => 15000,
        ]);

        $this->assertDatabaseHas('purchase_receipts', [
            'company_id' => $company->id,
            'id' => $receipt->id,
            'status' => 'POSTED',
        ]);

        $this->assertDatabaseHas('purchase_fiscal_entries', [
            'company_id' => $company->id,
            'id' => $entry->id,
            'status' => 'POSTED',
            'amount_cents' => 20050,
        ]);

        $this->assertDatabaseHas('purchase_requisition_lines', [
            'company_id' => $company->id,
            'purchase_requisition_id' => $requisition->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('purchase_order_lines', [
            'company_id' => $company->id,
            'purchase_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity_ordered' => 5,
        ]);

        $this->assertDatabaseHas('purchase_quotation_lines', [
            'company_id' => $company->id,
            'purchase_quotation_id' => $quotation->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('purchase_receipt_lines', [
            'company_id' => $company->id,
            'purchase_receipt_id' => $receipt->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $movement = StockLedgerMovement::query()->where('reference_type', 'purchase_receipt')->where('reference_id', $receipt->id)->first();
        $this->assertNotNull($movement);

        $posting = PurchaseFiscalEntryPosting::query()->where('purchase_fiscal_entry_id', $entry->id)->first();
        $this->assertNotNull($posting);
        $this->assertSame('POSTED', $posting->status);

        $this->actingAs($user, 'web')
            ->put(route('purchasing.receipts.update', $receipt), [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $order->id,
                'receipt_date' => '2026-08-05',
                'status' => 'CANCELLED',
                'notes' => 'Tentativa de alteração após lançamento.',
                'items' => [[
                    'purchase_order_line_id' => $orderLine->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity_received' => '5',
                    'lot_number' => 'LOT-001',
                    'notes' => 'Recebido sem avarias.',
                ]],
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($user, 'web')
            ->delete(route('purchasing.receipts.destroy', $receipt))
            ->assertSessionHasErrors('status');

        $this->actingAs($user, 'web')
            ->post(route('purchasing.receipts.reverse', $receipt))
            ->assertSessionHasErrors('reverse_category')
            ->assertSessionHasErrors('reverse_reason');

        $receiptReverseReason = 'Avaria identificada na inspeção e devolução imediata.';
        $receiptReverseCategory = 'quality';

        $this->actingAs($user, 'web')
            ->post(route('purchasing.receipts.reverse', $receipt), [
                'reverse_category' => $receiptReverseCategory,
                'reverse_reason' => $receiptReverseReason,
            ])
            ->assertRedirect(route('purchasing.receipts.show', $receipt));

        $this->assertDatabaseHas('purchase_receipts', [
            'id' => $receipt->id,
            'status' => 'CANCELLED',
        ]);

        $receipt->refresh();
        $this->assertSame($receiptReverseCategory, $receipt->metadata['reversal']['category'] ?? null);
        $this->assertSame($receiptReverseReason, $receipt->metadata['reversal']['reason'] ?? null);

        $this->assertDatabaseHas('stock_ledger_movements', [
            'reference_type' => 'purchase_receipt_reversal',
            'reference_id' => $receipt->id,
            'movement_type' => 'ISSUE',
        ]);

        $this->actingAs($user, 'web')
            ->put(route('purchasing.fiscal-entries.update', $entry), [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $order->id,
                'document_number' => 'NF-123',
                'issue_date' => '2026-08-05',
                'entry_date' => '2026-08-06',
                'status' => 'CANCELLED',
                'amount' => '200,50',
                'notes' => 'Tentativa de alteração após lançamento.',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($user, 'web')
            ->delete(route('purchasing.fiscal-entries.destroy', $entry))
            ->assertSessionHasErrors('status');

        $this->actingAs($user, 'web')
            ->post(route('purchasing.fiscal-entries.reverse', $entry))
            ->assertSessionHasErrors('reverse_category')
            ->assertSessionHasErrors('reverse_reason');

        $entryReverseReason = 'Divergência de impostos validada pelo fiscal.';
        $entryReverseCategory = 'fiscal';

        $this->actingAs($user, 'web')
            ->post(route('purchasing.fiscal-entries.reverse', $entry), [
                'reverse_category' => $entryReverseCategory,
                'reverse_reason' => $entryReverseReason,
            ])
            ->assertRedirect(route('purchasing.fiscal-entries.show', $entry));

        $this->assertDatabaseHas('purchase_fiscal_entries', [
            'id' => $entry->id,
            'status' => 'CANCELLED',
        ]);

        $entry->refresh();
        $this->assertSame($entryReverseCategory, $entry->metadata['reversal']['category'] ?? null);
        $this->assertSame($entryReverseReason, $entry->metadata['reversal']['reason'] ?? null);

        $this->assertDatabaseHas('purchase_fiscal_entry_postings', [
            'purchase_fiscal_entry_id' => $entry->id,
            'status' => 'REVERSED',
        ]);

        $posting = PurchaseFiscalEntryPosting::query()->where('purchase_fiscal_entry_id', $entry->id)->firstOrFail();
        $this->assertSame($entryReverseCategory, $posting->payload['reversal']['category'] ?? null);
        $this->assertSame($entryReverseReason, $posting->payload['reversal']['reason'] ?? null);

        $this->actingAs($user, 'web')
            ->getJson(route('purchasing.lookups.suppliers', ['q' => 'SUP', 'page' => 1]))
            ->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $this->actingAs($user, 'web')
            ->getJson(route('purchasing.lookups.requisitions', ['q' => 'REQ', 'page' => 1]))
            ->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $this->actingAs($user, 'web')
            ->getJson(route('purchasing.lookups.orders', ['q' => 'PUR', 'page' => 1]))
            ->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $this->actingAs($user, 'web')
            ->getJson(route('purchasing.lookups.order-lines', ['q' => 'P-001', 'order_id' => $order->id, 'page' => 1]))
            ->assertOk()
            ->assertJsonStructure(['results', 'pagination' => ['more']]);

        $this->actingAs($user, 'web')
            ->get(route('purchasing.requisitions.index'))
            ->assertOk()
            ->assertSee($requisition->requisition_number);

        $this->actingAs($user, 'web')
            ->get(route('purchasing.orders.index'))
            ->assertOk()
            ->assertSee($order->purchase_order_number);

        $this->actingAs($user, 'web')
            ->get(route('purchasing.quotations.index'))
            ->assertOk()
            ->assertSee($quotation->quotation_number);

        $this->actingAs($user, 'web')
            ->get(route('purchasing.receipts.index'))
            ->assertOk()
            ->assertSee($receipt->receipt_number);

        $this->actingAs($user, 'web')
            ->get(route('purchasing.fiscal-entries.index'))
            ->assertOk()
            ->assertSee($entry->entry_number);
    }

    /**
    * @return array{company: Company, user: User, supplier: Supplier, warehouse: Warehouse, product: Product}
     */
    private function purchasingContext(): array
    {
        $company = Company::query()->create([
            'name' => 'Atlas Components',
            'code' => 'ATL',
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'name' => 'Master User',
            'email' => 'master@atlas.test',
            'password' => 'Strong!Pass123',
            'current_company_id' => $company->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id, ['is_default' => true]);

        $role = Role::query()->withoutGlobalScope('tenant')->create([
            'company_id' => $company->id,
            'name' => 'Master',
            'slug' => 'master',
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

        $supplier = Supplier::query()->create([
            'company_id' => $company->id,
            'code' => 'SUP-001',
            'name' => 'Fornecedor Atlas',
            'person_type' => 'PJ',
            'status' => 'ACTIVE',
        ]);

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => 'Planta Atlas',
            'code' => 'PLT-001',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Almoxarifado Central',
            'code' => 'WH-001',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'P-001',
            'description' => 'Produto de compra',
            'product_type' => 'raw_material',
            'uom' => 'UN',
            'safety_stock' => 0,
            'lead_time_days' => 3,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return compact('company', 'user', 'supplier', 'warehouse', 'product');
    }
}
