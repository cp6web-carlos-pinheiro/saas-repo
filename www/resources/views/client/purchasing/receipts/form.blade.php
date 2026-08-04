@extends('layouts.client-area')

@php
    $editing = $receipt !== null;
    $productsById = $products->keyBy('id');
    $warehousesById = $warehouses->keyBy('id');
@endphp

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_receipt'))
@section('client-page-title', $editing ? __('purchase_receipt.edit') : __('purchase_receipt.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_receipt.edit') : __('purchase_receipt.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.receipts.show', $receipt) : route('purchasing.receipts.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.receipts.update', $receipt) : route('purchasing.receipts.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.supplier') }}
                    <x-ui.select name="supplier_id" class="mt-2" data-search="on" data-placeholder="{{ __('purchase_receipt.select_supplier') }}" data-ajax-url="{{ route('purchasing.lookups.suppliers') }}" data-minimum-input-length="1">
                        <option value="">{{ __('purchase_receipt.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $receipt?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('supplier_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.order') }}
                    <x-ui.select name="purchase_order_id" class="mt-2" data-search="on" data-placeholder="{{ __('purchase_receipt.select_order') }}" data-ajax-url="{{ route('purchasing.lookups.orders') }}" data-minimum-input-length="1">
                        <option value="">{{ __('purchase_receipt.select_order') }}</option>
                        @foreach ($orders as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_order_id', $receipt?->purchase_order_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('purchase_order_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.receipt_date') }}
                    <x-ui.input type="date" name="receipt_date" :value="old('receipt_date', $receipt?->receipt_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required />
                    @error('receipt_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_receipt.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $receipt?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_receipt.status_draft') }}</option>
                        <option value="POSTED" @selected(old('status', $receipt?->status ?? 'DRAFT') === 'POSTED')>{{ __('purchase_receipt.status_posted') }}</option>
                        <option value="CANCELLED" @selected(old('status', $receipt?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('purchase_receipt.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('purchase_receipt.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $receipt?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-xl font-semibold">{{ __('purchase_receipt.items') }}</h2>
                    <button type="button" class="rounded-full border border-[#dadce0] px-4 py-2 text-sm font-medium" data-prc-add-item>{{ __('purchase_receipt.add_item') }}</button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[1200px]">
                        <div class="grid grid-cols-[1.6fr_2fr_1.4fr_1fr_1fr_1.4fr_auto] gap-4 border-b border-[#dadce0] pb-2 text-xs font-semibold uppercase tracking-wide text-[#5f6368]">
                            <span>{{ __('purchase_receipt.order_line') }}</span>
                            <span>{{ __('purchase_receipt.product') }}</span>
                            <span>{{ __('purchase_receipt.warehouse') }}</span>
                            <span>{{ __('purchase_receipt.quantity_received') }}</span>
                            <span>{{ __('purchase_receipt.lot_number') }}</span>
                            <span>{{ __('purchase_receipt.notes') }}</span>
                            <span class="sr-only">{{ __('purchase_receipt.remove_item') }}</span>
                        </div>

                        <div class="mt-3 space-y-3" data-prc-items-container>
                            @foreach (old('items', $lineRows) as $index => $item)
                                <div class="grid grid-cols-[1.6fr_2fr_1.4fr_1fr_1fr_1.4fr_auto] items-start gap-4" data-prc-item-row>
                                    <x-ui.select name="items[{{ $index }}][purchase_order_line_id]" data-search="on" data-placeholder="{{ __('purchase_receipt.select_order_line') }}" data-ajax-url="{{ route('purchasing.lookups.order-lines', ['order_id' => (int) old('purchase_order_id', $receipt?->purchase_order_id ?? 0)]) }}" data-minimum-input-length="1">
                                        <option value="">{{ __('purchase_receipt.select_order_line') }}</option>
                                        @foreach ($orderLines as $line)
                                            <option value="{{ $line->id }}" @selected((int) old('items.'.$index.'.purchase_order_line_id', $item['purchase_order_line_id'] ?? 0) === $line->id)>
                                                #{{ $line->id }} - {{ $line->product?->sku }} ({{ number_format((float) $line->quantity_ordered, 6, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </x-ui.select>

                                    <x-ui.select name="items[{{ $index }}][product_id]" required data-search="on" data-placeholder="{{ __('purchase_receipt.select_product') }}" data-ajax-url="{{ route('purchasing.lookups.products') }}" data-minimum-input-length="1">
                                        <option value="">{{ __('purchase_receipt.select_product') }}</option>
                                        @php($selectedProductId = (int) old('items.'.$index.'.product_id', $item['product_id'] ?? 0))
                                        @if ($selectedProductId > 0 && $productsById->has($selectedProductId))
                                            <option value="{{ $selectedProductId }}" selected>{{ $productsById[$selectedProductId]->sku }} - {{ $productsById[$selectedProductId]->description ?? '—' }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.select name="items[{{ $index }}][warehouse_id]" required data-search="on">
                                        <option value="">{{ __('purchase_receipt.select_warehouse') }}</option>
                                        @php($selectedWarehouseId = (int) old('items.'.$index.'.warehouse_id', $item['warehouse_id'] ?? 0))
                                        @if ($selectedWarehouseId > 0 && $warehousesById->has($selectedWarehouseId))
                                            <option value="{{ $selectedWarehouseId }}" selected>{{ $warehousesById[$selectedWarehouseId]->code }} - {{ $warehousesById[$selectedWarehouseId]->name }}</option>
                                        @endif
                                    </x-ui.select>

                                    <x-ui.input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity_received]" :value="old('items.'.$index.'.quantity_received', $item['quantity_received'] ?? 1)" required />
                                    <x-ui.input name="items[{{ $index }}][lot_number]" :value="old('items.'.$index.'.lot_number', $item['lot_number'] ?? null)" />
                                    <x-ui.input name="items[{{ $index }}][notes]" :value="old('items.'.$index.'.notes', $item['notes'] ?? null)" />

                                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-prc-remove-item aria-label="{{ __('purchase_receipt.remove_item') }}" title="{{ __('purchase_receipt.remove_item') }}">
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

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.receipts.show', $receipt) : route('purchasing.receipts.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_receipt.save') : __('purchase_receipt.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="prc-item-template">
    <div class="grid grid-cols-[1.6fr_2fr_1.4fr_1fr_1fr_1.4fr_auto] items-start gap-4" data-prc-item-row>
        <x-ui.select name="items[__INDEX__][purchase_order_line_id]" data-search="on" data-placeholder="{{ __('purchase_receipt.select_order_line') }}" data-ajax-url="{{ route('purchasing.lookups.order-lines', ['order_id' => (int) old('purchase_order_id', $receipt?->purchase_order_id ?? 0)]) }}" data-minimum-input-length="1">
            <option value="">{{ __('purchase_receipt.select_order_line') }}</option>
            @foreach ($orderLines as $line)
                <option value="{{ $line->id }}">#{{ $line->id }} - {{ $line->product?->sku }} ({{ number_format((float) $line->quantity_ordered, 6, ',', '.') }})</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="items[__INDEX__][product_id]" required data-search="on" data-placeholder="{{ __('purchase_receipt.select_product') }}" data-ajax-url="{{ route('purchasing.lookups.products') }}" data-minimum-input-length="1">
            <option value="">{{ __('purchase_receipt.select_product') }}</option>
        </x-ui.select>
        <x-ui.select name="items[__INDEX__][warehouse_id]" required data-search="on">
            <option value="">{{ __('purchase_receipt.select_warehouse') }}</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity_received]" value="1" required />
        <x-ui.input name="items[__INDEX__][lot_number]" />
        <x-ui.input name="items[__INDEX__][notes]" />
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-prc-remove-item aria-label="{{ __('purchase_receipt.remove_item') }}" title="{{ __('purchase_receipt.remove_item') }}">
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
    const container = document.querySelector('[data-prc-items-container]');
    const template = document.getElementById('prc-item-template');
    const addButton = document.querySelector('[data-prc-add-item]');
    const orderSelect = document.querySelector('select[name="purchase_order_id"]');

    if (!container || !template || !addButton) {
        return;
    }

    const bindRow = (row) => {
        const removeButton = row.querySelector('[data-prc-remove-item]');

        if (removeButton) {
            removeButton.addEventListener('click', () => {
                if (container.querySelectorAll('[data-prc-item-row]').length === 1) {
                    return;
                }

                row.remove();
            });
        }
    };

    container.querySelectorAll('[data-prc-item-row]').forEach(bindRow);

    const buildOrderLineLookupUrl = () => {
        const baseUrl = "{{ route('purchasing.lookups.order-lines') }}";
        const orderId = orderSelect && orderSelect.value ? Number.parseInt(orderSelect.value, 10) : 0;
        const url = new URL(baseUrl, window.location.origin);

        if (!Number.isNaN(orderId) && orderId > 0) {
            url.searchParams.set('order_id', String(orderId));
        }

        return `${url.pathname}${url.search}`;
    };

    const refreshOrderLineSelects = () => {
        const selects = container.querySelectorAll('select[name$="[purchase_order_line_id]"]');
        const ajaxUrl = buildOrderLineLookupUrl();

        for (const select of selects) {
            select.dataset.ajaxUrl = ajaxUrl;

            if (typeof window.jQuery?.fn?.select2 === 'function' && window.jQuery(select).hasClass('select2-hidden-accessible')) {
                window.jQuery(select).val(null).trigger('change');
                window.jQuery(select).select2('destroy');
            }

            const firstOption = select.querySelector('option[value=""]');
            select.innerHTML = '';

            if (firstOption) {
                select.appendChild(firstOption);
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = "{{ __('purchase_receipt.select_order_line') }}";
                select.appendChild(option);
            }
        }

        if (typeof window.initializeUiSelects === 'function') {
            window.initializeUiSelects();
        }
    };

    if (orderSelect) {
        orderSelect.addEventListener('change', refreshOrderLineSelects);
    }

    addButton.addEventListener('click', () => {
        const index = container.querySelectorAll('[data-prc-item-row]').length;
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        if (!row) {
            return;
        }
        container.appendChild(row);
        bindRow(row);

        if (typeof window.initializeUiSelects === 'function') {
            window.initializeUiSelects();
        }
    });
});
</script>
@endsection
