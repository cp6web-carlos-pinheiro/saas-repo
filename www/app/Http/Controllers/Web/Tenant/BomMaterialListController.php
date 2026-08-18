<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomItem;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $sortableColumns = ['id', 'product', 'version_number', 'status', 'effective_from', 'effective_to', 'items_count', 'description'];
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

        if ($sort === 'product') {
            $query->orderBy(Product::query()->select('sku')->whereColumn('products.id', 'bom_headers.product_id'), $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $boms = $query->orderBy('id', $direction)->paginate(15)->withQueryString();

        return view('client.bom.search', compact('company', 'search', 'sort', 'direction', 'boms'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);

        $itemsForm = $this->oldItemsForm($request, [[
            'line_no' => 1,
            'component_product_id' => null,
            'quantity_per' => 1,
            'unit_id' => null,
        ]]);
        $selectedProductId = (int) $request->old('product_id', (int) $request->query('product_id', 0));
        $products = $this->companyProductsByIds(
            $company->id,
            $this->selectedProductIds($selectedProductId, $itemsForm, 'component_product_id')
        );

        return view('client.bom.form', [
            'company' => $company,
            'bom' => null,
            'products' => $products,
            'units' => $this->unitOptions($company->id),
            'editing' => false,
            'selectedProductId' => $selectedProductId > 0 ? $selectedProductId : null,
            'itemsForm' => $itemsForm,
        ]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);

        $validated = $this->validateBom($request, $company);
        $this->ensureApprovedWindowDoesNotOverlap($company, $validated);

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
        $itemsForm = $this->oldItemsForm($request, $this->itemsForm($bom));

        return view('client.bom.form', [
            'company' => $company,
            'bom' => $bom,
            'products' => $this->companyProductsByIds(
                $company->id,
                $this->selectedProductIds((int) $request->old('product_id', $bom->product_id), $itemsForm, 'component_product_id')
            ),
            'units' => $this->unitOptions($company->id),
            'editing' => true,
            'selectedProductId' => $bom->product_id,
            'itemsForm' => $itemsForm,
        ]);
    }

    public function update(Request $request, BomHeader $bom, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::WRITE_PERMISSION, $company->id);
        $this->ensureBomBelongsToCompany($bom, $company->id);

        $validated = $this->validateBom($request, $company, $bom);
        $this->ensureApprovedWindowDoesNotOverlap($company, $validated, $bom);

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

    public function componentProductUnit(Request $request, Product $product): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        abort_unless((int) $product->company_id === (int) $company->id, 404);

        $unit = Unit::query()->find($product->unit_id, ['id', 'code', 'name']);
        $uom = mb_strtoupper(trim((string) ($product->uom ?? '')));

        return response()->json([
            'product_id' => (int) $product->id,
            'unit_id' => $unit instanceof Unit ? (int) $unit->id : null,
            'uom' => $uom,
            'unit_label' => $unit instanceof Unit ? sprintf('%s - %s', $unit->code, $unit->name) : $uom,
        ]);
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
            'items.*.component_product_id' => [
                'required',
                'integer',
                'different:product_id',
                Rule::exists('products', 'id')->where('company_id', $company->id),
            ],
            'items.*.quantity_per' => ['required', 'numeric', 'gt:0'],
        ]);
    }

    private function companyProductsByIds(int $companyId, array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $ids)
            ->orderBy('sku')
            ->with('unit:id,code')
            ->get(['id', 'sku', 'description', 'unit_id']);
    }

    private function oldItemsForm(Request $request, array $fallback): array
    {
        $items = $request->old('items');

        return is_array($items) && $items !== [] ? array_values($items) : $fallback;
    }

    private function selectedProductIds(int $primaryId, array $items, string $itemKey): array
    {
        return collect($items)
            ->pluck($itemKey)
            ->prepend($primaryId > 0 ? $primaryId : null)
            ->filter(static fn ($id): bool => (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
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
                'line_no' => $index + 1,
                'quantity_per' => (float) $item['quantity_per'],
                'unit_id' => $componentProduct->unit_id !== null ? (int) $componentProduct->unit_id : null,
                'uom' => $this->resolveItemUom($componentProduct),
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
                'unit_id' => $item->unit_id,
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function unitOptions(int $companyId): array
    {
        return Unit::query()
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN company_id = ? THEN 0 ELSE 1 END', [$companyId])
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(static fn (Unit $unit): array => [$unit->id => sprintf('%s - %s', $unit->code, $unit->name)])
            ->all();
    }

    private function resolveItemUom(Product $componentProduct): string
    {
        $unitCode = Unit::query()
            ->whereKey($componentProduct->unit_id)
            ->value('code');

        if (is_string($unitCode) && trim($unitCode) !== '') {
            return mb_strtoupper($unitCode);
        }

        $fallbackUom = trim((string) ($componentProduct->uom ?? ''));

        return $fallbackUom !== '' ? mb_strtoupper($fallbackUom) : 'UN';
    }

    private function ensureBomBelongsToCompany(BomHeader $bom, int $companyId): void
    {
        abort_unless((int) $bom->company_id === $companyId, 404);
    }

    private function ensureApprovedWindowDoesNotOverlap(Company $company, array $validated, ?BomHeader $current = null): void
    {
        if (($validated['status'] ?? 'DRAFT') !== 'APPROVED') {
            return;
        }

        $effectiveFrom = isset($validated['effective_from']) && $validated['effective_from'] !== ''
            ? (string) $validated['effective_from']
            : null;
        $effectiveTo = isset($validated['effective_to']) && $validated['effective_to'] !== ''
            ? (string) $validated['effective_to']
            : null;

        if ($effectiveFrom === null) {
            return;
        }

        $query = BomHeader::query()
            ->where('company_id', $company->id)
            ->where('product_id', (int) $validated['product_id'])
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $effectiveTo ?? $effectiveFrom)
            ->where(static function (Builder $builder) use ($effectiveFrom): void {
                $builder->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $effectiveFrom);
            });

        if ($current instanceof BomHeader) {
            $query->where('id', '!=', $current->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'effective_from' => __('bom.approved_overlap_window'),
            ]);
        }
    }
}
