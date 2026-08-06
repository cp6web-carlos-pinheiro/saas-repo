<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class InventoryWebController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'inventory.read';

    private const UPDATE_PERMISSION = 'inventory.update';

    /** @var array<string, string> */
    private const MOVEMENT_TYPES = [
        'RECEIPT' => 'Entrada',
        'ISSUE' => 'Saída',
        'RESERVE' => 'Reserva',
        'RELEASE' => 'Liberação de reserva',
        'TRANSFER_OUT' => 'Transferência - saída',
        'TRANSFER_IN' => 'Transferência - entrada',
        'INSPECTION_HOLD' => 'Bloqueio para inspeção',
        'INSPECTION_RELEASE' => 'Liberação da inspeção',
    ];

    public function balances(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $warehouseId = $request->integer('warehouse_id') ?: null;
        $productId = $request->integer('product_id') ?: null;

        $balances = InventoryBalance::query()
            ->with(['warehouse:id,code,name', 'product:id,sku,description,unit_id', 'product.unit:id,code'])
            ->where('company_id', $company->id)
            ->when($warehouseId, static fn (Builder $query) => $query->where('warehouse_id', $warehouseId))
            ->when($productId, static fn (Builder $query) => $query->where('product_id', $productId))
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();

        return view('client.inventory.balances.index', [
            'company' => $company,
            'balances' => $balances,
            'warehouseId' => $warehouseId,
            'productId' => $productId,
            'warehouses' => $this->warehouseOptions($company),
            'products' => $this->productOptions($company),
        ]);
    }

    public function movements(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $warehouseId = $request->integer('warehouse_id') ?: null;
        $productId = $request->integer('product_id') ?: null;
        $movementType = strtoupper(trim((string) $request->query('movement_type')));

        if (! array_key_exists($movementType, self::MOVEMENT_TYPES)) {
            $movementType = '';
        }

        $movements = StockLedgerMovement::query()
            ->with(['warehouse:id,code,name', 'product:id,sku,description,unit_id', 'product.unit:id,code'])
            ->where('company_id', $company->id)
            ->when($warehouseId, static fn (Builder $query) => $query->where('warehouse_id', $warehouseId))
            ->when($productId, static fn (Builder $query) => $query->where('product_id', $productId))
            ->when($movementType !== '', static fn (Builder $query) => $query->where('movement_type', $movementType))
            ->orderByDesc('movement_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('client.inventory.movements.index', [
            'company' => $company,
            'movements' => $movements,
            'warehouseId' => $warehouseId,
            'productId' => $productId,
            'movementType' => $movementType,
            'movementTypes' => self::MOVEMENT_TYPES,
            'warehouses' => $this->warehouseOptions($company),
            'products' => $this->productOptions($company),
        ]);
    }

    public function createMovement(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        return view('client.inventory.movements.form', [
            'company' => $company,
            'movementTypes' => self::MOVEMENT_TYPES,
            'warehouses' => $this->warehouseOptions($company),
            'products' => $this->productOptions($company),
        ]);
    }

    public function storeMovement(Request $request, InventoryService $inventory, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $request->validate([
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('company_id', $company->id)],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $company->id)],
            'movement_type' => ['required', Rule::in(array_keys(self::MOVEMENT_TYPES))],
            'quantity' => ['required', 'numeric', 'min:0.000001'],
            'allocation_strategy' => ['nullable', Rule::in(['FIFO', 'FEFO'])],
            'lot_number' => ['nullable', 'string', 'max:80'],
            'expires_at' => ['nullable', 'date'],
            'reference_type' => ['nullable', 'string', 'max:120'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'movement_at' => ['required', 'date'],
        ]);

        $result = $inventory->postMovement($data, $request->user()?->id);

        $audit->record('tenant_inventory_movement.created', context: [
            'company_id' => $company->id,
            'stock_ledger_movement_id' => $result['movement']['id'],
            'actor_user_id' => $request->user()?->id,
        ], userId: $request->user()?->id, ipAddress: $request->ip(), userAgent: $request->userAgent());

        return redirect()->route('inventory.movements.index')->with('status', __('inventory_web.movement_created'));
    }

    /** @return Collection<int, Warehouse> */
    private function warehouseOptions(Company $company)
    {
        return Warehouse::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);
    }

    /** @return Collection<int, Product> */
    private function productOptions(Company $company)
    {
        return Product::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('sku')->get(['id', 'sku', 'description']);
    }
}
