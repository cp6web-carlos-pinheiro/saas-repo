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
        <x-ui.button :href="$editing ? route('purchasing.requisitions.show', $requisition) : route('purchasing.requisitions.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.requisitions.update', $requisition) : route('purchasing.requisitions.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif
            @if (! $editing && $creationContext)
                <x-ui.input type="hidden" name="source_reference_id" :value="$creationContext['sale_id']" unstyled />
                <x-ui.input type="hidden" name="source_reference_type" value="sale" unstyled />
                <x-ui.alert variant="info">{{ __('purchase_requisition.sale_context', ['sale' => $creationContext['sale_id']]) }}</x-ui.alert>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_requisition.required_date') }}
                    <x-ui.input type="date" name="required_date" :value="old('required_date', $requisition?->required_date?->format('Y-m-d') ?? $initialValues['required_date'])" class="mt-2" />
                    @error('required_date')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_requisition.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_requisition.status_draft') }}</option>
                        <option value="APPROVED" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_requisition.status_approved') }}</option>
                        <option value="CANCELLED" @selected(old('status', $requisition?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_requisition.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_requisition.source_type') }}
                <x-ui.input name="source_type" :value="old('source_type', $requisition?->source_type ?? $initialValues['source_type'])" class="mt-2" />
                @error('source_type')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold">{{ __('purchase_requisition.items') }}</h2>
                    </div>
                    <x-ui.button type="button" variant="outline" class="rounded-full" data-pr-add-item><x-ui.icon name="plus" size="sm" /> {{ __('purchase_requisition.add_item') }}</x-ui.button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[1100px]">
                        <div class="grid grid-cols-[2fr_1.5fr_1.5fr_1fr_1.2fr_1.2fr_auto] gap-4 border-b border-[var(--ui-border)] pb-2 text-xs font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">
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

                                    <x-ui.icon-button type="button" icon="trash" variant="danger" data-pr-remove-item :label="__('purchase_requisition.remove_item')" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <label class="block text-sm font-medium">
                {{ __('purchase_requisition.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $requisition?->notes ?? $initialValues['notes']) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.requisitions.show', $requisition) : route('purchasing.requisitions.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_requisition.save') : __('purchase_requisition.create') }}</x-ui.button>
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
        <x-ui.icon-button type="button" icon="trash" variant="danger" data-pr-remove-item :label="__('purchase_requisition.remove_item')" />
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
