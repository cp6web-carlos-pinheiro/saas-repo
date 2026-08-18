<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Scheduling\Application\Services\ProductionCalendarService;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionCalendarDay;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductionCalendarWebController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'production-calendar.read';

    private const CREATE_PERMISSION = 'production-calendar.update';

    private const UPDATE_PERMISSION = 'production-calendar.update';

    private const GENERATE_PERMISSION = 'production-calendar.generate';

    public function __construct(private readonly ProductionCalendarService $service) {}

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $workCenters = WorkCenter::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);

        $workCenterId = (int) $request->query('work_center_id', 0);
        $fromDate = (string) $request->query('from_date', now()->startOfMonth()->toDateString());
        $toDate = (string) $request->query('to_date', now()->endOfMonth()->toDateString());
        $sort = (string) $request->query('sort', 'calendar_date');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        abort_unless(in_array($sort, ['calendar_date', 'work_center', 'is_working_day', 'available_capacity', 'notes'], true), 404);

        $daysQuery = ProductionCalendarDay::query()
            ->with('workCenter:id,code,name')
            ->when($workCenterId > 0, static fn ($query) => $query->where('work_center_id', $workCenterId))
            ->whereDate('calendar_date', '>=', $fromDate)
            ->whereDate('calendar_date', '<=', $toDate);

        if ($sort === 'work_center') {
            $daysQuery->orderBy(WorkCenter::query()->select('name')->whereColumn('work_centers.id', 'production_calendar_days.work_center_id'), $direction);
        } else {
            $daysQuery->orderBy($sort, $direction);
        }

        $days = $daysQuery
            ->orderBy('id', $direction)
            ->paginate(20)
            ->withQueryString();

        return view('client.production.calendar.search', compact('company', 'workCenters', 'workCenterId', 'fromDate', 'toDate', 'sort', 'direction', 'days'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $workCenters = WorkCenter::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);

        return view('client.production.calendar.form', [
            'company' => $company,
            'day' => null,
            'workCenters' => $workCenters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $request->validate([
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'calendar_date' => ['required', 'date'],
            'is_working_day' => ['required', 'boolean'],
            'available_capacity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $day = $this->service->upsertDay((int) $data['work_center_id'], $data);

        return redirect()->route('production.calendar.show', (int) ($day['id'] ?? 0))->with('status', __('production.calendar.created'));
    }

    public function show(Request $request, ProductionCalendarDay $day): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        abort_unless((int) $day->company_id === (int) $company->id, 404);

        $day->load('workCenter:id,code,name');

        return view('client.production.calendar.show', compact('company', 'day'));
    }

    public function edit(Request $request, ProductionCalendarDay $day): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);
        abort_unless((int) $day->company_id === (int) $company->id, 404);

        $workCenters = WorkCenter::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);

        return view('client.production.calendar.form', [
            'company' => $company,
            'day' => $day,
            'workCenters' => $workCenters,
        ]);
    }

    public function update(Request $request, ProductionCalendarDay $day): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);
        abort_unless((int) $day->company_id === (int) $company->id, 404);

        $data = $request->validate([
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'calendar_date' => ['required', 'date'],
            'is_working_day' => ['required', 'boolean'],
            'available_capacity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->upsertDay((int) $data['work_center_id'], $data);

        return redirect()->route('production.calendar.show', $day)->with('status', __('production.calendar.updated'));
    }

    public function upsertDay(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $request->validate([
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'calendar_date' => ['required', 'date'],
            'is_working_day' => ['required', 'boolean'],
            'available_capacity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->upsertDay((int) $data['work_center_id'], $data);

        return redirect()->route('production.calendar.index', [
            'work_center_id' => (int) $data['work_center_id'],
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->endOfMonth()->toDateString(),
        ])->with('status', __('production.calendar.updated'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::GENERATE_PERMISSION, $company->id);

        $data = $request->validate([
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $this->service->bulkGenerate((int) $data['work_center_id'], (string) $data['from_date'], (string) $data['to_date']);

        return redirect()->route('production.calendar.index', [
            'work_center_id' => (int) $data['work_center_id'],
            'from_date' => (string) $data['from_date'],
            'to_date' => (string) $data['to_date'],
        ])->with('status', __('production.calendar.generated'));
    }
}
