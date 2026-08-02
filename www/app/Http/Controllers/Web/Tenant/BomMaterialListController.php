<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomItem;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class BomMaterialListController extends Controller
{
    private const READ_PERMISSION = 'bom.explode';

    private const WRITE_PERMISSION = 'bom.explode';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'version_number');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $sortableColumns = ['version_number', 'status', 'effective_from', 'created_at'];
        $sort = in_array($sort, $sortableColumns, true) ? $sort : 'version_number';

        $query = BomHeader::query()
            ->with(['product'])
            ->withCount('items')
            ->where('company_id', $company->id);

        if ($search !== '') {
            $query->where(static function ($nested) use ($search): void {
                $term = '%'.$search.'%';

                $nested->where('description', 'like', $term)
                    ->orWhereHas('product', static function ($productQuery) use ($term): void {
                        $productQuery->where('sku', 'like', $term)
                            ->orWhere('description', 'like', $term);
                    });
            });
        }

        $boms = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('client.bom.search', compact('company', 'search', 'sort', 'direction', 'boms'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);

        $products = $this->companyProducts($company->id);
        $selectedProductId = (int) $request->query('product_id', 0);
        $selectedProduct = $selectedProductId > 0
            ? $products->firstWhere('id', $selectedProductId)
            : null;

        return view('client.bom.form', [
            'company' => $company,
            'bom' => null,
            'products' => $products,
            'editing' => false,
            'selectedProductId' => $selectedProduct?->id,
            'itemsForm' => [[
                'line_no' => 1,
                'component_product_id' => null,
                'quantity_per' => 1,
                'scrap_factor' => 0,
                'uom' => '',
            ]],
        ]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);

        $validated = $this->validateBom($request, $company);

        $bom = DB::transaction(function () use ($validated, $company, $request): BomHeader {
            $versionNumber = ((int) BomHeader::query()
                ->where('company_id', $company->id)
                ->where('product_id', (int) $validated['product_id'])
                ->max('version_number')) + 1;

            $bom = BomHeader::query()->create([
                'company_id' => $company->id,
                'product_id' => (int) $validated['product_id'],
                'version_number' => $versionNumber,
                'status' => $validated['status'],
                'effective_from' => $validated['effective_from'] ?? null,
                'effective_to' => $validated['effective_to'] ?? null,
                'description' => $validated['description'] ?? null,
                'approved_by' => $validated['status'] === 'APPROVED' ? $request->user()?->id : null,
                'approved_at' => $validated['status'] === 'APPROVED' ? now() : null,
            ]);

            $this->syncItems($bom, $company->id, $validated['items']);

            return $bom->load(['product', 'items.componentProduct']);
        });

        $audit->record(
            'tenant_bom.created',
            context: [
                'bom_header_id' => $bom->id,
                'product_id' => $bom->product_id,
                'version_number' => $bom->version_number,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('bom.material-lists.show', $bom)->with('status', __('bom.created'));
    }

    public function show(Request $request, BomHeader $bom): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        $this->ensureBomBelongsToCompany($bom, $company->id);

        $bom->load(['product', 'items.componentProduct']);

        return view('client.bom.show', compact('company', 'bom'));
    }

    public function edit(Request $request, BomHeader $bom): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureBomBelongsToCompany($bom, $company->id);

        $bom->load(['product', 'items.componentProduct']);

        return view('client.bom.form', [
            'company' => $company,
            'bom' => $bom,
            'products' => $this->companyProducts($company->id),
            'editing' => true,
            'selectedProductId' => $bom->product_id,
            'itemsForm' => $this->itemsForm($bom),
        ]);
    }

    public function update(Request $request, BomHeader $bom, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureBomBelongsToCompany($bom, $company->id);

        $validated = $this->validateBom($request, $company, $bom);

        DB::transaction(function () use ($bom, $company, $validated, $request): void {
            $bom->fill([
                'status' => $validated['status'],
                'effective_from' => $validated['effective_from'] ?? null,
                'effective_to' => $validated['effective_to'] ?? null,
                'description' => $validated['description'] ?? null,
                'approved_by' => $validated['status'] === 'APPROVED' ? $request->user()?->id : null,
                'approved_at' => $validated['status'] === 'APPROVED' ? now() : null,
            ]);
            $bom->save();

            BomItem::query()->where('bom_header_id', $bom->id)->delete();
            $this->syncItems($bom, $company->id, $validated['items']);
        });

        $audit->record(
            'tenant_bom.updated',
            context: [
                'bom_header_id' => $bom->id,
                'product_id' => $bom->product_id,
                'version_number' => $bom->version_number,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('bom.material-lists.show', $bom)->with('status', __('bom.updated'));
    }

    public function destroy(Request $request, BomHeader $bom, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureBomBelongsToCompany($bom, $company->id);

        $bomId = $bom->id;
        $productId = $bom->product_id;
        $versionNumber = $bom->version_number;

        $bom->delete();

        $audit->record(
            'tenant_bom.removed',
            context: [
                'bom_header_id' => $bomId,
                'product_id' => $productId,
                'version_number' => $versionNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('bom.material-lists.index')->with('status', __('bom.removed'));
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

    private function validateBom(Request $request, Company $company, ?BomHeader $bom = null): array
    {
        return $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('company_id', $company->id),
            ],
            'status' => ['required', 'string', Rule::in(['DRAFT', 'APPROVED', 'OBSOLETE'])],
            'effective_from' => ['nullable', 'date', 'required_if:status,APPROVED'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'description' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.line_no' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.component_product_id' => [
                'required',
                'integer',
                'different:product_id',
                Rule::exists('products', 'id')->where('company_id', $company->id),
            ],
            'items.*.quantity_per' => ['required', 'numeric', 'gt:0'],
            'items.*.scrap_factor' => ['nullable', 'numeric', 'min:0'],
            'items.*.uom' => ['nullable', 'string', 'max:20'],
        ]);
    }

    private function companyProducts(int $companyId)
    {
        return Product::query()
            ->where('company_id', $companyId)
            ->orderBy('sku')
            ->get(['id', 'sku', 'description', 'uom']);
    }

    private function syncItems(BomHeader $bom, int $companyId, array $items): void
    {
        foreach (array_values($items) as $index => $item) {
            $componentProduct = Product::query()
                ->where('company_id', $companyId)
                ->findOrFail((int) $item['component_product_id']);

            BomItem::query()->create([
                'company_id' => $companyId,
                'bom_header_id' => $bom->id,
                'component_product_id' => $componentProduct->id,
                'line_no' => (int) $item['line_no'],
                'quantity_per' => (float) $item['quantity_per'],
                'scrap_factor' => (float) ($item['scrap_factor'] ?? 0),
                'uom' => trim((string) ($item['uom'] ?? '')) !== '' ? trim((string) $item['uom']) : $componentProduct->uom,
            ]);
        }
    }

    private function itemsForm(BomHeader $bom): array
    {
        return $bom->items
            ->sortBy('line_no')
            ->values()
            ->map(static fn (BomItem $item): array => [
                'line_no' => $item->line_no,
                'component_product_id' => $item->component_product_id,
                'quantity_per' => $item->quantity_per,
                'scrap_factor' => $item->scrap_factor,
                'uom' => $item->uom ?? '',
            ])
            ->all();
    }

    private function ensureBomBelongsToCompany(BomHeader $bom, int $companyId): void
    {
        abort_unless((int) $bom->company_id === $companyId, 404);
    }
}