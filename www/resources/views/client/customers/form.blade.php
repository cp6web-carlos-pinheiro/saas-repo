@extends('layouts.client-area')

@php($editing = $customer !== null)

@section('title', __('ui.module_customers'))
@section('client-page-title', $editing ? __('customer.edit') : __('customer.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $editing ? __('customer.edit') : __('customer.create') }}">
        <x-slot:actions>
        <x-ui.button :href="$editing ? route('customers.show', $customer) : route('customers.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('customers.update', $customer) : route('customers.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('customer.name') }}" for="name" :required="true" :error="$errors->first('name')">
                <x-ui.input name="name" :value="old('name', $customer?->name)" required @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('name'), 'border-[var(--ui-border)]' => ! $errors->has('name')])  id="name" :aria-describedby="$errors->has('name') ? 'name-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('customer.person_type') }}" for="customer-person-type" :required="true" :error="$errors->first('person_type')">
                <x-ui.select id="customer-person-type" name="person_type" class="mt-2" required data-search="on" :aria-describedby="$errors->has('person_type') ? 'customer-person-type-error' : null">
                    <option value="PJ" @selected(old('person_type', $customer?->person_type ?? 'PJ') === 'PJ')>{{ __('customer.person_type_pj') }}</option>
                    <option value="PF" @selected(old('person_type', $customer?->person_type ?? 'PJ') === 'PF')>{{ __('customer.person_type_pf') }}</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="{{ __('customer.email') }}" for="email" :error="$errors->first('email')">
                <x-ui.input name="email" type="email" :value="old('email', $customer?->email)" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('email'), 'border-[var(--ui-border)]' => ! $errors->has('email')])  id="email" :aria-describedby="$errors->has('email') ? 'email-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('customer.phone') }}" for="phone" :error="$errors->first('phone')">
                <x-ui.input name="phone" :value="old('phone', $customer?->phone)" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('phone'), 'border-[var(--ui-border)]' => ! $errors->has('phone')])  id="phone" :aria-describedby="$errors->has('phone') ? 'phone-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('customer.status') }}" for="status" :required="true" :error="$errors->first('status')">
                <x-ui.select name="status" class="mt-2" required data-search="off" id="status" :aria-describedby="$errors->has('status') ? 'status-error' : null">
                    <option value="ACTIVE" @selected(old('status', $customer?->status ?? 'ACTIVE') === 'ACTIVE')>{{ __('customer.active') }}</option>
                    <option value="INACTIVE" @selected(old('status', $customer?->status ?? 'ACTIVE') === 'INACTIVE')>{{ __('customer.inactive') }}</option>
                </x-ui.select>
            </x-ui.field>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('customers.show', $customer) : route('customers.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('customer.save') : __('customer.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
