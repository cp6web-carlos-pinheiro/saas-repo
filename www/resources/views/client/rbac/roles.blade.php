@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.rbac_roles'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('ui.rbac_roles') }}</h1>
            <p class="mt-2 text-sm text-[var(--ui-text-muted)]">{{ __('rbac.role_permissions') }} + {{ __('rbac.role_users') }}</p>
        </div>
        <x-ui.button :href="route('company-access.rbac.roles.create')" variant="primary" class="rounded-full">{{ __('rbac.new_role') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <div class="overflow-x-auto">
            <x-ui.table :caption="__('rbac.roles')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="name" :label="__('rbac.role_name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="slug" :label="__('rbac.role_slug')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="permissions_count" :label="__('rbac.permissions_count')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="users_count" :label="__('rbac.users_count')" :sort="$sort" :direction="$direction" />
                        <th class="px-3 py-3">{{ __('rbac.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        @php($isAdministratorRole = app(\App\Services\SaaS\CompanyUserAccessService::class)->isAdministratorRoleSlug((string) $role->slug))
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-4 font-semibold">{{ $role->name }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $role->slug }}</td>
                            <td class="px-3 py-4">{{ $role->permissions_count }}</td>
                            <td class="px-3 py-4">{{ $role->users_count }}</td>
                            <td class="px-3 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button :href="route('company-access.rbac.roles.show', $role)" variant="secondary" class="rounded-full text-xs">{{ __('rbac.details') }}</x-ui.button>
                                    @if ($isAdministratorRole)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ __('rbac.master_role_locked') }}</span>
                                    @else
                                        <x-ui.button :href="route('company-access.rbac.roles.edit', $role)" variant="primary" class="rounded-full text-xs">{{ __('rbac.update') }}</x-ui.button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('rbac.empty_roles') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $roles->links() }}</div>
    </x-ui.panel>
</div>
@endsection
