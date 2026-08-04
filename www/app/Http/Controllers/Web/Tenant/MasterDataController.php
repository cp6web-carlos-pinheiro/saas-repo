<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\MasterDataRecord;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class MasterDataController extends Controller
{
    use HandlesTenantAuthorization;

    /**
     * @var array<string, array{permission: string, code_regex?: string, code_max: int}>
     */
    private const DOMAINS = [
        'units' => ['permission' => 'admin-data.units', 'code_max' => 20],
        'categories' => ['permission' => 'admin-data.categories', 'code_max' => 40],
        'brands' => ['permission' => 'admin-data.brands', 'code_max' => 40],
    ];

    public function index(Request $request, string $domain): View
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.read', $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['name', 'code', 'is_active', 'created_at'], true), 404);

        $records = MasterDataRecord::query()
            ->where('company_id', $company->id)
            ->where('domain', $domain)
            ->when($status !== '', static fn (Builder $query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.admin-data.search', compact('records', 'domain', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request, string $domain): View
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.create', $company->id);

        return view('client.admin-data.form', [
            'record' => null,
            'domain' => $domain,
            'company' => $company,
        ]);
    }

    public function show(Request $request, string $domain, MasterDataRecord $record): View
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.read', $company->id);

        $this->assertRecordDomain($record, $domain, $company);

        return view('client.admin-data.show', compact('record', 'domain', 'company'));
    }

    public function store(Request $request, string $domain, AuditLogService $audit): RedirectResponse
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.create', $company->id);

        $data = $this->validateRecord($request, $company, $domain, null, $config);

        $record = MasterDataRecord::query()->create([
            'company_id' => $company->id,
            'domain' => $domain,
            'code' => mb_strtoupper((string) $data['code']),
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
            'metadata' => null,
        ]);

        $audit->record(
            'tenant_master_data.'.$domain.'.created',
            context: [
                'record_id' => $record->id,
                'domain' => $domain,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.index', ['domain' => $domain])->with('status', __('admin_data.created'));
    }

    public function edit(Request $request, string $domain, MasterDataRecord $record): View
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.update', $company->id);

        $this->assertRecordDomain($record, $domain, $company);

        return view('client.admin-data.form', compact('record', 'domain', 'company'));
    }

    public function update(Request $request, string $domain, MasterDataRecord $record, AuditLogService $audit): RedirectResponse
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.update', $company->id);

        $this->assertRecordDomain($record, $domain, $company);

        $data = $this->validateRecord($request, $company, $domain, $record, $config);

        $record->fill([
            'code' => mb_strtoupper((string) $data['code']),
            'name' => (string) $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_by' => $request->user()?->id,
            'metadata' => null,
        ]);
        $record->save();

        $audit->record(
            'tenant_master_data.'.$domain.'.updated',
            context: [
                'record_id' => $record->id,
                'domain' => $domain,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.index', ['domain' => $domain])->with('status', __('admin_data.updated'));
    }

    public function destroy(Request $request, string $domain, MasterDataRecord $record, AuditLogService $audit): RedirectResponse
    {
        $config = $this->domainConfig($domain);
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, $config['permission'].'.update', $company->id);

        $this->assertRecordDomain($record, $domain, $company);

        if ($this->recordHasDependencies($record)) {
            return redirect()
                ->route('admin-data.show', ['domain' => $domain, 'record' => $record->id])
                ->withErrors(['record' => __('admin_data.remove_blocked')]);
        }

        $recordId = $record->id;
        $recordCode = $record->code;
        $record->delete();

        $audit->record(
            'tenant_master_data.'.$domain.'.removed',
            context: [
                'record_id' => $recordId,
                'record_code' => $recordCode,
                'domain' => $domain,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin-data.index', ['domain' => $domain])->with('status', __('admin_data.removed'));
    }

    /**
     * @param array{permission: string, code_max: int, code_regex?: string} $config
        * @return array{code: string, name: string, description?: string|null, is_active?: bool}
     */
    private function validateRecord(Request $request, Company $company, string $domain, ?MasterDataRecord $record, array $config): array
    {
        $rules = [
            'code' => [
                'required',
                'string',
                'max:'.$config['code_max'],
                Rule::unique('master_data_records', 'code')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', $domain))
                    ->ignore($record?->id),
            ],
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('master_data_records', 'name')
                    ->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', $domain))
                    ->ignore($record?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (isset($config['code_regex'])) {
            $rules['code'][] = 'regex:'.$config['code_regex'];
        }

        return $request->validate($rules);
    }

    /**
     * @return array{permission: string, code_max: int, code_regex?: string}
     */
    private function domainConfig(string $domain): array
    {
        abort_unless(isset(self::DOMAINS[$domain]), 404);

        return self::DOMAINS[$domain];
    }

    private function assertRecordDomain(MasterDataRecord $record, string $domain, Company $company): void
    {
        abort_unless((int) $record->company_id === (int) $company->id, 404);
        abort_unless($record->domain === $domain, 404);
    }

    private function recordHasDependencies(MasterDataRecord $record): bool
    {
        $id = (int) $record->id;
        $companyId = (int) $record->company_id;

        return match ($record->domain) {
            'units' => DB::table('products')->where('company_id', $companyId)->where('unit_id', $id)->exists(),
            'categories' => DB::table('products')->where('company_id', $companyId)->where('category_id', $id)->exists(),
            'brands' => DB::table('products')->where('company_id', $companyId)->where('brand_id', $id)->exists(),
            default => false,
        };
    }
}
