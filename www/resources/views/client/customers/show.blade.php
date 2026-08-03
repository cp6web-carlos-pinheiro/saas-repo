@extends('layouts.client-area')

@section('title', __('ui.module_customers'))
@section('client-page-title', __('customer.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $customer->name }}</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('customers.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('customers.edit', $customer)" variant="material-edit" class="rounded-full">{{ __('customer.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-admin-delete-confirm data-admin-name="{{ $customer->name }}" data-confirm-title="{{ __('customer.confirm_delete_title') }}" data-confirm-text="{{ __('customer.confirm_delete_text') }}" data-confirm-confirm="{{ __('customer.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('customer.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('customer.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('customer.person_type')">{{ $customer->person_type === 'PF' ? __('customer.person_type_pf') : __('customer.person_type_pj') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('customer.tax_id')">{{ $customer->tax_id ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('customer.email')">{{ $customer->email ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('customer.phone')">{{ $customer->phone ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('customer.status')" :value="$customer->status === 'ACTIVE' ? __('customer.active') : __('customer.inactive')" :tone="$customer->status === 'ACTIVE' ? 'success' : 'neutral'" />
            <x-ui.definition-item-date class="sm:col-span-2 xl:col-span-1" :label="__('customer.created_at')" :value="$customer->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
