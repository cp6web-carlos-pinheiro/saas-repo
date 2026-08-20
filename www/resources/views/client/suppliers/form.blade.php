@extends('layouts.client-area')

@php($editing = $supplier !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_suppliers'))
@section('client-page-title', $editing ? __('supplier.edit') : __('supplier.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $editing ? __('supplier.edit') : __('supplier.create') }}">
        <x-slot:actions>
        <x-ui.button :href="$editing ? route('purchasing.suppliers.show', $supplier) : route('purchasing.suppliers.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.suppliers.update', $supplier) : route('purchasing.suppliers.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('supplier.name') }}" for="name" :required="true" :error="$errors->first('name')">
                <x-ui.input name="name" :value="old('name', $supplier?->name)" required @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('name'), 'border-[var(--ui-border)]' => ! $errors->has('name')])  id="name" :aria-describedby="$errors->has('name') ? 'name-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('supplier.person_type') }}" for="supplier-person-type" :required="true" :error="$errors->first('person_type')">
                <x-ui.select id="supplier-person-type" name="person_type" class="mt-2" required data-search="off" :aria-describedby="$errors->has('person_type') ? 'supplier-person-type-error' : null">
                    <option value="PJ" @selected(old('person_type', $supplier?->person_type ?? 'PJ') === 'PJ')>{{ __('supplier.person_type_pj') }}</option>
                    <option value="PF" @selected(old('person_type', $supplier?->person_type ?? 'PJ') === 'PF')>{{ __('supplier.person_type_pf') }}</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="{{ __('supplier.email') }}" for="email" :error="$errors->first('email')">
                <x-ui.input name="email" type="email" :value="old('email', $supplier?->email)" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('email'), 'border-[var(--ui-border)]' => ! $errors->has('email')])  id="email" :aria-describedby="$errors->has('email') ? 'email-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('supplier.phone') }}" for="phone" :error="$errors->first('phone')">
                <x-ui.input name="phone" :value="old('phone', $supplier?->phone)" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('phone'), 'border-[var(--ui-border)]' => ! $errors->has('phone')])  id="phone" :aria-describedby="$errors->has('phone') ? 'phone-error' : null"/>
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="{{ __('supplier.status') }}" for="status" :required="true" :error="$errors->first('status')">
                    <x-ui.select name="status" class="mt-2" required data-search="off" id="status" :aria-describedby="$errors->has('status') ? 'status-error' : null">
                        <option value="ACTIVE" @selected(old('status', $supplier?->status ?? 'ACTIVE') === 'ACTIVE')>{{ __('supplier.active') }}</option>
                        <option value="INACTIVE" @selected(old('status', $supplier?->status ?? 'ACTIVE') === 'INACTIVE')>{{ __('supplier.inactive') }}</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="{{ __('supplier.default_lead_time_days') }}" for="default-lead-time-days" :error="$errors->first('default_lead_time_days')">
                    <x-ui.input name="default_lead_time_days" type="number" min="0" :value="old('default_lead_time_days', $supplier?->default_lead_time_days ?? 0)" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('default_lead_time_days'), 'border-[var(--ui-border)]' => ! $errors->has('default_lead_time_days')])  id="default-lead-time-days" :aria-describedby="$errors->has('default_lead_time_days') ? 'default-lead-time-days-error' : null"/>
                </x-ui.field>
            </div>

            <x-ui.field label="{{ __('supplier.payment_terms') }}" for="payment-terms" :error="$errors->first('payment_terms')">
                <x-ui.input name="payment_terms" :value="old('payment_terms', $supplier?->payment_terms)" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('payment_terms'), 'border-[var(--ui-border)]' => ! $errors->has('payment_terms')])  id="payment-terms" :aria-describedby="$errors->has('payment_terms') ? 'payment-terms-error' : null"/>
            </x-ui.field>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.suppliers.show', $supplier) : route('purchasing.suppliers.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('supplier.save') : __('supplier.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
