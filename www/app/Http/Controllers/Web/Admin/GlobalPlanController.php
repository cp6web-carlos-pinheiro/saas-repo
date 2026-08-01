<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Plan;
use App\Services\SaaS\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class GlobalPlanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'sort_order');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['code', 'label', 'amount_cents', 'is_active', 'sort_order', 'created_at'], true), 404);

        $plans = Plan::query()
            ->withCount('subscriptions')
            ->when($search !== '', static fn ($query) => $query->where(static fn ($q) => $q
                ->where('code', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.plan.search', compact('plans', 'search', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.plan.form', ['plan' => null]);
    }

    public function show(Plan $plan): View
    {
        $plan->loadCount('subscriptions');

        return view('admin.plan.show', compact('plan'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validatePlan($request);

        $plan = Plan::query()->create($data + ['is_active' => true]);

        $audit->record(
            'platform_plan.created',
            context: ['plan_id' => $plan->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.plans.index')->with('status', __('global_plan.created'));
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plan.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validatePlan($request, $plan);

        $plan->fill($data);
        $plan->is_active = (bool) ($data['is_active'] ?? false);
        $plan->save();

        $audit->record(
            'platform_plan.updated',
            context: ['plan_id' => $plan->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.plans.index')->with('status', __('global_plan.updated'));
    }

    public function destroy(Request $request, Plan $plan, AuditLogService $audit): RedirectResponse
    {
        $planId = $plan->id;
        $plan->delete();

        $audit->record(
            'platform_plan.removed',
            context: ['plan_id' => $planId, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.plans.index')->with('status', __('global_plan.removed'));
    }

    private function validatePlan(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:80', Rule::unique('plans', 'code')->ignore($plan)],
            'label' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'string', 'max:30'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'billing_cycle_label' => ['nullable', 'string', 'max:180'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'interval_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'renewable' => ['nullable', 'boolean'],
            'allow_once' => ['nullable', 'boolean'],
            'default_status' => ['required', 'string', Rule::in(['active', 'trialing', 'canceled'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($data['trial_days'] ?? null) === null && ($data['interval_months'] ?? null) === null) {
            throw ValidationException::withMessages([
                'trial_days' => __('global_plan.duration_required'),
            ]);
        }

        if (($data['trial_days'] ?? null) !== null) {
            $data['interval_months'] = null;
        }

        if (($data['interval_months'] ?? null) !== null) {
            $data['trial_days'] = null;
        }

        $data['amount_cents'] = $this->normalizeAmountToCents((string) $data['amount']);
        unset($data['amount']);

        $data['renewable'] = (bool) ($data['renewable'] ?? false);
        $data['allow_once'] = (bool) ($data['allow_once'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function normalizeAmountToCents(string $rawAmount): int
    {
        $normalized = trim($rawAmount);
        $normalized = str_replace(' ', '', $normalized);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        if (! is_numeric($normalized)) {
            throw ValidationException::withMessages([
                'amount' => __('global_plan.invalid_amount'),
            ]);
        }

        $cents = (int) round(((float) $normalized) * 100);

        if ($cents < 0) {
            throw ValidationException::withMessages([
                'amount' => __('global_plan.invalid_amount'),
            ]);
        }

        return $cents;
    }
}
