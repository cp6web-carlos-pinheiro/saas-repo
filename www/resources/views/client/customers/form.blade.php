@extends('layouts.client-area')

@php($editing = $customer !== null)

@section('title', __('ui.module_customers'))
@section('client-page-title', $editing ? __('customer.edit') : __('customer.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('customer.edit') : __('customer.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('customers.show', $customer) : route('customers.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('customers.update', $customer) : route('customers.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('customer.name') }}
                <x-ui.input name="name" :value="old('name', $customer?->name)" required @class(['mt-2', 'border-red-500' => $errors->has('name'), 'border-[#dadce0]' => ! $errors->has('name')]) />
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('customer.person_type') }}
                    <x-ui.select id="customer-person-type" name="person_type" class="mt-2" required data-search="on">
                        <option value="PJ" @selected(old('person_type', $customer?->person_type ?? 'PJ') === 'PJ')>{{ __('customer.person_type_pj') }}</option>
                        <option value="PF" @selected(old('person_type', $customer?->person_type ?? 'PJ') === 'PF')>{{ __('customer.person_type_pf') }}</option>
                    </x-ui.select>
                    @error('person_type')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('customer.tax_id') }}
                    <x-ui.input name="tax_id" :value="old('tax_id', $customer?->tax_id)" inputmode="numeric" data-tax-id-mask="true" data-person-type-source="customer-person-type" @class(['mt-2', 'border-red-500' => $errors->has('tax_id'), 'border-[#dadce0]' => ! $errors->has('tax_id')]) />
                    @error('tax_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('customer.email') }}
                <x-ui.input name="email" type="email" :value="old('email', $customer?->email)" @class(['mt-2', 'border-red-500' => $errors->has('email'), 'border-[#dadce0]' => ! $errors->has('email')]) />
                @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('customer.phone') }}
                <x-ui.input name="phone" :value="old('phone', $customer?->phone)" @class(['mt-2', 'border-red-500' => $errors->has('phone'), 'border-[#dadce0]' => ! $errors->has('phone')]) />
                @error('phone')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('customer.status') }}
                <x-ui.select name="status" class="mt-2" required data-search="off">
                    <option value="ACTIVE" @selected(old('status', $customer?->status ?? 'ACTIVE') === 'ACTIVE')>{{ __('customer.active') }}</option>
                    <option value="INACTIVE" @selected(old('status', $customer?->status ?? 'ACTIVE') === 'INACTIVE')>{{ __('customer.inactive') }}</option>
                </x-ui.select>
                @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('customer.default_cfop_id') }}
                    <x-ui.select name="default_cfop_id" class="mt-2" data-search="on">
                        <option value="">{{ __('customer.select_default_cfop') }}</option>
                        @foreach ($cfops as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('default_cfop_id', $customer?->default_cfop_id) === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('default_cfop_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('customer.tax_profile_id') }}
                    <x-ui.select name="tax_profile_id" class="mt-2" data-search="on">
                        <option value="">{{ __('customer.select_tax_profile') }}</option>
                        @foreach ($taxProfiles as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('tax_profile_id', $customer?->tax_profile_id) === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('tax_profile_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('customers.show', $customer) : route('customers.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('customer.save') : __('customer.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
