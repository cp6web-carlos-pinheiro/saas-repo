<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Application\Services\ProductSpreadsheetService;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Product\Presentation\Http\Requests\ImportProductsRequest;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\MasterDataRecord;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ProductController extends Controller
{
    private const READ_PERMISSION = 'bom.explode';

    private const CREATE_PERMISSION = 'bom.explode';

    private const UPDATE_PERMISSION = 'bom.explode';

    private const XLSX_CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

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
            'units' => $this->masterDataOptions($company, 'units'),
            'categories' => $this->masterDataOptions($company, 'categories'),
            'brands' => $this->masterDataOptions($company, 'brands'),
            'ncms' => $this->masterDataOptions($company, 'ncms'),
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
        $resolvedUom = $this->resolveUom($company, $data);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => $data['sku'],
            'description' => $data['description'],
            'product_type' => $data['product_type'],
            'uom' => $resolvedUom,
            'unit_id' => isset($data['unit_id']) ? (int) $data['unit_id'] : null,
            'category_id' => isset($data['category_id']) ? (int) $data['category_id'] : null,
            'brand_id' => isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            'ncm_id' => isset($data['ncm_id']) ? (int) $data['ncm_id'] : null,
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

        return view('client.products.form', [
            'product' => $product,
            'company' => $company,
            'units' => $this->masterDataOptions($company, 'units'),
            'categories' => $this->masterDataOptions($company, 'categories'),
            'brands' => $this->masterDataOptions($company, 'brands'),
            'ncms' => $this->masterDataOptions($company, 'ncms'),
        ]);
    }

    public function update(Request $request, Product $product, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateProduct($request, $company, $product);
        $resolvedUom = $this->resolveUom($company, $data);

        $product->fill([
            'sku' => $data['sku'],
            'description' => $data['description'],
            'product_type' => $data['product_type'],
            'uom' => $resolvedUom,
            'unit_id' => isset($data['unit_id']) ? (int) $data['unit_id'] : null,
            'category_id' => isset($data['category_id']) ? (int) $data['category_id'] : null,
            'brand_id' => isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            'ncm_id' => isset($data['ncm_id']) ? (int) $data['ncm_id'] : null,
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

    public function export(Request $request, ProductSpreadsheetService $spreadsheetService): BinaryFileResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'sku');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['sku', 'product_type', 'lead_time_days', 'is_active', 'created_at'], true), 404);

        $products = Product::query()
            ->where('company_id', $company->id)
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(static function (Builder $nested) use ($search): void {
                    $nested->where('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->get();

        $path = $spreadsheetService->export($products, $company->name);
        $filename = 'products-'.now()->format('Ymd-His').'.xlsx';

        $audit = app(AuditLogService::class);
        $audit->record(
            'tenant_product.exported',
            context: [
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
                'products_count' => $products->count(),
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()
            ->download($path, $filename, ['Content-Type' => self::XLSX_CONTENT_TYPE])
            ->deleteFileAfterSend(true);
    }

    public function import(ImportProductsRequest $request, ProductSpreadsheetService $spreadsheetService, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        try {
            $result = $spreadsheetService->import($company, $request->file('file'));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\App\Shared\Presentation\Exceptions\DomainException $exception) {
            throw ValidationException::withMessages([
                'file' => $exception->getMessage(),
            ]);
        }

        $audit->record(
            'tenant_product.imported',
            context: [
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
                'created_count' => $result['created'],
                'updated_count' => $result['updated'],
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('products.index')
            ->with('status', __('product.import_success', ['created' => $result['created'], 'updated' => $result['updated']]));
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
            'uom' => ['nullable', 'string', 'max:20', 'required_without:unit_id'],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_records', 'id')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', 'units')->where('is_active', true)),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_records', 'id')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', 'categories')),
            ],
            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_records', 'id')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', 'brands')),
            ],
            'ncm_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_records', 'id')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', 'ncms')),
            ],
            'safety_stock' => ['required', 'integer', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'lot_control' => ['required', 'boolean'],
            'serial_control' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function masterDataOptions(Company $company, string $domain): array
    {
        return MasterDataRecord::query()
            ->where('company_id', $company->id)
            ->where('domain', $domain)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(static fn (MasterDataRecord $record): array => [$record->id => sprintf('%s - %s', $record->code, $record->name)])
            ->all();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveUom(Company $company, array $data): string
    {
        if (isset($data['unit_id']) && (int) $data['unit_id'] > 0) {
            $unitCode = MasterDataRecord::query()
                ->where('company_id', $company->id)
                ->where('domain', 'units')
                ->whereKey((int) $data['unit_id'])
                ->value('code');

            if (is_string($unitCode) && $unitCode !== '') {
                return mb_strtoupper($unitCode);
            }
        }

        return mb_strtoupper((string) ($data['uom'] ?? 'UN'));
    }
}