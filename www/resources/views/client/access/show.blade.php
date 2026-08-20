@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.manage_accesses'))
@section('client-page-title', __('company_access.details'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $customer->name }}</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('company-access.users.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('company-access.users.edit', $customer)" variant="primary" class="rounded-full">{{ __('company_access.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('company-access.users.destroy', $customer) }}" data-admin-delete-confirm data-admin-name="{{ $customer->name }}" data-confirm-title="{{ __('company_access.confirm_delete_title') }}" data-confirm-text="{{ __('company_access.confirm_delete_text') }}" data-confirm-confirm="{{ __('company_access.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('company_access.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('company_access.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    @if ($errors->has('customer'))
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first('customer') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('company_access.email')">{{ $customer->email }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('company_access.access_role')">{{ $companyAccess['role_name'] ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('rbac.role_slug')">{{ $companyAccess['role_slug'] ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('company_access.status')" :value="$customer->is_active ? __('company_access.active') : __('company_access.inactive')" :tone="$customer->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date class="sm:col-span-2 xl:col-span-1" :label="__('company_access.created_at')" :value="$customer->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
