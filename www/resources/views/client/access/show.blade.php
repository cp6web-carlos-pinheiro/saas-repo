@extends('layouts.client-area')

@section('title', $customer->name.' | '.__('company_access.title'))
@section('client-page-title', __('company_access.details'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $customer->name }}</h1>
        </div>
        <span class="rounded-full px-3 py-1 text-xs {{ $customer->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $customer->is_active ? __('company_access.active') : __('company_access.inactive') }}
        </span>
    </div>

    @if ($errors->has('customer'))
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first('customer') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <dl class="divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('company_access.email') }}</dt>
                <dd class="font-medium">{{ $customer->email }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('company_access.access_role') }}</dt>
                <dd class="font-medium">{{ $companyAccess['role_name'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('rbac.role_slug') }}</dt>
                <dd class="font-medium">{{ $companyAccess['role_slug'] ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('company_access.created_at') }}</dt>
                <dd class="font-medium">{{ $customer->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('company-access.users.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('company-access.users.edit', $customer)" variant="brand-primary" class="rounded-full">{{ __('company_access.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('company-access.users.destroy', $customer) }}" data-admin-delete-confirm data-admin-name="{{ $customer->name }}" data-confirm-title="{{ __('company_access.confirm_delete_title') }}" data-confirm-text="{{ __('company_access.confirm_delete_text') }}" data-confirm-confirm="{{ __('company_access.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('company_access.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('company_access.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
