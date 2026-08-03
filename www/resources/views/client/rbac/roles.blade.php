@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.rbac_roles'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('ui.rbac_roles') }}</h1>
            <p class="mt-2 text-sm text-[#5f6368]">{{ __('rbac.role_permissions') }} + {{ __('rbac.role_users') }}</p>
        </div>
        <x-ui.button :href="route('company-access.rbac.roles.create')" variant="brand-primary" class="rounded-full">{{ __('rbac.new_role') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">{{ __('rbac.role_name') }}</th>
                        <th class="px-3 py-3">{{ __('rbac.role_slug') }}</th>
                        <th class="px-3 py-3">{{ __('rbac.permissions_count') }}</th>
                        <th class="px-3 py-3">{{ __('rbac.users_count') }}</th>
                        <th class="px-3 py-3">{{ __('rbac.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        @php($isAdministratorRole = app(\App\Services\SaaS\CompanyUserAccessService::class)->isAdministratorRoleSlug((string) $role->slug))
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-4 font-semibold">{{ $role->name }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $role->slug }}</td>
                            <td class="px-3 py-4">{{ $role->permissions_count }}</td>
                            <td class="px-3 py-4">{{ $role->users_count }}</td>
                            <td class="px-3 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button :href="route('company-access.rbac.roles.show', $role)" variant="surface-muted" class="rounded-full text-xs">{{ __('rbac.details') }}</x-ui.button>
                                    @if ($isAdministratorRole)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ __('rbac.master_role_locked') }}</span>
                                    @else
                                        <x-ui.button :href="route('company-access.rbac.roles.edit', $role)" variant="material-edit" class="rounded-full text-xs">{{ __('rbac.update') }}</x-ui.button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[#5f6368]">{{ __('rbac.empty_roles') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $roles->links() }}</div>
    </x-ui.panel>
</div>
@endsection
