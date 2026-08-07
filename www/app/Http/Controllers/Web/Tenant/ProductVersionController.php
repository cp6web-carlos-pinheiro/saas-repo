<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Application\DTO\ApproveProductVersionDTO;
use App\Modules\Product\Application\DTO\CreateProductVersionDTO;
use App\Modules\Product\Application\DTO\UpdateProductVersionDTO;
use App\Modules\Product\Application\Services\ProductVersionService;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Product\Infrastructure\Persistence\Models\ProductVersion;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Shared\Presentation\Exceptions\DomainException;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ProductVersionController extends Controller
{
    private const READ_PERMISSION = 'bom.explode';

    private const WRITE_PERMISSION = 'bom.explode';

    public function index(Request $request, ProductVersionService $service): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $productId = (int) $request->query('product_id', 0);
        $sort = (string) $request->query('sort', 'version_number');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        abort_unless(in_array($sort, ['version_number', 'status', 'effective_from', 'effective_to', 'compatibility_rule', 'change_summary'], true), 404);
        $selectedProduct = $productId > 0
            ? Product::query()->find($productId)
            : null;

        $versions = $selectedProduct !== null
            ? $service->history($selectedProduct->id)
            : collect();

        $versions = $versions->sortBy($sort, SORT_REGULAR, $direction === 'desc')->values();

        return view('client.products.versions', compact('selectedProduct', 'versions', 'sort', 'direction', 'company'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $query = Product::query()
            ->select(['id', 'sku', 'description'])
            ->orderBy('sku');

        if ($term !== '') {
            $query->where(function ($inner) use ($term): void {
                $inner
                    ->where('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate($perPage, ['id', 'sku', 'description'], 'page', $page);

        $results = $paginator
            ->getCollection()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'text' => sprintf('%s - %s', $product->sku, $product->description ?? __('product.no_description')),
            ])
            ->values();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function create(Request $request, Product $product): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);

        return view('client.products.version-form', [
            'product' => $product,
            'version' => null,
            'editing' => false,
            'payloadJson' => "{}",
            'company' => $company,
        ]);
    }

    public function store(Request $request, Product $product, ProductVersionService $service, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);

        $validated = $this->validateVersion($request);
        try {
            $version = $service->createDraft($product->id, CreateProductVersionDTO::fromArray($validated), $request->user()?->id);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'payload_json' => $exception->getMessage(),
            ]);
        }

        $audit->record(
            'tenant_product_version.created',
            context: [
                'product_id' => $product->id,
                'version_id' => $version['id'] ?? null,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.versions.show', [$product, $version['id']])->with('status', __('product.version_created'));
    }

    public function show(Request $request, Product $product, ProductVersion $version): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        $this->ensureVersionBelongsToProduct($product, $version);

        return view('client.products.version-show', compact('product', 'version', 'company'));
    }

    public function edit(Request $request, Product $product, ProductVersion $version): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureVersionBelongsToProduct($product, $version);

        return view('client.products.version-form', [
            'product' => $product,
            'version' => $version,
            'editing' => true,
            'payloadJson' => $this->payloadJson($version),
            'company' => $company,
        ]);
    }

    public function update(Request $request, Product $product, ProductVersion $version, ProductVersionService $service, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureVersionBelongsToProduct($product, $version);

        $validated = $this->validateVersion($request);
        try {
            $service->updateDraft($product->id, $version->id, UpdateProductVersionDTO::fromArray($validated));
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'payload_json' => $exception->getMessage(),
            ]);
        }

        $audit->record(
            'tenant_product_version.updated',
            context: [
                'product_id' => $product->id,
                'version_id' => $version->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.versions.show', [$product, $version])->with('status', __('product.version_updated'));
    }

    public function destroy(Request $request, Product $product, ProductVersion $version, ProductVersionService $service, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureVersionBelongsToProduct($product, $version);

        $versionId = $version->id;
        $versionNumber = $version->version_number;
        $service->delete($product->id, $version->id);

        $audit->record(
            'tenant_product_version.deleted',
            context: [
                'product_id' => $product->id,
                'version_id' => $versionId,
                'version_number' => $versionNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.versions', ['product_id' => $product->id])->with('status', __('product.version_removed'));
    }

    public function approve(Request $request, Product $product, ProductVersion $version, ProductVersionService $service, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureVersionBelongsToProduct($product, $version);

        $validated = $this->validateVersionApproval($request);
        try {
            $service->approve($product->id, $version->id, ApproveProductVersionDTO::fromArray($validated), $request->user()?->id);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'effective_from' => $exception->getMessage(),
            ]);
        }

        $audit->record(
            'tenant_product_version.approved',
            context: [
                'product_id' => $product->id,
                'version_id' => $version->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.versions.show', [$product, $version])->with('status', __('product.version_approved'));
    }

    public function obsolete(Request $request, Product $product, ProductVersion $version, ProductVersionService $service, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureVersionBelongsToProduct($product, $version);

        $service->markObsolete($product->id, $version->id);

        $audit->record(
            'tenant_product_version.obsoleted',
            context: [
                'product_id' => $product->id,
                'version_id' => $version->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('products.versions.show', [$product, $version])->with('status', __('product.version_obsoleted'));
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

    private function ensureVersionBelongsToProduct(Product $product, ProductVersion $version): void
    {
        abort_unless((int) $version->product_id === (int) $product->id, 404);
    }

    private function payloadJson(ProductVersion $version): string
    {
        return json_encode($version->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    private function validateVersion(Request $request): array
    {
        $validated = $request->validate([
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'compatibility_rule' => ['required', 'string', 'in:NONE,BACKWARD,FORWARD,FULL'],
            'change_summary' => ['nullable', 'string', 'max:2000'],
            'payload_json' => ['required', 'string'],
        ]);

        $payload = json_decode($validated['payload_json'], true);

        if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'payload_json' => __('product.payload_invalid'),
            ]);
        }

        $validated['payload'] = $payload;
        unset($validated['payload_json']);

        return $validated;
    }

    private function validateVersionApproval(Request $request): array
    {
        return $request->validate([
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_summary' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
