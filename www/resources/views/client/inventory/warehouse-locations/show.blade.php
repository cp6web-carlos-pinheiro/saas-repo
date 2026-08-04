@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouse_locations'))
@section('client-page-title', __('warehouse_location.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $location->name }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('inventory.warehouse-locations.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('inventory.warehouse-locations.edit', $location)" variant="material-edit" class="rounded-full">{{ __('warehouse_location.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('inventory.warehouse-locations.destroy', $location) }}" data-admin-delete-confirm data-admin-name="{{ $location->name }}" data-confirm-title="{{ __('warehouse_location.confirm_delete_title') }}" data-confirm-text="{{ __('warehouse_location.confirm_delete_text') }}" data-confirm-confirm="{{ __('warehouse_location.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('warehouse_location.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('warehouse_location.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('warehouse_location.reference')">#{{ $location->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('warehouse_location.name')">{{ $location->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('warehouse_location.code')">{{ $location->code }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('warehouse_location.warehouse')">{{ $location->warehouse?->code }} - {{ $location->warehouse?->name }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('warehouse_location.status')" :value="$location->is_active ? __('warehouse_location.active') : __('warehouse_location.inactive')" :tone="$location->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date :label="__('warehouse_location.created_at')" :value="$location->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
