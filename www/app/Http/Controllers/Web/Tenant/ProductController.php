<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProductController extends Controller
{
    private const READ_PERMISSION = 'bom.explode';

    private const CREATE_PERMISSION = 'bom.explode';

    private const UPDATE_PERMISSION = 'bom.explode';

    public function index(Request $request, ProductService $service): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'sku');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $products = $service->paginate(
            15,
            ['q' => $search, 'company_id' => $company->id],
            $sort,
            $direction,
        )->withQueryString();

        return view('client.products.search', compact('products', 'search', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.products.form', [
            'product' => null,
            'company' => $company,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        return view('client.products.show', compact('product', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateProduct($request, $company);
        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => $data['sku'],
            'description' => $data['description'],
            'product_type' => $data['product_type'],
            'uom' => $data['uom'],
            'safety_stock' => (int) $data['safety_stock'],
            'lead_time_days' => (int) $data['lead_time_days'],
            'lot_control' => (bool) ($data['lot_control'] ?? false),
            'serial_control' => (bool) ($data['serial_control'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $audit->record(
            'tenant_product.created',
            context: [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.index')->with('status', __('product.created'));
    }

    public function edit(Request $request, Product $product): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        return view('client.products.form', compact('product', 'company'));
    }

    public function update(Request $request, Product $product, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateProduct($request, $company, $product);

        $product->fill([
            'sku' => $data['sku'],
            'description' => $data['description'],
            'product_type' => $data['product_type'],
            'uom' => $data['uom'],
            'safety_stock' => (int) $data['safety_stock'],
            'lead_time_days' => (int) $data['lead_time_days'],
            'lot_control' => (bool) ($data['lot_control'] ?? false),
            'serial_control' => (bool) ($data['serial_control'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $product->save();

        $audit->record(
            'tenant_product.updated',
            context: [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.show', $product)->with('status', __('product.updated'));
    }

    public function destroy(Request $request, Product $product, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $productId = $product->id;
        $sku = $product->sku;
        $product->delete();

        $audit->record(
            'tenant_product.removed',
            context: [
                'product_id' => $productId,
                'sku' => $sku,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.index')->with('status', __('product.removed'));
    }

    private function activeCompanyFrom(Request $request): Company
    {
        $companyId = (int) ($request->user()?->current_company_id ?? 0);

        abort_unless($companyId > 0, 404);

        return Company::query()->findOrFail($companyId);
    }

    private function ensurePermission(Request $request, string $permission, int $companyId): void
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $company = Company::query()->findOrFail($companyId);

        if (app(CompanyUserAccessService::class)->isCompanyAdministrator($user, $company)) {
            return;
        }

        abort_unless($user->hasPermission($permission, $companyId), 403);
    }

    private function validateProduct(Request $request, Company $company, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('products', 'sku')
                    ->where('company_id', $company->id)
                    ->ignore($product),
            ],
            'description' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', 'in:FG,WIP,RAW,CONSUMABLE'],
            'uom' => ['required', 'string', 'max:20'],
            'safety_stock' => ['required', 'integer', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'lot_control' => ['required', 'boolean'],
            'serial_control' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}