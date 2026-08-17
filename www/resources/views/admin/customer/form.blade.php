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
    <x-ui.page-heading
        :title="$editing ? __('global_customer.edit') : __('global_customer.create')"
        :breadcrumbs="[['label' => __('global_customer.title'), 'href' => route('global-admin.customers.index')], ['label' => $editing ? __('global_customer.edit') : __('global_customer.create')]]"
    />

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        @if ($errors->any())
            <x-ui.alert class="mb-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.customers.update', $customer) : route('global-admin.customers.store') }}" class="space-y-5">
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
                <x-ui.field :label="__('global_customer.company')" for="company_id" required :error="$errors->first('company_id')">
                    <x-ui.select id="company_id" name="company_id" required data-search="on">
                        <option value="">{{ __('global_customer.select_company') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((int) $selectedCompanyId === $company->id)>{{ $company->name }} ({{ $company->code }})</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            @endif

            <x-ui.field :label="__('global_customer.name')" for="name" required :error="$errors->first('name')">
                <x-ui.input id="name" name="name" :value="old('name', $customer?->name)" required />
            </x-ui.field>

            <x-ui.field :label="__('global_customer.email')" for="email" required :error="$errors->first('email')">
                <x-ui.input id="email" name="email" type="email" :value="old('email', $customer?->email)" required />
            </x-ui.field>

            <x-ui.field :label="$editing ? __('global_customer.new_password') : __('global_customer.password')" for="password" :required="! $editing" :error="$errors->first('password')">
                <x-ui.input id="password" name="password" type="password" :required="! $editing" />
            </x-ui.field>

            <x-ui.field :label="__('global_customer.password_confirmation')" for="password_confirmation">
                <x-ui.input id="password_confirmation" name="password_confirmation" type="password" :required="! $editing" />
            </x-ui.field>

            <fieldset class="rounded-2xl border border-[var(--ui-border)] p-5">
                <legend class="px-2 font-semibold text-[var(--ui-text)]">{{ __('global_customer.access_section') }}</legend>

                @if ($mustBeAdministrator)
                    <x-ui.alert class="mt-2" variant="info">{{ __('global_customer.first_user_administrator') }}</x-ui.alert>
                    <x-ui.input type="hidden" name="access_profile" value="administrator" unstyled />
                @else
                    <x-ui.field class="mt-2" :label="__('global_customer.access_profile')" for="access_profile" required :hint="__('global_customer.access_profile_help')">
                        <x-ui.select id="access_profile" name="access_profile" required data-search="off">
                            <option value="administrator" @selected($selectedProfile === 'administrator')>{{ __('global_customer.profile_administrator') }}</option>
                            <option value="custom" @selected($selectedProfile === 'custom')>{{ __('global_customer.profile_custom') }}</option>
                        </x-ui.select>
                    </x-ui.field>
                @endif

                <div class="mt-5">
                    <p class="text-sm font-medium text-[var(--ui-text)]">{{ __('global_customer.modules') }}</p>
                    <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ __('global_customer.modules_help') }}</p>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($modules as $module => $permissions)
                            @php($moduleIsSelected = $mustBeAdministrator || $selectedProfile === 'administrator' || in_array($module, (array) $selectedModuleNames, true))
                            <x-ui.checkbox name="modules[]" :value="$module" :checked="$moduleIsSelected" class="mt-1" :description="\App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleDescription((string) $module) ?: null">
                                {{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel((string) $module) }}
                            </x-ui.checkbox>
                        @endforeach
                    </div>
                    @error('modules')<p class="mt-2 text-sm text-[var(--ui-danger)]">{{ $message }}</p>@enderror
                </div>
            </fieldset>

            @if ($editing)
                <x-ui.checkbox name="is_active" value="1" :checked="old('is_active', $customer->is_active)">{{ __('global_customer.active') }}</x-ui.checkbox>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$hasCompanyContext ? route('global-admin.companies.show', $contextCompany) : ($editing ? route('global-admin.customers.show', $customer) : route('global-admin.customers.index'))" variant="secondary" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_customer.save') : __('global_customer.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection