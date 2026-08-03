@extends('layouts.client-area')

@section('title', $role->name.' | '.__('rbac.title'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
@php($isAdministratorRole = app(\App\Services\SaaS\CompanyUserAccessService::class)->isAdministratorRoleSlug((string) $role->slug))
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $role->name }}</h1>
            <p class="mt-1 text-sm text-[#5f6368]">{{ $role->slug }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('company-access.rbac.roles.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            @unless($isAdministratorRole)
                <x-ui.button :href="route('company-access.rbac.roles.edit', $role)" variant="brand-primary" class="rounded-full">{{ __('rbac.update') }}</x-ui.button>
            @endunless
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->has('role'))
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first('role') }}</x-ui.alert>
    @endif

    @if ($isAdministratorRole)
        <x-ui.alert class="mt-5" variant="info">{{ __('rbac.master_role_locked') }}</x-ui.alert>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.2fr,1fr]">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6 md:p-8">
            <h2 class="text-lg font-semibold">{{ __('rbac.role_permissions') }}</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($role->permissions->groupBy('module') as $module => $permissions)
                    <div class="rounded-xl border border-[#dadce0] p-4">
                        <p class="text-sm font-semibold">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-[#5f6368]">
                            @foreach ($permissions as $permission)
                                <li>{{ $permission->label() }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @unless($isAdministratorRole)
                <form method="POST" action="{{ route('company-access.rbac.roles.destroy', $role) }}" class="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('rbac.delete') }}</x-ui.button>
                </form>
            @endunless
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6 md:p-8">
            <h2 class="text-lg font-semibold">{{ __('rbac.role_users') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($role->users as $customer)
                    <div class="rounded-xl border border-[#dadce0] p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $customer->name }}</p>
                                <p class="mt-1 text-xs text-[#5f6368]">{{ $customer->email }}</p>
                            </div>
                            <x-ui.button :href="route('company-access.users.edit', $customer)" variant="surface-muted" class="rounded-full text-xs">{{ __('company_access.edit') }}</x-ui.button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#5f6368]">{{ __('rbac.empty_roles') }}</p>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</div>
@endsection
