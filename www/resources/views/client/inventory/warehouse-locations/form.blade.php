@extends('layouts.client-area')

@php($editing = $location !== null)

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouse_locations'))
@section('client-page-title', $editing ? __('warehouse_location.edit') : __('warehouse_location.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('warehouse_location.edit') : __('warehouse_location.create') }}</h1>
        <x-ui.button :href="$editing ? route('inventory.warehouse-locations.show', $location) : route('inventory.warehouse-locations.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('inventory.warehouse-locations.update', $location) : route('inventory.warehouse-locations.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('warehouse_location.name') }}
                    <x-ui.input name="name" :value="old('name', $location?->name)" class="mt-2" required />
                    @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('warehouse_location.code') }}
                    <x-ui.input name="code" :value="old('code', $location?->code)" class="mt-2" required />
                    @error('code')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('warehouse_location.warehouse') }}
                    <x-ui.select name="warehouse_id" class="mt-2" required data-search="on">
                        <option value="">{{ __('warehouse_location.select_warehouse') }}</option>
                        @foreach ($warehouses as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('warehouse_id', $location?->warehouse_id) === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('warehouse_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('warehouse_location.status') }}
                    <x-ui.select name="is_active" class="mt-2" required data-search="off">
                        <option value="1" @selected((string) old('is_active', $location?->is_active ?? true) === '1')>{{ __('warehouse_location.active') }}</option>
                        <option value="0" @selected((string) old('is_active', $location?->is_active ?? true) === '0')>{{ __('warehouse_location.inactive') }}</option>
                    </x-ui.select>
                    @error('is_active')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('inventory.warehouse-locations.show', $location) : route('inventory.warehouse-locations.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('warehouse_location.save') : __('warehouse_location.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
