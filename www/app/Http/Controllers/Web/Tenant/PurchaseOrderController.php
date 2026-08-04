<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PurchaseOrderController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'purchasing.orders.read';

    private const CREATE_PERMISSION = 'purchasing.orders.create';

    private const UPDATE_PERMISSION = 'purchasing.orders.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchId = ctype_digit($search) ? (int) $search : null;
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'id');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($status, ['DRAFT', 'APPROVED', 'CANCELLED'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['id', 'order_date', 'status', 'created_at'], true), 404);

        $orders = PurchaseOrder::query()
            ->with('supplier:id,name')
            ->withCount('lines')
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('purchase_order_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.orders.search', compact('orders', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.purchasing.orders.form', [
            'order' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
        ]);
    }

    public function show(Request $request, PurchaseOrder $order): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $order->load(['supplier:id,name', 'requisition:id,requisition_number'])->loadCount('lines');

        return view('client.purchasing.orders.show', compact('order', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateOrder($request);

        $order = PurchaseOrder::query()->create([
            'company_id' => $company->id,
            'purchase_order_number' => $this->generateNumber($company),
            'supplier_id' => (int) $data['supplier_id'],
            'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
            'status' => $data['status'],
            'order_date' => $data['order_date'],
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'created_by' => $request->user()?->id,
            'approved_by' => $data['status'] === 'APPROVED' ? $request->user()?->id : null,
            'approved_at' => $data['status'] === 'APPROVED' ? now() : null,
            'notes' => $data['notes'] ?? null,
        ]);

        $audit->record(
            'tenant_purchase_order.created',
            context: [
                'purchase_order_id' => $order->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.orders.index')->with('status', __('purchase_order.created'));
    }

    public function edit(Request $request, PurchaseOrder $order): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        return view('client.purchasing.orders.form', [
            'order' => $order,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
        ]);
    }

    public function update(Request $request, PurchaseOrder $order, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateOrder($request);

        $order->fill([
            'supplier_id' => (int) $data['supplier_id'],
            'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
            'status' => $data['status'],
            'order_date' => $data['order_date'],
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($data['status'] === 'APPROVED' && $order->approved_at === null) {
            $order->approved_by = $request->user()?->id;
            $order->approved_at = now();
        }

        if ($data['status'] !== 'APPROVED') {
            $order->approved_by = null;
            $order->approved_at = null;
        }

        $order->save();

        $audit->record(
            'tenant_purchase_order.updated',
            context: [
                'purchase_order_id' => $order->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.orders.index')->with('status', __('purchase_order.updated'));
    }

    public function destroy(Request $request, PurchaseOrder $order, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $orderId = $order->id;
        $orderNumber = $order->purchase_order_number;
        $order->delete();

        $audit->record(
            'tenant_purchase_order.removed',
            context: [
                'purchase_order_id' => $orderId,
                'purchase_order_number' => $orderNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.orders.index')->with('status', __('purchase_order.removed'));
    }

    /**
     * @return array{supplier_id: int|string, purchase_requisition_id?: int|string|null, status: string, order_date: string, expected_delivery_date?: string|null, notes?: string|null}
     */
    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_requisition_id' => ['nullable', 'integer', Rule::exists('purchase_requisitions', 'id')],
            'status' => ['required', Rule::in(['DRAFT', 'APPROVED', 'CANCELLED'])],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'PO-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseOrder::query()->where('company_id', $company->id)->where('purchase_order_number', $number)->exists());

        return $number;
    }

    /**
     * @return array<int, string>
     */
    private function supplierOptions(Company $company): array
    {
        return Supplier::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(static fn (string $name, int $id): array => [$id => "#{$id} - {$name}"])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function requisitionOptions(Company $company): array
    {
        return PurchaseRequisition::query()
            ->where('company_id', $company->id)
            ->orderByDesc('id')
            ->limit(200)
            ->pluck('requisition_number', 'id')
            ->mapWithKeys(static fn (string $number, int $id): array => [$id => "#{$id} - {$number}"])
            ->all();
    }
}
