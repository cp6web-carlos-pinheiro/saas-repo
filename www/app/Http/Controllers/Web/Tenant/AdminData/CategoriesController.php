<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant\AdminData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\ProductCategory;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CategoriesController extends Controller
{
    use HandlesTenantAuthorization;

    private const PERMISSION = 'admin-data.categories';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.read', $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['id', 'name', 'is_active', 'created_at'], true), 404);

        $categories = ProductCategory::query()
            ->where('company_id', $company->id)
            ->when($status !== '', static fn (Builder $query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.admin-data.categories.search', compact('categories', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.create', $company->id);

        return view('client.admin-data.categories.form', [
            'category' => null,
            'company' => $company,
        ]);
    }

    public function show(Request $request, ProductCategory $category): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.read', $company->id);

        abort_unless((int) $category->company_id === (int) $company->id, 404);

        return view('client.admin-data.categories.show', compact('category', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.create', $company->id);

        $data = $this->validateCategory($request, $company);
        $code = $this->generateCode($company->id, (string) $data['name']);

        $category = ProductCategory::query()->create([
            'company_id' => $company->id,
            'code' => $code,
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
            'metadata' => null,
        ]);

        $audit->record(
            'tenant_master_data.categories.created',
            context: [
                'record_id' => $category->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.categories.index')->with('status', __('admin_data_categories.created'));
    }

    public function edit(Request $request, ProductCategory $category): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.update', $company->id);

        abort_unless((int) $category->company_id === (int) $company->id, 404);

        return view('client.admin-data.categories.form', compact('category', 'company'));
    }

    public function update(Request $request, ProductCategory $category, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.update', $company->id);

        abort_unless((int) $category->company_id === (int) $company->id, 404);

        $data = $this->validateCategory($request, $company, $category);

        $category->fill([
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_by' => $request->user()?->id,
            'metadata' => null,
        ]);
        $category->save();

        $audit->record(
            'tenant_master_data.categories.updated',
            context: [
                'record_id' => $category->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.categories.index')->with('status', __('admin_data_categories.updated'));
    }

    public function destroy(Request $request, ProductCategory $category, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::PERMISSION.'.update', $company->id);

        abort_unless((int) $category->company_id === (int) $company->id, 404);

        if ($this->hasDependencies($category)) {
            return redirect()->route('admin-data.categories.show', $category)->withErrors(['record' => __('admin_data_categories.remove_blocked')]);
        }

        $recordId = $category->id;
        $recordCode = $category->code;
        $category->delete();

        $audit->record(
            'tenant_master_data.categories.removed',
            context: [
                'record_id' => $recordId,
                'record_code' => $recordCode,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.categories.index')->with('status', __('admin_data_categories.removed'));
    }

    /**
     * @return array{name: string, description?: string|null, is_active?: bool}
     */
    private function validateCategory(Request $request, Company $company, ?ProductCategory $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('product_categories', 'name')
                    ->where(static fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function generateCode(int $companyId, string $name): string
    {
        $base = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->value();

        if ($base === '') {
            $base = 'CAT';
        }

        $base = 'CAT-'.mb_substr($base, 0, 36);
        $candidate = $base;
        $counter = 2;

        while (ProductCategory::query()->where('company_id', $companyId)->where('code', $candidate)->exists()) {
            $suffix = '-'.$counter;
            $candidate = mb_substr($base, 0, max(1, 40 - mb_strlen($suffix))).$suffix;
            $counter++;
        }

        return $candidate;
    }

    private function hasDependencies(ProductCategory $category): bool
    {
        return \DB::table('products')->where('company_id', (int) $category->company_id)->where('category_id', (int) $category->id)->exists();
    }
}
