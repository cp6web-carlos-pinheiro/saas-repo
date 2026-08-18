<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Scheduling\Application\Services\WorkCenterService;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductionWorkCenterController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'work-centers.read';

    private const CREATE_PERMISSION = 'work-centers.create';

    private const UPDATE_PERMISSION = 'work-centers.update';

    private const SHIFT_CREATE_PERMISSION = 'work-centers.shifts.create';

    public function __construct(private readonly WorkCenterService $service) {}

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'code');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['code', 'name', 'plant', 'resource_type', 'capacity_per_day', 'efficiency_factor', 'is_active'], true), 404);

        $centersQuery = WorkCenter::query()
            ->with('plant:id,code,name')
            ->when($status !== '', static fn ($query) => $query->where('is_active', $status === 'ACTIVE'))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('resource_type', 'like', "%{$search}%");
                });
            });

        if ($sort === 'plant') {
            $centersQuery->orderBy(Plant::query()->select('name')->whereColumn('plants.id', 'work_centers.plant_id'), $direction);
        } else {
            $centersQuery->orderBy($sort, $direction);
        }

        $centers = $centersQuery
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('client.production.work-centers.search', compact('company', 'centers', 'search', 'status', 'sort', 'direction'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $plants = Plant::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('client.production.work-centers.form', [
            'company' => $company,
            'workCenter' => null,
            'plants' => $plants,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'resource_type' => ['required', 'string', 'in:MACHINE,LINE'],
            'capacity_per_day' => ['required', 'numeric', 'min:0'],
            'efficiency_factor' => ['required', 'numeric', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $created = $this->service->create($data);

        return redirect()->route('production.work-centers.show', (int) ($created['id'] ?? 0))->with('status', __('production.work_centers.created'));
    }

    public function show(Request $request, WorkCenter $workCenter): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        abort_unless((int) $workCenter->company_id === (int) $company->id, 404);

        $workCenter->load([
            'plant:id,code,name',
            'shifts' => static fn ($query) => $query->orderBy('shift_start'),
            'calendarDays' => static fn ($query) => $query->orderByDesc('calendar_date')->limit(20),
        ]);

        return view('client.production.work-centers.show', compact('company', 'workCenter'));
    }

    public function edit(Request $request, WorkCenter $workCenter): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, [self::UPDATE_PERMISSION, self::CREATE_PERMISSION]);
        abort_unless((int) $workCenter->company_id === (int) $company->id, 404);

        $plants = Plant::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('client.production.work-centers.form', [
            'company' => $company,
            'workCenter' => $workCenter,
            'plants' => $plants,
        ]);
    }

    public function update(Request $request, WorkCenter $workCenter): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, [self::UPDATE_PERMISSION, self::CREATE_PERMISSION]);
        abort_unless((int) $workCenter->company_id === (int) $company->id, 404);

        $data = $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'resource_type' => ['required', 'string', 'in:MACHINE,LINE'],
            'capacity_per_day' => ['required', 'numeric', 'min:0'],
            'efficiency_factor' => ['required', 'numeric', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->service->update((int) $workCenter->id, $data);

        return redirect()->route('production.work-centers.show', $workCenter)->with('status', __('production.work_centers.updated'));
    }

    public function storeShift(Request $request, WorkCenter $workCenter): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::SHIFT_CREATE_PERMISSION, $company->id);
        abort_unless((int) $workCenter->company_id === (int) $company->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'shift_start' => ['required', 'date_format:H:i'],
            'shift_end' => ['required', 'date_format:H:i'],
            'capacity_hours' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->service->addShift((int) $workCenter->id, $data);

        return redirect()->route('production.work-centers.show', $workCenter)->with('status', __('production.work_centers.shift_added'));
    }
}
