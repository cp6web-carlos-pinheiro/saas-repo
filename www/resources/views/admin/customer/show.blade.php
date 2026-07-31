@extends('layouts.global-admin')
@section('title', $customer->name.' | '.__('global_customer.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_customer.title'), 'href' => route('global-admin.customers.index')], ['label' => $customer->name]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-[#5f6368]">{{ __('global_customer.details') }}</p>
                <h1 class="font-display text-3xl font-bold">{{ $customer->name }}</h1>
            </div>
            <span class="rounded-full px-3 py-1 text-xs {{ $customer->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $customer->is_active ? __('global_customer.active') : __('global_customer.inactive') }}
            </span>
        </div>

        <dl class="mt-8 divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.email') }}</dt>
                <dd class="font-medium">{{ $customer->email }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.company') }}</dt>
                <dd class="font-medium">{{ $customer->currentCompany?->name ?? __('global_customer.company_unlinked') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.company_id') }}</dt>
                <dd class="font-medium">{{ $customer->currentCompany?->id ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.created_at') }}</dt>
                <dd class="font-medium">{{ $customer->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 rounded-2xl border border-[#dadce0] bg-[#f8fafd] p-5">
            <h2 class="font-display text-xl font-semibold">{{ __('global_customer.company_details') }}</h2>

            @if ($customer->currentCompany)
                <dl class="mt-4 divide-y divide-[#dadce0]">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_id') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->id }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_name') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_code') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_status') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->is_active ? __('global_customer.active') : __('global_customer.inactive') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_created_at') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_updated_at') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 text-sm text-[#5f6368]">{{ __('global_customer.company_unlinked_details') }}</p>
            @endif
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.customers.index')" variant="surface-muted" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.customers.edit', $customer)" variant="brand-primary" class="rounded-full">
                {{ __('global_customer.edit') }}
            </x-ui.button>

            <form method="POST" action="{{ route('global-admin.customers.destroy', $customer) }}" data-admin-delete-confirm data-admin-name="{{ $customer->name }}" data-confirm-title="{{ __('global_customer.confirm_delete_title') }}" data-confirm-text="{{ __('global_customer.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_customer.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_customer.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('global_customer.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
