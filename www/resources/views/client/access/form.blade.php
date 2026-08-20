@extends('layouts.client-area')

@php($editing = $customer !== null)
@php($selectedRole = (int) old('role_id', $selectedRoleId))

@section('title', __('ui.module_users').' | '.__('ui.manage_accesses'))
@section('client-page-title', $editing ? __('company_access.edit') : __('company_access.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('company_access.edit') : __('company_access.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('company-access.users.show', $customer) : route('company-access.users.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('company-access.users.update', $customer) : route('company-access.users.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('company_access.name') }}
                <x-ui.input name="name" :value="old('name', $customer?->name)" required @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('name'), 'border-[var(--ui-border)]' => ! $errors->has('name')]) />
                @error('name')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('company_access.email') }}
                <x-ui.input name="email" type="email" :value="old('email', $customer?->email)" required @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('email'), 'border-[var(--ui-border)]' => ! $errors->has('email')]) />
                @error('email')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ $editing ? __('company_access.new_password') : __('company_access.password') }}
                <x-ui.input name="password" type="password" :required="! $editing" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('password'), 'border-[var(--ui-border)]' => ! $errors->has('password')]) />
                @error('password')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('company_access.password_confirmation') }}
                <x-ui.input name="password_confirmation" type="password" :required="! $editing" class="mt-2" />
            </label>

            <fieldset class="rounded-2xl border border-[var(--ui-border)] p-5">
                <legend class="px-2 font-semibold">{{ __('company_access.access_section') }}</legend>

                @if ($mustBeAdministrator)
                    <x-ui.alert class="mt-2" variant="info">{{ __('company_access.first_user_administrator') }}</x-ui.alert>
                    <x-ui.input type="hidden" name="role_id" :value="$administratorRoleId" unstyled />
                @else
                    <label class="mt-2 block text-sm font-medium">
                        {{ __('company_access.access_role') }}
                        <x-ui.select name="role_id" required class="mt-2" data-search="on">
                            @foreach ($assignableRoles as $role)
                                <option value="{{ $role->id }}" @selected($selectedRole === (int) $role->id)>{{ $role->name }} ({{ $role->slug }})</option>
                            @endforeach
                        </x-ui.select>
                    </label>
                    <p class="mt-2 text-sm text-[var(--ui-text-muted)]">{{ __('company_access.access_role_help') }}</p>
                    @error('role_id')<span class="mt-2 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                @endif
            </fieldset>

            @if ($editing)
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface)] px-4 py-3">
                    <x-ui.input type="hidden" name="is_active" value="0" unstyled />
                    <x-ui.checkbox name="is_active" value="1" :checked="(bool) old('is_active', $customer->is_active)">{{ __('company_access.active') }}</x-ui.checkbox>
                </div>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('company-access.users.show', $customer) : route('company-access.users.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('company_access.save') : __('company_access.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
