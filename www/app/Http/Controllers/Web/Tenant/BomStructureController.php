<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BomStructureController extends Controller
{
    private const READ_PERMISSION = 'bom.explode';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));

        $structures = Product::query()
            ->where('company_id', $company->id)
            ->whereHas('bomHeaders')
            ->withCount('bomHeaders')
            ->withMax('bomHeaders', 'version_number')
            ->withCount([
                'bomHeaders as approved_bom_headers_count' => static function (Builder $query): void {
                    $query->where('status', 'APPROVED');
                },
            ])
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';

                $query->where(static function (Builder $nested) use ($term): void {
                    $nested->where('sku', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('sku')
            ->paginate(15)
            ->withQueryString();

        return view('client.bom.structures', compact('company', 'structures', 'search'));
    }

    public function show(Request $request, Product $product): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        abort_unless((int) $product->company_id === $company->id, 404);

        $revisions = BomHeader::query()
            ->withCount('items')
            ->where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->orderByDesc('version_number')
            ->get();

        return view('client.bom.structure-show', compact('company', 'product', 'revisions'));
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
}