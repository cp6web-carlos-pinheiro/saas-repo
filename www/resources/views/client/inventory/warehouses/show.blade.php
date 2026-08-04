@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouses'))
@section('client-page-title', __('warehouse.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $warehouse->name }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('inventory.warehouses.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('inventory.warehouses.edit', $warehouse)" variant="material-edit" class="rounded-full">{{ __('warehouse.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('inventory.warehouses.destroy', $warehouse) }}" data-admin-delete-confirm data-admin-name="{{ $warehouse->name }}" data-confirm-title="{{ __('warehouse.confirm_delete_title') }}" data-confirm-text="{{ __('warehouse.confirm_delete_text') }}" data-confirm-confirm="{{ __('warehouse.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('warehouse.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('warehouse.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
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
