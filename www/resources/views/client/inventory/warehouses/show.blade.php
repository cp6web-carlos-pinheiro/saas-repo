@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouses'))
@section('client-page-title', __('warehouse.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $warehouse->name }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('inventory.warehouses.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('inventory.warehouses.edit', $warehouse)" variant="primary" class="rounded-full">{{ __('warehouse.edit') }}</x-ui.button>
            <x-ui.confirm-button :action="route('inventory.warehouses.destroy', $warehouse)" method="DELETE" class="rounded-full" :label="__('warehouse.remove')" :confirm-title="__('warehouse.confirm_delete_title')" :confirm-text="__('warehouse.confirm_delete_text', ['name' => $warehouse->name])" :confirm-label="__('warehouse.confirm_delete_confirm')" :cancel-label="__('warehouse.confirm_delete_cancel')" />
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('warehouse.reference')">#{{ $warehouse->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('warehouse.name')">{{ $warehouse->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('warehouse.code')">{{ $warehouse->code }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('warehouse.plant')">{{ $warehouse->plant?->code }} - {{ $warehouse->plant?->name }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('warehouse.status')" :value="$warehouse->is_active ? __('warehouse.active') : __('warehouse.inactive')" :tone="$warehouse->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date :label="__('warehouse.created_at')" :value="$warehouse->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
