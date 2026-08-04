@extends('layouts.client-area')

@php
    $editing = $requisition !== null;
    $productsById = $products->keyBy('id');
    $warehousesById = $warehouses->keyBy('id');
    $suppliersById = $suppliers->keyBy('id');
@endphp

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', $editing ? __('purchase_requisition.edit') : __('purchase_requisition.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_requisition.edit') : __('purchase_requisition.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.requisitions.show', $requisition) : route('purchasing.requisitions.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.requisitions.update', $requisition) : route('purchasing.requisitions.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_requisition.required_date') }}
                    <x-ui.input type="date" name="required_date" :value="old('required_date', $requisition?->required_date?->format('Y-m-d'))" class="mt-2" />
                    @error('required_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_requisition.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_requisition.status_draft') }}</option>
                        <option value="APPROVED" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_requisition.status_approved') }}</option>
                        <option value="CANCELLED" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_requisition.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_requisition.source_type') }}
                <x-ui.input name="source_type" :value="old('source_type', $requisition?->source_type ?? 'manual')" class="mt-2" />
                @error('source_type')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold">{{ __('purchase_requisition.items') }}</h2>
                    </div>
                    <button type="button" class="rounded-full border border-[#dadce0] px-4 py-2 text-sm font-medium" data-pr-add-item>{{ __('purchase_requisition.add_item') }}</button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[1100px]">
                        <div class="grid grid-cols-[2fr_1.5fr_1.5fr_1fr_1.2fr_1.2fr_auto] gap-4 border-b border-[#dadce0] pb-2 text-xs font-semibold uppercase tracking-wide text-[#5f6368]">
                            <span>{{ __('purchase_requisition.product') }}</span>
                            <span>{{ __('purchase_requisition.warehouse') }}</span>
                            <span>{{ __('purchase_requisition.supplier') }}</span>
                            <span>{{ __('purchase_requisition.quantity') }}</span>
                            <span>{{ __('purchase_requisition.need_by_date') }}</span>
                            <span>{{ __('purchase_requisition.order_date') }}</span>
                            <span class="sr-only">{{ __('purchase_requisition.remove_item') }}</span>
                        </div>

                        <div class="mt-3 space-y-3" data-pr-items-container>
                            @foreach (old('items', $lineRows) as $index => $item)
                                <div class="grid grid-cols-[2fr_1.5fr_1.5fr_1fr_1.2fr_1.2fr_auto] items-start gap-4" data-pr-item-row>
                                    <x-ui.select name="items[{{ $index }}][product_id]" required data-search="on" data-placeholder="{{ __('purchase_requisition.select_product') }}" data-ajax-url="{{ route('purchasing.lookups.products') }}" data-minimum-input-length="1">
                                        <option value="">{{ __('purchase_requisition.select_product') }}</option>
                                        @php($selectedProductId = (int) old('items.'.$index.'.product_id', $item['product_id'] ?? 0))
                                        @if ($selectedProductId > 0 && $productsById->has($selectedProductId))
                                            <option value="{{ $selectedProductId }}" selected>{{ $productsById[$selectedProductId]->sku }} - {{ $productsById[$selectedProductId]->description ?? '—' }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.select name="items[{{ $index }}][warehouse_id]" data-search="on">
                                        <option value="">{{ __('purchase_requisition.select_warehouse') }}</option>
                                        @php($selectedWarehouseId = (int) old('items.'.$index.'.warehouse_id', $item['warehouse_id'] ?? 0))
                                        @if ($selectedWarehouseId > 0 && $warehousesById->has($selectedWarehouseId))
                                            <option value="{{ $selectedWarehouseId }}" selected>{{ $warehousesById[$selectedWarehouseId]->code }} - {{ $warehousesById[$selectedWarehouseId]->name }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.select name="items[{{ $index }}][supplier_id]" data-search="on">
                                        <option value="">{{ __('purchase_requisition.select_supplier') }}</option>
                                        @php($selectedSupplierId = (int) old('items.'.$index.'.supplier_id', $item['supplier_id'] ?? 0))
                                        @if ($selectedSupplierId > 0 && $suppliersById->has($selectedSupplierId))
                                            <option value="{{ $selectedSupplierId }}" selected>{{ $suppliersById[$selectedSupplierId]->code }} - {{ $suppliersById[$selectedSupplierId]->name }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity]" :value="old('items.'.$index.'.quantity', $item['quantity'] ?? 1)" required />
                                    <x-ui.input type="date" name="items[{{ $index }}][need_by_date]" :value="old('items.'.$index.'.need_by_date', $item['need_by_date'] ?? now()->addDays(7)->toDateString())" required />
                                    <x-ui.input type="date" name="items[{{ $index }}][order_date]" :value="old('items.'.$index.'.order_date', $item['order_date'] ?? now()->toDateString())" required />

                                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-pr-remove-item aria-label="{{ __('purchase_requisition.remove_item') }}" title="{{ __('purchase_requisition.remove_item') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4h8v2" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <label class="block text-sm font-medium">
                {{ __('purchase_requisition.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $requisition?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.requisitions.show', $requisition) : route('purchasing.requisitions.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_requisition.save') : __('purchase_requisition.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="pr-item-template">
    <div class="grid grid-cols-[2fr_1.5fr_1.5fr_1fr_1.2fr_1.2fr_auto] items-start gap-4" data-pr-item-row>
        <x-ui.select name="items[__INDEX__][product_id]" required data-search="on" data-placeholder="{{ __('purchase_requisition.select_product') }}" data-ajax-url="{{ route('purchasing.lookups.products') }}" data-minimum-input-length="1">
            <option value="">{{ __('purchase_requisition.select_product') }}</option>
        </x-ui.select>
        <x-ui.select name="items[__INDEX__][warehouse_id]" data-search="on">
            <option value="">{{ __('purchase_requisition.select_warehouse') }}</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="items[__INDEX__][supplier_id]" data-search="on">
            <option value="">{{ __('purchase_requisition.select_supplier') }}</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity]" value="1" required />
        <x-ui.input type="date" name="items[__INDEX__][need_by_date]" value="{{ now()->addDays(7)->toDateString() }}" required />
        <x-ui.input type="date" name="items[__INDEX__][order_date]" value="{{ now()->toDateString() }}" required />
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-pr-remove-item aria-label="{{ __('purchase_requisition.remove_item') }}" title="{{ __('purchase_requisition.remove_item') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 6h18" />
                <path d="M8 6V4h8v2" />
                <path d="M19 6l-1 14H6L5 6" />
                <path d="M10 11v6" />
                <path d="M14 11v6" />
            </svg>
        </button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-pr-items-container]');
    const template = document.getElementById('pr-item-template');
    const addButton = document.querySelector('[data-pr-add-item]');

    if (!container || !template || !addButton) {
        return;
    }

    const bindRow = (row) => {
        const removeButton = row.querySelector('[data-pr-remove-item]');

        if (removeButton) {
            removeButton.addEventListener('click', () => {
                if (container.querySelectorAll('[data-pr-item-row]').length === 1) {
                    return;
                }

                row.remove();
            });
        }
    };

    container.querySelectorAll('[data-pr-item-row]').forEach(bindRow);

    addButton.addEventListener('click', () => {
        const index = container.querySelectorAll('[data-pr-item-row]').length;
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        if (!row) {
            return;
        }
        container.appendChild(row);
        bindRow(row);
    });
});
</script>
@endsection
