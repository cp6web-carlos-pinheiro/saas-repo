@extends('layouts.global-admin')
@php($editing = $customer !== null)
@php($hasCompanyContext = $contextCompany !== null)
@php($selectedCompanyId = old('company_id', $contextCompany?->id ?? $customer?->current_company_id))
@php($selectedProfile = old('access_profile', $accessProfile))
@php($selectedModuleNames = old('modules', $selectedModules))
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
                <x-ui.input type="hidden" name="company_id" :value="$contextCompany->id" unstyled />
                <x-ui.input type="hidden" name="return_to_company_id" :value="$contextCompany->id" unstyled />
            @else
                <label class="block text-sm font-medium">
                    {{ __('global_customer.company') }}
                    <x-ui.select name="company_id" required data-search="on" @class(['mt-2', 'border-red-500' => $errors->has('company_id'), 'border-[#dadce0]' => ! $errors->has('company_id')])>
                        <option value="">{{ __('global_customer.select_company') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((int) $selectedCompanyId === $company->id)>{{ $company->name }} ({{ $company->code }})</option>
                        @endforeach
                    </x-ui.select>
                    @error('company_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            @endif

            <label class="block text-sm font-medium">
                {{ __('global_customer.name') }}
                <x-ui.input name="name" :value="old('name', $customer?->name)" required @class(['mt-2', 'border-red-500' => $errors->has('name'), 'border-[#dadce0]' => ! $errors->has('name')]) />
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_customer.email') }}
                <x-ui.input name="email" type="email" :value="old('email', $customer?->email)" required @class(['mt-2', 'border-red-500' => $errors->has('email'), 'border-[#dadce0]' => ! $errors->has('email')]) />
                @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ $editing ? __('global_customer.new_password') : __('global_customer.password') }}
                <x-ui.input name="password" type="password" :required="! $editing" @class(['mt-2', 'border-red-500' => $errors->has('password'), 'border-[#dadce0]' => ! $errors->has('password')]) />
                @error('password')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_customer.password_confirmation') }}
                <x-ui.input name="password_confirmation" type="password" :required="! $editing" class="mt-2" />
            </label>

            <fieldset class="rounded-2xl border border-[#dadce0] p-5">
                <legend class="px-2 font-semibold">{{ __('global_customer.access_section') }}</legend>

                @if ($mustBeAdministrator)
                    <x-ui.alert class="mt-2" variant="info">{{ __('global_customer.first_user_administrator') }}</x-ui.alert>
                    <x-ui.input type="hidden" name="access_profile" value="administrator" unstyled />
                @else
                    <label class="mt-2 block text-sm font-medium">
                        {{ __('global_customer.access_profile') }}
                        <x-ui.select name="access_profile" required class="mt-2" data-search="off">
                            <option value="administrator" @selected($selectedProfile === 'administrator')>{{ __('global_customer.profile_administrator') }}</option>
                            <option value="custom" @selected($selectedProfile === 'custom')>{{ __('global_customer.profile_custom') }}</option>
                        </x-ui.select>
                    </label>
                    <p class="mt-2 text-sm text-[#5f6368]">{{ __('global_customer.access_profile_help') }}</p>
                @endif

                <div class="mt-5">
                    <p class="text-sm font-medium">{{ __('global_customer.modules') }}</p>
                    <p class="mt-1 text-sm text-[#5f6368]">{{ __('global_customer.modules_help') }}</p>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($modules as $module => $permissions)
                            @php($moduleIsSelected = $mustBeAdministrator || $selectedProfile === 'administrator' || in_array($module, (array) $selectedModuleNames, true))
                            <label class="flex items-start gap-3 rounded-xl border border-[#dadce0] p-4">
                                <input type="checkbox" name="modules[]" value="{{ $module }}" @checked($moduleIsSelected) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
                                <span>
                                    <span class="block font-medium">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel((string) $module) }}</span>
                                    @php($moduleDescription = \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleDescription((string) $module))
                                    @if ($moduleDescription !== '')
                                        <span class="mt-1 block text-xs text-[#5f6368]">{{ $moduleDescription }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('modules')<span class="mt-2 block text-sm text-red-700">{{ $message }}</span>@enderror
                </div>
            </fieldset>

            @if ($editing)
                <label class="flex items-center gap-2 rounded-xl border border-[#dadce0] bg-white px-4 py-3 text-sm text-[#202124]">
                    <input type="hidden" name="is_active" value="0" />
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $customer->is_active)) class="h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
                    <span>{{ __('global_customer.active') }}</span>
                </label>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$hasCompanyContext ? route('global-admin.companies.show', $contextCompany) : ($editing ? route('global-admin.customers.show', $customer) : route('global-admin.customers.index'))" variant="surface-muted" class="rounded-full" :full="true">
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
