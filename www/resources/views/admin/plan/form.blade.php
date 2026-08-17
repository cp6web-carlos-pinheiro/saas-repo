@extends('layouts.global-admin')
@php($editing = $plan !== null)
@section('title', ($editing ? __('global_plan.edit') : __('global_plan.create')).' | '.__('global_plan.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$editing ? __('global_plan.edit') : __('global_plan.create')"
        :breadcrumbs="[['label' => __('global_plan.title'), 'href' => route('global-admin.plans.index')], ['label' => $editing ? __('global_plan.edit') : __('global_plan.create')]]"
    />

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        @if ($errors->any())
            <x-ui.alert class="mb-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.plans.update', $plan) : route('global-admin.plans.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field :label="__('global_plan.code')" for="code" required :error="$errors->first('code')">
                    <x-ui.input id="code" name="code" :value="old('code', $plan?->code)" required />
                </x-ui.field>

                <x-ui.field :label="__('global_plan.label')" for="label" required :error="$errors->first('label')">
                    <x-ui.input id="label" name="label" :value="old('label', $plan?->label)" required />
                </x-ui.field>
            </div>

            <x-ui.field :label="__('global_plan.description')" for="description" :error="$errors->first('description')">
                <x-ui.textarea id="description" name="description" rows="4">{{ old('description', $plan?->description) }}</x-ui.textarea>
            </x-ui.field>

            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.field :label="__('global_plan.payment_method')" for="payment_method" :error="$errors->first('payment_method')">
                    <x-ui.input id="payment_method" name="payment_method" :value="old('payment_method', $plan?->payment_method)" />
                </x-ui.field>

                <x-ui.field :label="__('global_plan.amount')" for="amount" required :error="$errors->first('amount')">
                    <x-ui.input id="amount" name="amount" :value="old('amount', number_format(($plan?->amount_cents ?? 0) / 100, 2, ',', '.'))" required inputmode="decimal" data-currency-mask="brl" />
                </x-ui.field>

                <x-ui.field :label="__('global_plan.billing_cycle_label')" for="billing_cycle_label" :error="$errors->first('billing_cycle_label')">
                    <x-ui.input id="billing_cycle_label" name="billing_cycle_label" :value="old('billing_cycle_label', $plan?->billing_cycle_label)" />
                </x-ui.field>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.field :label="__('global_plan.trial_days')" for="trial_days" :error="$errors->first('trial_days')">
                    <x-ui.input id="trial_days" type="number" min="0" name="trial_days" :value="old('trial_days', $plan?->trial_days)" />
                </x-ui.field>

                <x-ui.field :label="__('global_plan.interval_months')" for="interval_months" :error="$errors->first('interval_months')">
                    <x-ui.input id="interval_months" type="number" min="1" name="interval_months" :value="old('interval_months', $plan?->interval_months)" />
                </x-ui.field>

                <x-ui.field :label="__('global_plan.sort_order')" for="sort_order" :error="$errors->first('sort_order')">
                    <x-ui.input id="sort_order" type="number" min="0" name="sort_order" :value="old('sort_order', $plan?->sort_order ?? 0)" />
                </x-ui.field>
            </div>

            <x-ui.field :label="__('global_plan.default_status')" for="default_status" :error="$errors->first('default_status')">
                @php($defaultStatus = old('default_status', $plan?->default_status ?? 'active'))
                <x-ui.select id="default_status" name="default_status" data-search="on">
                    <option value="active" @selected($defaultStatus === 'active')>{{ __('global_plan.default_status_active') }}</option>
                    <option value="trialing" @selected($defaultStatus === 'trialing')>{{ __('global_plan.default_status_trialing') }}</option>
                    <option value="canceled" @selected($defaultStatus === 'canceled')>{{ __('global_plan.default_status_canceled') }}</option>
                </x-ui.select>
            </x-ui.field>

            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.checkbox name="renewable" value="1" :checked="old('renewable', $plan?->renewable ?? true)">{{ __('global_plan.renewable') }}</x-ui.checkbox>
                <x-ui.checkbox name="allow_once" value="1" :checked="old('allow_once', $plan?->allow_once ?? false)">{{ __('global_plan.allow_once') }}</x-ui.checkbox>
                <x-ui.checkbox class="md:col-span-2" name="is_active" value="1" :checked="old('is_active', $plan?->is_active ?? true)">{{ __('global_plan.active') }}</x-ui.checkbox>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.plans.show', $plan) : route('global-admin.plans.index')" variant="secondary" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_plan.save') : __('global_plan.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection