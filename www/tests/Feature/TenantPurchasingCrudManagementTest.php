<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseFiscalEntry;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseQuotation;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceipt;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantPurchasingCrudManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_administrator_can_create_and_list_basic_purchasing_cruds(): void
    {
        ['company' => $company, 'user' => $user, 'supplier' => $supplier] = $this->purchasingContext();

        $this->actingAs($user, 'web')
            ->post(route('purchasing.requisitions.store'), [
                'required_date' => '2026-08-10',
                'status' => 'DRAFT',
                'source_type' => 'manual',
                'notes' => 'Solicitação inicial.',
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
            ])
            ->assertRedirect(route('purchasing.orders.index'));

        $order = PurchaseOrder::query()->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('purchasing.quotations.store'), [
                'supplier_id' => $supplier->id,
                'purchase_requisition_id' => $requisition->id,
                'quotation_date' => '2026-08-03',
                'valid_until' => '2026-08-20',
                'status' => 'RECEIVED',
                'amount' => '150,00',
                'notes' => 'Cotação recebida.',
            ])
            ->assertRedirect(route('purchasing.quotations.index'));

        $this->actingAs($user, 'web')
            ->post(route('purchasing.receipts.store'), [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $order->id,
                'receipt_date' => '2026-08-05',
                'status' => 'DRAFT',
                'notes' => 'Recebimento parcial.',
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
            'status' => 'DRAFT',
        ]);

        $this->assertDatabaseHas('purchase_fiscal_entries', [
            'company_id' => $company->id,
            'id' => $entry->id,
            'status' => 'POSTED',
            'amount_cents' => 20050,
        ]);

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
     * @return array{company: Company, user: User, supplier: Supplier}
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

        return compact('company', 'user', 'supplier');
    }
}
