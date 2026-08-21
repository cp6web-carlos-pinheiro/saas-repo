@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_suppliers'))
@section('client-page-title', __('supplier.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $supplier->name }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.suppliers.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.suppliers.edit', $supplier)" variant="primary" class="rounded-full">{{ __('supplier.edit') }}</x-ui.button>

            <x-ui.confirm-button :action="route('purchasing.suppliers.destroy', $supplier)" method="DELETE" class="rounded-full" :label="__('supplier.remove')" :confirm-title="__('supplier.confirm_delete_title')" :confirm-text="__('supplier.confirm_delete_text', ['name' => $supplier->name])" :confirm-label="__('supplier.confirm_delete_confirm')" :cancel-label="__('supplier.confirm_delete_cancel')" />
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('supplier.person_type')">{{ $supplier->person_type === 'PF' ? __('supplier.person_type_pf') : __('supplier.person_type_pj') }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('supplier.email')">{{ $supplier->email ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('supplier.phone')">{{ $supplier->phone ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('supplier.status')" :value="$supplier->status === 'ACTIVE' ? __('supplier.active') : __('supplier.inactive')" :tone="$supplier->status === 'ACTIVE' ? 'success' : 'neutral'" />
            <x-ui.definition-item :label="__('supplier.default_lead_time_days')">{{ $supplier->default_lead_time_days }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('supplier.payment_terms')">{{ $supplier->payment_terms ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date class="sm:col-span-2 xl:col-span-1" :label="__('supplier.created_at')" :value="$supplier->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
