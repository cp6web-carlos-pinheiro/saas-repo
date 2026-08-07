<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Scheduling\Application\Services\ProductionSchedulingService;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ProductionSchedulingWebController extends Controller
{
    use HandlesTenantAuthorization;

    private const RUN_PERMISSION = 'production-scheduling.run';

    public function __construct(private readonly ProductionSchedulingService $service)
    {
    }

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RUN_PERMISSION, $company->id);

        $orders = ProductionOrder::query()
            ->whereIn('status', ['RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED'])
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'order_number', 'product_id', 'source_reference_id', 'source_reference_type', 'scheduled_start_date', 'scheduled_end_date', 'status']);

        return view('client.production.scheduling.search', [
            'company' => $company,
            'orders' => $orders,
        ]);
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RUN_PERMISSION, $company->id);

        $orders = ProductionOrder::query()
            ->whereIn('status', ['RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED'])
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'order_number', 'source_reference_id', 'source_reference_type', 'scheduled_end_date', 'status']);

        return view('client.production.scheduling.form', [
            'company' => $company,
            'orders' => $orders,
            'runKey' => null,
            'input' => [
                'reference_date' => now()->toDateString(),
                'mode' => 'finite',
                'direction' => 'forward',
                'sequencing_rule' => 'priority_due_date',
                'production_order_ids' => [],
            ],
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        return $this->execute($request, null);
    }

    public function show(Request $request, string $run): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RUN_PERMISSION, $company->id);

        $payload = Cache::get($this->cacheKey($run));
        abort_unless(is_array($payload), 404);

        return view('client.production.scheduling.show', [
            'company' => $company,
            'runKey' => $run,
            'result' => $payload['result'],
            'input' => $payload['input'],
        ]);
    }

    public function edit(Request $request, string $run): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RUN_PERMISSION, $company->id);

        $payload = Cache::get($this->cacheKey($run));
        abort_unless(is_array($payload), 404);

        $orders = ProductionOrder::query()
            ->whereIn('status', ['RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED'])
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'order_number', 'source_reference_id', 'source_reference_type', 'scheduled_end_date', 'status']);

        return view('client.production.scheduling.form', [
            'company' => $company,
            'orders' => $orders,
            'runKey' => $run,
            'input' => $payload['input'],
        ]);
    }

    public function update(Request $request, string $run): RedirectResponse
    {
        return $this->execute($request, $run);
    }

    private function execute(Request $request, ?string $run): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::RUN_PERMISSION, $company->id);

        $data = $request->validate([
            'reference_date' => ['nullable', 'date'],
            'mode' => ['nullable', 'string', 'in:finite,infinite'],
            'direction' => ['nullable', 'string', 'in:forward,backward'],
            'sequencing_rule' => ['nullable', 'string', 'in:priority_due_date,due_date_priority,release_date_priority,order_number'],
            'production_order_ids' => ['required', 'array', 'min:1'],
            'production_order_ids.*' => ['integer', 'exists:production_orders,id'],
        ]);

        try {
            $result = $this->service->schedule($data);
        } catch (DomainException $exception) {
            $target = $run === null ? 'production.scheduling.create' : 'production.scheduling.edit';

            return redirect()
                ->route($target, $run === null ? [] : ['run' => $run])
                ->withErrors(['scheduling' => $exception->getMessage()])
                ->withInput($data);
        }

        $runKey = Str::lower(Str::random(16));
        Cache::put($this->cacheKey($runKey), [
            'input' => $data,
            'result' => $result,
        ], now()->addMinutes(30));

        return redirect()
            ->route('production.scheduling.show', ['run' => $runKey])
            ->with('status', __('production.scheduling.completed'));
    }

    private function cacheKey(string $run): string
    {
        return 'production:scheduling:web:'.$run;
    }
}
