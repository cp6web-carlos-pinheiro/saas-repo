<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GlobalCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $searchTerms = preg_split('/\s+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['name', 'email', 'is_active', 'created_at'], true), 404);

        $customers = User::query()
            ->with(['currentCompany:id,name,code,is_active,created_at,updated_at'])
            ->when($search !== '', fn (Builder $query) => $this->applySearchFilters($query, $searchTerms))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.customer.search', compact('customers', 'search', 'sort', 'direction'));
    }

    public function create(Request $request): View
    {
        $companyId = $request->query('company_id');
        $contextCompany = null;

        if ($companyId !== null && ctype_digit((string) $companyId)) {
            $contextCompany = Company::query()->find((int) $companyId);
        }

        return view('admin.customer.form', [
            'customer' => null,
            'contextCompany' => $contextCompany,
        ]);
    }

    public function show(User $customer): View
    {
        $customer->loadMissing('currentCompany:id,name,code,is_active,created_at,updated_at');

        return view('admin.customer.show', compact('customer'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validateCustomer($request);

        $customer = User::query()->create([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'is_active' => true,
            'current_company_id' => $data['company_id'] ?? null,
        ]);

        if (! empty($data['company_id'])) {
            $customer->companies()->syncWithoutDetaching([
                (int) $data['company_id'] => ['is_default' => true],
            ]);
        }

        $audit->record(
            'platform_customer.created',
            context: ['customer_id' => $customer->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.customers.index')->with('status', __('global_customer.created'));
    }

    public function edit(User $customer): View
    {
        return view('admin.customer.form', [
            'customer' => $customer,
            'contextCompany' => null,
        ]);
    }

    public function update(Request $request, User $customer, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validateCustomer($request, $customer);

        $customer->fill([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        if (! empty($data['password'])) {
            $customer->password = Hash::make($data['password']);
        }

        $customer->save();

        $audit->record(
            'platform_customer.updated',
            context: ['customer_id' => $customer->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.customers.index')->with('status', __('global_customer.updated'));
    }

    public function destroy(Request $request, User $customer, AuditLogService $audit): RedirectResponse
    {
        $customerId = $customer->id;
        $customer->delete();

        $audit->record(
            'platform_customer.removed',
            context: ['customer_id' => $customerId, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.customers.index')->with('status', __('global_customer.removed'));
    }

    private function validateCustomer(Request $request, ?User $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($customer)],
            'password' => [$customer ? 'nullable' : 'required', 'confirmed', 'min:10'],
            'is_active' => ['nullable', 'boolean'],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
        ]);
    }

    private function applySearchFilters(Builder $query, array $searchTerms): void
    {
        $query->where(function (Builder $customerQuery) use ($searchTerms): void {
            foreach ($searchTerms as $term) {
                $customerQuery->where(fn (Builder $termQuery) => $this->applyTermFilters($termQuery, $term));
            }
        });
    }

    private function applyTermFilters(Builder $termQuery, string $term): void
    {
        $termIsActive = in_array($term, ['ativo', 'active'], true);
        $termIsInactive = in_array($term, ['inativo', 'inactive'], true);
        $termIsNumeric = ctype_digit($term);

        $termQuery
            ->where('users.name', 'like', "%{$term}%")
            ->orWhere('users.email', 'like', "%{$term}%")
            ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery
                ->where('companies.name', 'like', "%{$term}%")
                ->orWhere('companies.code', 'like', "%{$term}%"))
            ->orWhereHas('companies', static fn (Builder $companyQuery) => $companyQuery
                ->where('companies.name', 'like', "%{$term}%")
                ->orWhere('companies.code', 'like', "%{$term}%"));

        if ($termIsNumeric) {
            $termId = (int) $term;

            $termQuery->orWhere('users.id', $termId)
                ->orWhere('users.current_company_id', $termId)
                ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery->where('companies.id', $termId))
                ->orWhereHas('companies', static fn (Builder $companyQuery) => $companyQuery->where('companies.id', $termId));
        }

        if ($termIsActive) {
            $termQuery->orWhere('users.is_active', true)
                ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery->where('companies.is_active', true));
        }

        if ($termIsInactive) {
            $termQuery->orWhere('users.is_active', false)
                ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery->where('companies.is_active', false));
        }
    }
}
