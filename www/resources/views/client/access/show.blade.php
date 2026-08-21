@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.manage_accesses'))
@section('client-page-title', __('company_access.details'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $customer->name }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('company-access.users.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('company-access.users.edit', $customer)" variant="primary" class="rounded-full">{{ __('company_access.edit') }}</x-ui.button>

            <x-ui.confirm-button :action="route('company-access.users.destroy', $customer)" method="DELETE" class="rounded-full" :label="__('company_access.remove')" :confirm-title="__('company_access.confirm_delete_title')" :confirm-text="__('company_access.confirm_delete_text', ['name' => $customer->name])" :confirm-label="__('company_access.confirm_delete_confirm')" :cancel-label="__('company_access.confirm_delete_cancel')" />
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

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
