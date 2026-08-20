@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.rbac_roles'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
@php($isAdministratorRole = app(\App\Services\SaaS\CompanyUserAccessService::class)->isAdministratorRoleSlug((string) $role->slug))
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $role->name }}" subtitle="{{ $role->slug }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('company-access.rbac.roles.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            @unless($isAdministratorRole)
                <x-ui.button :href="route('company-access.rbac.roles.edit', $role)" variant="primary" class="rounded-full">{{ __('rbac.update') }}</x-ui.button>
                <form method="POST" action="{{ route('company-access.rbac.roles.destroy', $role) }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('rbac.delete') }}</x-ui.button>
                </form>
            @endunless
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

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
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
            <h2 class="text-lg font-semibold">{{ __('rbac.role_permissions') }}</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($role->permissions->groupBy('module') as $module => $permissions)
                    <x-ui.definition-item-list
                        :label="\App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module)"
                        :items="$permissions->map(static fn ($permission) => $permission->label())->values()->all()"
                    />
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
            <h2 class="text-lg font-semibold">{{ __('rbac.role_users') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($role->users as $customer)
                    <div class="rounded-xl border border-[var(--ui-border)] p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $customer->name }}</p>
                                <p class="mt-1 text-xs text-[var(--ui-text-muted)]">{{ $customer->email }}</p>
                            </div>
                            <x-ui.button :href="route('company-access.users.edit', $customer)" variant="primary" class="rounded-full text-xs">{{ __('company_access.edit') }}</x-ui.button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[var(--ui-text-muted)]">{{ __('rbac.empty_roles') }}</p>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</div>
@endsection
