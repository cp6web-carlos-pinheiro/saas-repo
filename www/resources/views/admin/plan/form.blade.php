@extends('layouts.global-admin')
@php($editing = $plan !== null)
@section('title', ($editing ? __('global_plan.edit') : __('global_plan.create')).' | '.__('global_plan.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_plan.title'), 'href' => route('global-admin.plans.index')], ['label' => $editing ? __('global_plan.edit') : __('global_plan.create')]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('global_plan.edit') : __('global_plan.create') }}</h1>

        @if ($errors->any())
            <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.plans.update', $plan) : route('global-admin.plans.store') }}" class="mt-6 space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('global_plan.code') }}
                    <input name="code" value="{{ old('code', $plan?->code) }}" required @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('code'), 'border-[#dadce0]' => ! $errors->has('code')])>
                    @error('code')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('global_plan.label') }}
                    <input name="label" value="{{ old('label', $plan?->label) }}" required @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('label'), 'border-[#dadce0]' => ! $errors->has('label')])>
                    @error('label')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('global_plan.description') }}
                <textarea name="description" rows="4" @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('description'), 'border-[#dadce0]' => ! $errors->has('description')])>{{ old('description', $plan?->description) }}</textarea>
                @error('description')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('global_plan.payment_method') }}
                    <input name="payment_method" value="{{ old('payment_method', $plan?->payment_method) }}" @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('payment_method'), 'border-[#dadce0]' => ! $errors->has('payment_method')])>
                    @error('payment_method')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('global_plan.billing_cycle_label') }}
                    <input name="billing_cycle_label" value="{{ old('billing_cycle_label', $plan?->billing_cycle_label) }}" @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('billing_cycle_label'), 'border-[#dadce0]' => ! $errors->has('billing_cycle_label')])>
                    @error('billing_cycle_label')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <label class="block text-sm font-medium">
                    {{ __('global_plan.trial_days') }}
                    <input type="number" min="1" name="trial_days" value="{{ old('trial_days', $plan?->trial_days) }}" @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('trial_days'), 'border-[#dadce0]' => ! $errors->has('trial_days')])>
                    @error('trial_days')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('global_plan.interval_months') }}
                    <input type="number" min="1" name="interval_months" value="{{ old('interval_months', $plan?->interval_months) }}" @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('interval_months'), 'border-[#dadce0]' => ! $errors->has('interval_months')])>
                    @error('interval_months')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('global_plan.sort_order') }}
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('sort_order'), 'border-[#dadce0]' => ! $errors->has('sort_order')])>
                    @error('sort_order')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('global_plan.default_status') }}
                <x-ui.select name="default_status" class="mt-2" data-search="off">
                    @php($defaultStatus = old('default_status', $plan?->default_status ?? 'active'))
                    <option value="active" @selected($defaultStatus === 'active')>{{ __('global_plan.default_status_active') }}</option>
                    <option value="trialing" @selected($defaultStatus === 'trialing')>{{ __('global_plan.default_status_trialing') }}</option>
                    <option value="canceled" @selected($defaultStatus === 'canceled')>{{ __('global_plan.default_status_canceled') }}</option>
                </x-ui.select>
                @error('default_status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-3 md:grid-cols-2">
                <label class="flex items-center gap-2 text-sm">
                    <input name="renewable" type="checkbox" value="1" @checked(old('renewable', $plan?->renewable ?? true))>
                    {{ __('global_plan.renewable') }}
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input name="allow_once" type="checkbox" value="1" @checked(old('allow_once', $plan?->allow_once ?? false))>
                    {{ __('global_plan.allow_once') }}
                </label>

                <label class="flex items-center gap-2 text-sm md:col-span-2">
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $plan?->is_active ?? true))>
                    {{ __('global_plan.active') }}
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.plans.show', $plan) : route('global-admin.plans.index')" variant="surface-muted" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="brand-primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_plan.save') : __('global_plan.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
