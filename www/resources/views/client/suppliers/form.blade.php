@extends('layouts.client-area')

@php($editing = $supplier !== null)

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_suppliers'))
@section('client-page-title', $editing ? __('supplier.edit') : __('supplier.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('supplier.edit') : __('supplier.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('purchasing.suppliers.show', $supplier) : route('purchasing.suppliers.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.suppliers.update', $supplier) : route('purchasing.suppliers.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('supplier.name') }}
                <x-ui.input name="name" :value="old('name', $supplier?->name)" required @class(['mt-2', 'border-red-500' => $errors->has('name'), 'border-[#dadce0]' => ! $errors->has('name')]) />
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('supplier.person_type') }}
                    <x-ui.select id="supplier-person-type" name="person_type" class="mt-2" required data-search="off">
                        <option value="PJ" @selected(old('person_type', $supplier?->person_type ?? 'PJ') === 'PJ')>{{ __('supplier.person_type_pj') }}</option>
                        <option value="PF" @selected(old('person_type', $supplier?->person_type ?? 'PJ') === 'PF')>{{ __('supplier.person_type_pf') }}</option>
                    </x-ui.select>
                    @error('person_type')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('supplier.tax_id') }}
                    <x-ui.input name="tax_id" :value="old('tax_id', $supplier?->tax_id)" inputmode="numeric" data-tax-id-mask="true" data-person-type-source="supplier-person-type" @class(['mt-2', 'border-red-500' => $errors->has('tax_id'), 'border-[#dadce0]' => ! $errors->has('tax_id')]) />
                    @error('tax_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('supplier.email') }}
                <x-ui.input name="email" type="email" :value="old('email', $supplier?->email)" @class(['mt-2', 'border-red-500' => $errors->has('email'), 'border-[#dadce0]' => ! $errors->has('email')]) />
                @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('supplier.phone') }}
                <x-ui.input name="phone" :value="old('phone', $supplier?->phone)" @class(['mt-2', 'border-red-500' => $errors->has('phone'), 'border-[#dadce0]' => ! $errors->has('phone')]) />
                @error('phone')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('supplier.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="ACTIVE" @selected(old('status', $supplier?->status ?? 'ACTIVE') === 'ACTIVE')>{{ __('supplier.active') }}</option>
                        <option value="INACTIVE" @selected(old('status', $supplier?->status ?? 'ACTIVE') === 'INACTIVE')>{{ __('supplier.inactive') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('supplier.default_lead_time_days') }}
                    <x-ui.input name="default_lead_time_days" type="number" min="0" :value="old('default_lead_time_days', $supplier?->default_lead_time_days ?? 0)" @class(['mt-2', 'border-red-500' => $errors->has('default_lead_time_days'), 'border-[#dadce0]' => ! $errors->has('default_lead_time_days')]) />
                    @error('default_lead_time_days')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('supplier.payment_terms') }}
                <x-ui.input name="payment_terms" :value="old('payment_terms', $supplier?->payment_terms)" @class(['mt-2', 'border-red-500' => $errors->has('payment_terms'), 'border-[#dadce0]' => ! $errors->has('payment_terms')]) />
                @error('payment_terms')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.suppliers.show', $supplier) : route('purchasing.suppliers.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('supplier.save') : __('supplier.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
