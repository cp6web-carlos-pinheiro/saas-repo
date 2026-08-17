@extends('layouts.global-admin')
@section('title', $plan->label.' | '.__('global_plan.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$plan->label"
        :subtitle="__('global_plan.details')"
        :breadcrumbs="[['label' => __('global_plan.title'), 'href' => route('global-admin.plans.index')], ['label' => $plan->label]]"
    >
        <x-slot:actions>
            <x-ui.definition-item-status
                :label="__('global_plan.status')"
                :value="$plan->is_active ? __('global_plan.active') : __('global_plan.inactive')"
                :tone="$plan->is_active ? 'success' : 'neutral'"
                inline
            />
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        <x-ui.definition-grid cols="sm:grid-cols-2 xl:grid-cols-3">
            <x-ui.definition-item label="ID">{{ $plan->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.code')">{{ $plan->code }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.label')">{{ $plan->label }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-3" :label="__('global_plan.description')">{{ $plan->description ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-money :label="__('global_plan.amount')" :amount-cents="$plan->amount_cents" />
            <x-ui.definition-item :label="__('global_plan.payment_method')">{{ $plan->payment_method ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.billing_cycle_label')">{{ $plan->billing_cycle_label ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.duration')">
                @if ($plan->trial_days)
                    {{ $plan->trial_days }} {{ $plan->trial_days === 1 ? __('global_plan.day_singular') : __('global_plan.day_plural') }}
                @elseif ($plan->interval_months)
                    {{ $plan->interval_months }} {{ $plan->interval_months === 1 ? __('global_plan.month_singular') : __('global_plan.month_plural') }}
                @else
                    —
                @endif
            </x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.default_status')">{{ $plan->default_status }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.renewable')">{{ $plan->renewable ? __('global_plan.yes') : __('global_plan.no') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.allow_once')">{{ $plan->allow_once ? __('global_plan.yes') : __('global_plan.no') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.sort_order')">{{ $plan->sort_order }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_plan.subscriptions_count')">{{ $plan->subscriptions_count }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('global_plan.created_at')" :value="$plan->created_at" />
            <x-ui.definition-item-date :label="__('global_plan.updated_at')" :value="$plan->updated_at" />
        </x-ui.definition-grid>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.plans.index')" variant="secondary" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.plans.edit', $plan)" variant="primary" class="rounded-full">
                <x-ui.icon name="pencil" size="sm" /> {{ __('global_plan.edit') }}
            </x-ui.button>

            <x-ui.confirm-button
                :action="route('global-admin.plans.destroy', $plan)"
                :label="__('global_plan.remove')"
                :confirm-title="__('global_plan.confirm_delete_title')"
                :confirm-text="__('global_plan.confirm_delete_text', ['name' => $plan->label])"
                :confirm-label="__('global_plan.confirm_delete_confirm')"
                :cancel-label="__('global_plan.confirm_delete_cancel')"
                class="rounded-full"
            />
        </div>
    </x-ui.panel>
</div>
@endsection