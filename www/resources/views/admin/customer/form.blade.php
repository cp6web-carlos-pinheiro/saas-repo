@extends('layouts.global-admin')
@php($editing = $customer !== null)
@php($hasCompanyContext = ! $editing && $contextCompany !== null)
@section('title', ($editing ? __('global_customer.edit') : __('global_customer.create')).' | '.__('global_customer.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_customer.title'), 'href' => route('global-admin.customers.index')], ['label' => $editing ? __('global_customer.edit') : __('global_customer.create')]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('global_customer.edit') : __('global_customer.create') }}</h1>

        @if ($errors->any())
            <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.customers.update', $customer) : route('global-admin.customers.store') }}" class="mt-6 space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            @if ($hasCompanyContext)
                <x-ui.alert variant="info">
                    {{ __('global_customer.company_context_label') }}: {{ $contextCompany->name }} | CNPJ: {{ $contextCompany->code ?? __('global_customer.company_cnpj_uninformed') }}
                </x-ui.alert>
                <input type="hidden" name="company_id" value="{{ $contextCompany->id }}">
            @endif

            <label class="block text-sm font-medium">
                {{ __('global_customer.name') }}
                <input name="name" value="{{ old('name', $customer?->name) }}" required @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('name'), 'border-[#dadce0]' => ! $errors->has('name')])>
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_customer.email') }}
                <input name="email" type="email" value="{{ old('email', $customer?->email) }}" required @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('email'), 'border-[#dadce0]' => ! $errors->has('email')])>
                @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ $editing ? __('global_customer.new_password') : __('global_customer.password') }}
                <input name="password" type="password" @required(! $editing) @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('password'), 'border-[#dadce0]' => ! $errors->has('password')])>
                @error('password')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_customer.password_confirmation') }}
                <input name="password_confirmation" type="password" @required(! $editing) class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3">
            </label>

            @if ($editing)
                <label class="flex items-center gap-2 text-sm">
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $customer->is_active))>
                    {{ __('global_customer.active') }}
                </label>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.customers.show', $customer) : ($hasCompanyContext ? route('global-admin.companies.show', $contextCompany) : route('global-admin.customers.index'))" variant="surface-muted" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="brand-primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_customer.save') : __('global_customer.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
