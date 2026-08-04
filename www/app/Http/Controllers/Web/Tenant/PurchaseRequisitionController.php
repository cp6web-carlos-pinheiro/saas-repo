<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PurchaseRequisitionController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'purchasing.requisitions.read';

    private const CREATE_PERMISSION = 'purchasing.requisitions.create';

    private const UPDATE_PERMISSION = 'purchasing.requisitions.update';

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

        abort_unless(in_array($sort, ['id', 'required_date', 'status', 'created_at'], true), 404);

        $requisitions = PurchaseRequisition::query()
            ->withCount('lines')
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('requisition_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.requisitions.search', compact('requisitions', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.purchasing.requisitions.form', [
            'requisition' => null,
            'company' => $company,
        ]);
    }

    public function show(Request $request, PurchaseRequisition $requisition): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $requisition->loadCount('lines');

        return view('client.purchasing.requisitions.show', compact('requisition', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateRequisition($request);

        $requisition = PurchaseRequisition::query()->create([
            'company_id' => $company->id,
            'requisition_number' => $this->generateNumber($company),
            'required_date' => $data['required_date'] ?? null,
            'status' => $data['status'],
            'source_type' => $data['source_type'] ?? 'manual',
            'requested_by' => $request->user()?->id,
            'approved_by' => $data['status'] === 'APPROVED' ? $request->user()?->id : null,
            'approved_at' => $data['status'] === 'APPROVED' ? now() : null,
            'notes' => $data['notes'] ?? null,
        ]);

        $audit->record(
            'tenant_purchase_requisition.created',
            context: [
                'purchase_requisition_id' => $requisition->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.requisitions.index')->with('status', __('purchase_requisition.created'));
    }

    public function edit(Request $request, PurchaseRequisition $requisition): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        return view('client.purchasing.requisitions.form', compact('requisition', 'company'));
    }

    public function update(Request $request, PurchaseRequisition $requisition, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateRequisition($request);

        $requisition->fill([
            'required_date' => $data['required_date'] ?? null,
            'status' => $data['status'],
            'source_type' => $data['source_type'] ?? 'manual',
            'notes' => $data['notes'] ?? null,
        ]);

        if ($data['status'] === 'APPROVED' && $requisition->approved_at === null) {
            $requisition->approved_by = $request->user()?->id;
            $requisition->approved_at = now();
        }

        if ($data['status'] !== 'APPROVED') {
            $requisition->approved_by = null;
            $requisition->approved_at = null;
        }

        $requisition->save();

        $audit->record(
            'tenant_purchase_requisition.updated',
            context: [
                'purchase_requisition_id' => $requisition->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.requisitions.index')->with('status', __('purchase_requisition.updated'));
    }

    public function destroy(Request $request, PurchaseRequisition $requisition, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $requisitionId = $requisition->id;
        $requisitionNumber = $requisition->requisition_number;
        $requisition->delete();

        $audit->record(
            'tenant_purchase_requisition.removed',
            context: [
                'purchase_requisition_id' => $requisitionId,
                'purchase_requisition_number' => $requisitionNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.requisitions.index')->with('status', __('purchase_requisition.removed'));
    }

    /**
     * @return array{required_date?: string|null, status: string, source_type?: string|null, notes?: string|null}
     */
    private function validateRequisition(Request $request): array
    {
        return $request->validate([
            'required_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['DRAFT', 'APPROVED', 'CANCELLED'])],
            'source_type' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'REQ-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseRequisition::query()->where('company_id', $company->id)->where('requisition_number', $number)->exists());

        return $number;
    }
}
