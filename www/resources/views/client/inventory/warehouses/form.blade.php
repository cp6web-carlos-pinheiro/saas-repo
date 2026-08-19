@extends('layouts.client-area')

@php($editing = $warehouse !== null)

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouses'))
@section('client-page-title', $editing ? __('warehouse.edit') : __('warehouse.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('warehouse.edit') : __('warehouse.create') }}</h1>
        <x-ui.button :href="$editing ? route('inventory.warehouses.show', $warehouse) : route('inventory.warehouses.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('inventory.warehouses.update', $warehouse) : route('inventory.warehouses.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('warehouse.name') }}
                <x-ui.input name="name" :value="old('name', $warehouse?->name)" class="mt-2" required />
                @error('name')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('warehouse.plant') }}
                    <x-ui.select name="plant_id" class="mt-2" required>
                        <option value="">{{ __('warehouse.select_plant') }}</option>
                        @foreach ($plants as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('plant_id', $warehouse?->plant_id) === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('plant_id')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('warehouse.status') }}
                    <x-ui.select name="is_active" class="mt-2" required data-search="off">
                        <option value="1" @selected((string) old('is_active', $warehouse?->is_active ?? true) === '1')>{{ __('warehouse.active') }}</option>
                        <option value="0" @selected((string) old('is_active', $warehouse?->is_active ?? true) === '0')>{{ __('warehouse.inactive') }}</option>
                    </x-ui.select>
                    @error('is_active')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('inventory.warehouses.show', $warehouse) : route('inventory.warehouses.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('warehouse.save') : __('warehouse.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
