@extends('layouts.client-area')

@php
    $editing = $order !== null;
    $productsById = $products->keyBy('id');
    $warehousesById = $warehouses->keyBy('id');
@endphp

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_order'))
@section('client-page-title', $editing ? __('purchase_order.edit') : __('purchase_order.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $editing ? __('purchase_order.edit') : __('purchase_order.create') }}">
        <x-slot:actions>
        <x-ui.button :href="$editing ? route('purchasing.orders.show', $order) : route('purchasing.orders.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.orders.update', $order) : route('purchasing.orders.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="{{ __('purchase_order.supplier') }}" for="supplier-id" :required="true" :error="$errors->first('supplier_id')">
                    <x-ui.select name="supplier_id" class="mt-2" required data-search="on" data-placeholder="{{ __('purchase_order.select_supplier') }}" data-ajax-url="{{ route('purchasing.lookups.suppliers') }}" data-minimum-input-length="1" id="supplier-id" :aria-describedby="$errors->has('supplier_id') ? 'supplier-id-error' : null">
                        <option value="">{{ __('purchase_order.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $order?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="{{ __('purchase_order.requisition') }}" for="purchase-requisition-id" :error="$errors->first('purchase_requisition_id')">
                    <x-ui.select name="purchase_requisition_id" class="mt-2" data-search="on" data-placeholder="{{ __('purchase_order.select_requisition') }}" data-ajax-url="{{ route('purchasing.lookups.requisitions') }}" data-minimum-input-length="1" id="purchase-requisition-id" :aria-describedby="$errors->has('purchase_requisition_id') ? 'purchase-requisition-id-error' : null">
                        <option value="">{{ __('purchase_order.select_requisition') }}</option>
                        @foreach ($requisitions as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_requisition_id', $order?->purchase_requisition_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.field label="{{ __('purchase_order.order_date') }}" for="order-date" :required="true" :error="$errors->first('order_date')">
                    <x-ui.date-picker name="order_date" :value="old('order_date', $order?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required id="order-date" :aria-describedby="$errors->has('order_date') ? 'order-date-error' : null" />
                </x-ui.field>

                <x-ui.field label="{{ __('purchase_order.expected_delivery_date') }}" for="expected-delivery-date" :error="$errors->first('expected_delivery_date')">
                    <x-ui.date-picker name="expected_delivery_date" :value="old('expected_delivery_date', $order?->expected_delivery_date?->format('Y-m-d'))" class="mt-2" id="expected-delivery-date" :aria-describedby="$errors->has('expected_delivery_date') ? 'expected-delivery-date-error' : null" />
                </x-ui.field>

                <x-ui.field label="{{ __('purchase_order.status') }}" for="status" :required="true" :error="$errors->first('status')">
                    <x-ui.select name="status" class="mt-2" required data-search="off" id="status" :aria-describedby="$errors->has('status') ? 'status-error' : null">
                        <option value="DRAFT" @selected(old('status', $order?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_order.status_draft') }}</option>
                        <option value="APPROVED" @selected(old('status', $order?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_order.status_approved') }}</option>
                        <option value="CANCELLED" @selected(old('status', $order?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_order.status_cancelled') }}</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            <x-ui.field label="{{ __('purchase_order.notes') }}" for="notes" :error="$errors->first('notes')">
                <x-ui.textarea name="notes" class="mt-2" rows="4" id="notes" :aria-describedby="$errors->has('notes') ? 'notes-error' : null">{{ old('notes', $order?->notes) }}</x-ui.textarea>
            </x-ui.field>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-xl font-semibold">{{ __('purchase_order.items') }}</h2>
                    <x-ui.button type="button" variant="outline" class="rounded-full" data-po-add-item><x-ui.icon name="plus" size="sm" /> {{ __('purchase_order.add_item') }}</x-ui.button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[1100px]">
                        <div class="grid grid-cols-[2fr_1.4fr_1fr_1fr_1.2fr_1.2fr_auto] gap-4 border-b border-[var(--ui-border)] pb-2 text-xs font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">
                            <span>{{ __('purchase_order.product') }}</span>
                            <span>{{ __('purchase_order.warehouse') }}</span>
                            <span>{{ __('purchase_order.quantity') }}</span>
                            <span>{{ __('purchase_order.unit_price') }}</span>
                            <span>{{ __('purchase_order.need_by_date') }}</span>
                            <span>{{ __('purchase_order.promised_date') }}</span>
                            <span class="sr-only">{{ __('purchase_order.remove_item') }}</span>
                        </div>

                        <div class="mt-3 space-y-3" data-po-items-container>
                            @foreach (old('items', $lineRows) as $index => $item)
                                <div class="grid grid-cols-[2fr_1.4fr_1fr_1fr_1.2fr_1.2fr_auto] items-start gap-4" data-po-item-row>
                                    <x-ui.select name="items[{{ $index }}][product_id]" required data-search="on" data-placeholder="{{ __('purchase_order.select_product') }}" data-ajax-url="{{ route('purchasing.lookups.products') }}" data-minimum-input-length="1">
                                        <option value="">{{ __('purchase_order.select_product') }}</option>
                                        @php($selectedProductId = (int) old('items.'.$index.'.product_id', $item['product_id'] ?? 0))
                                        @if ($selectedProductId > 0 && $productsById->has($selectedProductId))
                                            <option value="{{ $selectedProductId }}" selected>{{ $productsById[$selectedProductId]->sku }} - {{ $productsById[$selectedProductId]->description ?? '—' }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.select name="items[{{ $index }}][warehouse_id]" data-search="on">
                                        <option value="">{{ __('purchase_order.select_warehouse') }}</option>
                                        @php($selectedWarehouseId = (int) old('items.'.$index.'.warehouse_id', $item['warehouse_id'] ?? 0))
                                        @if ($selectedWarehouseId > 0 && $warehousesById->has($selectedWarehouseId))
                                            <option value="{{ $selectedWarehouseId }}" selected>{{ $warehousesById[$selectedWarehouseId]->code }} - {{ $warehousesById[$selectedWarehouseId]->name }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity]" :value="old('items.'.$index.'.quantity', $item['quantity'] ?? 1)" required />
                                    <x-ui.input type="text" name="items[{{ $index }}][unit_price]" :value="old('items.'.$index.'.unit_price', $item['unit_price'] ?? '0,00')" data-currency-mask="brl" inputmode="decimal" />
                                    <x-ui.date-picker name="items[{{ $index }}][need_by_date]" :value="old('items.'.$index.'.need_by_date', $item['need_by_date'] ?? '')" />
                                    <x-ui.date-picker name="items[{{ $index }}][promised_date]" :value="old('items.'.$index.'.promised_date', $item['promised_date'] ?? '')" />

                                    <x-ui.icon-button type="button" icon="trash" variant="danger" data-po-remove-item :label="__('purchase_order.remove_item')" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.orders.show', $order) : route('purchasing.orders.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_order.save') : __('purchase_order.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="po-item-template">
    <div class="grid grid-cols-[2fr_1.4fr_1fr_1fr_1.2fr_1.2fr_auto] items-start gap-4" data-po-item-row>
        <x-ui.select name="items[__INDEX__][product_id]" required data-search="on" data-placeholder="{{ __('purchase_order.select_product') }}" data-ajax-url="{{ route('purchasing.lookups.products') }}" data-minimum-input-length="1">
            <option value="">{{ __('purchase_order.select_product') }}</option>
        </x-ui.select>
        <x-ui.select name="items[__INDEX__][warehouse_id]" data-search="on">
            <option value="">{{ __('purchase_order.select_warehouse') }}</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity]" value="1" required />
        <x-ui.input type="text" name="items[__INDEX__][unit_price]" value="0,00" data-currency-mask="brl" inputmode="decimal" />
        <x-ui.date-picker name="items[__INDEX__][need_by_date]" />
        <x-ui.date-picker name="items[__INDEX__][promised_date]" />
        <x-ui.icon-button type="button" icon="trash" variant="danger" data-po-remove-item :label="__('purchase_order.remove_item')" />
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-po-items-container]');
    const template = document.getElementById('po-item-template');
    const addButton = document.querySelector('[data-po-add-item]');

    if (!container || !template || !addButton) {
        return;
    }

    const bindRow = (row) => {
        const removeButton = row.querySelector('[data-po-remove-item]');

        if (removeButton) {
            removeButton.addEventListener('click', () => {
                if (container.querySelectorAll('[data-po-item-row]').length === 1) {
                    return;
                }

                row.remove();
            });
        }
    };

    container.querySelectorAll('[data-po-item-row]').forEach(bindRow);

    addButton.addEventListener('click', () => {
        const index = container.querySelectorAll('[data-po-item-row]').length;
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
