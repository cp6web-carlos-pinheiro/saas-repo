@extends('layouts.global-admin')
@section('title', $plan->label.' | '.__('global_plan.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_plan.title'), 'href' => route('global-admin.plans.index')], ['label' => $plan->label]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-[#5f6368]">{{ __('global_plan.details') }}</p>
                <h1 class="font-display text-3xl font-bold">{{ $plan->label }}</h1>
            </div>
            <span class="rounded-full px-3 py-1 text-xs {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $plan->is_active ? __('global_plan.active') : __('global_plan.inactive') }}
            </span>
        </div>

        <dl class="mt-8 divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">ID</dt>
                <dd class="font-medium">{{ $plan->id }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.code') }}</dt>
                <dd class="font-medium">{{ $plan->code }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.label') }}</dt>
                <dd class="font-medium">{{ $plan->label }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.description') }}</dt>
                <dd class="font-medium">{{ $plan->description ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.amount') }}</dt>
                <dd class="font-medium">R$ {{ number_format($plan->amount_cents / 100, 2, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.payment_method') }}</dt>
                <dd class="font-medium">{{ $plan->payment_method ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.billing_cycle_label') }}</dt>
                <dd class="font-medium">{{ $plan->billing_cycle_label ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.duration') }}</dt>
                <dd class="font-medium">
                    @if ($plan->trial_days)
                        {{ $plan->trial_days }} {{ $plan->trial_days === 1 ? __('global_plan.day_singular') : __('global_plan.day_plural') }}
                    @elseif ($plan->interval_months)
                        {{ $plan->interval_months }} {{ $plan->interval_months === 1 ? __('global_plan.month_singular') : __('global_plan.month_plural') }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.default_status') }}</dt>
                <dd class="font-medium">{{ $plan->default_status }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.renewable') }}</dt>
                <dd class="font-medium">{{ $plan->renewable ? __('global_plan.yes') : __('global_plan.no') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.allow_once') }}</dt>
                <dd class="font-medium">{{ $plan->allow_once ? __('global_plan.yes') : __('global_plan.no') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.sort_order') }}</dt>
                <dd class="font-medium">{{ $plan->sort_order }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.subscriptions_count') }}</dt>
                <dd class="font-medium">{{ $plan->subscriptions_count }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.created_at') }}</dt>
                <dd class="font-medium">{{ $plan->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_plan.updated_at') }}</dt>
                <dd class="font-medium">{{ $plan->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.plans.index')" variant="surface-muted" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.plans.edit', $plan)" variant="brand-primary" class="rounded-full">
                {{ __('global_plan.edit') }}
            </x-ui.button>

            <form method="POST" action="{{ route('global-admin.plans.destroy', $plan) }}" data-admin-delete-confirm data-admin-name="{{ $plan->label }}" data-confirm-title="{{ __('global_plan.confirm_delete_title') }}" data-confirm-text="{{ __('global_plan.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_plan.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_plan.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('global_plan.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
