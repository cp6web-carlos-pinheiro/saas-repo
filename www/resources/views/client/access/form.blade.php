@extends('layouts.google')

@php($editing = $customer !== null)
@php($selectedProfile = old('access_profile', $accessProfile))
@php($selectedModuleNames = old('modules', $selectedModules))

@section('title', ($editing ? __('company_access.edit') : __('company_access.create')).' | '.__('ui.app_name'))
@section('bodyClass', 'min-h-screen bg-[#f8fafd] text-[#202124]')

@section('content')
<div class="mx-auto w-full max-w-4xl p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-[#5f6368]">{{ __('company_access.company_context') }}: {{ $company->name }}</p>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('company_access.edit') : __('company_access.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('company-access.users.show', $customer) : route('company-access.users.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('company-access.users.update', $customer) : route('company-access.users.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('company_access.name') }}
                <input name="name" value="{{ old('name', $customer?->name) }}" required @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('name'), 'border-[#dadce0]' => ! $errors->has('name')])>
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('company_access.email') }}
                <input name="email" type="email" value="{{ old('email', $customer?->email) }}" required @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('email'), 'border-[#dadce0]' => ! $errors->has('email')])>
                @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ $editing ? __('company_access.new_password') : __('company_access.password') }}
                <input name="password" type="password" @required(! $editing) @class(['mt-2 w-full rounded-xl border px-4 py-3', 'border-red-500' => $errors->has('password'), 'border-[#dadce0]' => ! $errors->has('password')])>
                @error('password')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('company_access.password_confirmation') }}
                <input name="password_confirmation" type="password" @required(! $editing) class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3">
            </label>

            <fieldset class="rounded-2xl border border-[#dadce0] p-5">
                <legend class="px-2 font-semibold">{{ __('company_access.access_section') }}</legend>

                @if ($mustBeAdministrator)
                    <x-ui.alert class="mt-2" variant="info">{{ __('company_access.first_user_administrator') }}</x-ui.alert>
                    <input type="hidden" name="access_profile" value="administrator">
                @else
                    <label class="mt-2 block text-sm font-medium">
                        {{ __('company_access.access_profile') }}
                        <select name="access_profile" required class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3">
                            <option value="administrator" @selected($selectedProfile === 'administrator')>{{ __('company_access.profile_administrator') }}</option>
                            <option value="custom" @selected($selectedProfile === 'custom')>{{ __('company_access.profile_custom') }}</option>
                        </select>
                    </label>
                    <p class="mt-2 text-sm text-[#5f6368]">{{ __('company_access.access_profile_help') }}</p>
                @endif

                <div class="mt-5">
                    <p class="text-sm font-medium">{{ __('company_access.modules') }}</p>
                    <p class="mt-1 text-sm text-[#5f6368]">{{ __('company_access.modules_help') }}</p>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($modules as $module => $permissions)
                            @php($moduleIsSelected = $mustBeAdministrator || $selectedProfile === 'administrator' || in_array($module, (array) $selectedModuleNames, true))
                            <label class="flex items-start gap-3 rounded-xl border border-[#dadce0] p-4">
                                <input name="modules[]" type="checkbox" value="{{ $module }}" @checked($moduleIsSelected) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35">
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
                <label class="flex items-center gap-2 text-sm">
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $customer->is_active))>
                    {{ __('company_access.active') }}
                </label>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('company-access.users.show', $customer) : route('company-access.users.index')" variant="surface-muted" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('company_access.save') : __('company_access.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
