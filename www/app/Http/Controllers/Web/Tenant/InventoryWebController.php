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

    private const MOVEMENT_TYPES = ['RECEIPT', 'ISSUE', 'RESERVE', 'RELEASE', 'TRANSFER_OUT', 'TRANSFER_IN', 'INSPECTION_HOLD', 'INSPECTION_RELEASE'];

    public function balances(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $warehouseId = $request->integer('warehouse_id') ?: null;
        $productId = $request->integer('product_id') ?: null;
        $sort = (string) $request->query('sort', 'warehouse');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['warehouse', 'product', 'qty_available', 'qty_reserved', 'qty_inspection', 'qty_in_transit', 'last_movement_at'], true), 404);

        $balancesQuery = InventoryBalance::query()
            ->with(['warehouse:id,code,name', 'product:id,sku,description,unit_id', 'product.unit:id,code'])
            ->where('company_id', $company->id)
            ->when($warehouseId, static fn (Builder $query) => $query->where('warehouse_id', $warehouseId))
            ->when($productId, static fn (Builder $query) => $query->where('product_id', $productId));

        if ($sort === 'warehouse') {
            $balancesQuery->orderBy(Warehouse::query()->select('name')->whereColumn('warehouses.id', 'inventory_balances.warehouse_id'), $direction);
        } elseif ($sort === 'product') {
            $balancesQuery->orderBy(Product::query()->select('sku')->whereColumn('products.id', 'inventory_balances.product_id'), $direction);
        } else {
            $balancesQuery->orderBy($sort, $direction);
        }

        $balances = $balancesQuery
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('client.inventory.balances.index', [
            'company' => $company,
            'balances' => $balances,
            'warehouseId' => $warehouseId,
            'productId' => $productId,
            'sort' => $sort,
            'direction' => $direction,
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
        $sort = (string) $request->query('sort', 'movement_at');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($movementType, self::MOVEMENT_TYPES, true)) {
            $movementType = '';
        }

        abort_unless(in_array($sort, ['movement_at', 'movement_type', 'warehouse', 'product', 'quantity', 'reference', 'notes'], true), 404);

        $movementsQuery = StockLedgerMovement::query()
            ->with(['warehouse:id,code,name', 'product:id,sku,description,unit_id', 'product.unit:id,code'])
            ->where('company_id', $company->id)
            ->when($warehouseId, static fn (Builder $query) => $query->where('warehouse_id', $warehouseId))
            ->when($productId, static fn (Builder $query) => $query->where('product_id', $productId))
            ->when($movementType !== '', static fn (Builder $query) => $query->where('movement_type', $movementType));

        if ($sort === 'warehouse') {
            $movementsQuery->orderBy(Warehouse::query()->select('code')->whereColumn('warehouses.id', 'stock_ledger_movements.warehouse_id'), $direction);
        } elseif ($sort === 'product') {
            $movementsQuery->orderBy(Product::query()->select('sku')->whereColumn('products.id', 'stock_ledger_movements.product_id'), $direction);
        } elseif ($sort === 'reference') {
            $movementsQuery->orderBy('reference_type', $direction)->orderBy('reference_id', $direction);
        } else {
            $movementsQuery->orderBy($sort, $direction);
        }

        $movements = $movementsQuery
            ->orderBy('id', $direction)
            ->paginate(20)
            ->withQueryString();

        return view('client.inventory.movements.index', [
            'company' => $company,
            'movements' => $movements,
            'warehouseId' => $warehouseId,
            'productId' => $productId,
            'movementType' => $movementType,
            'sort' => $sort,
            'direction' => $direction,
            'movementTypes' => $this->movementTypes(),
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
            'movementTypes' => $this->movementTypes(),
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
            'movement_type' => ['required', Rule::in(self::MOVEMENT_TYPES)],
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

    /** @return array<string, string> */
    private function movementTypes(): array
    {
        return collect(self::MOVEMENT_TYPES)
            ->mapWithKeys(static fn (string $type): array => [$type => __('inventory_web.movement_types.'.$type)])
            ->all();
    }
}
