@extends('layouts.client-area')

@php
    $editing = $quotation !== null;
    $productsById = $products->keyBy('id');
@endphp

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_quotation'))
@section('client-page-title', $editing ? __('purchase_quotation.edit') : __('purchase_quotation.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('purchase_quotation.edit') : __('purchase_quotation.create') }}</h1>
        <x-ui.button :href="$editing ? route('purchasing.quotations.show', $quotation) : route('purchasing.quotations.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('purchasing.quotations.update', $quotation) : route('purchasing.quotations.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.supplier') }}
                    <x-ui.select name="supplier_id" class="mt-2" data-search="on" data-placeholder="{{ __('purchase_quotation.select_supplier') }}" data-ajax-url="{{ route('purchasing.lookups.suppliers') }}" data-minimum-input-length="1">
                        <option value="">{{ __('purchase_quotation.select_supplier') }}</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('supplier_id', $quotation?->supplier_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('supplier_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.requisition') }}
                    <x-ui.select name="purchase_requisition_id" class="mt-2" data-search="on" data-placeholder="{{ __('purchase_quotation.select_requisition') }}" data-ajax-url="{{ route('purchasing.lookups.requisitions') }}" data-minimum-input-length="1">
                        <option value="">{{ __('purchase_quotation.select_requisition') }}</option>
                        @foreach ($requisitions as $id => $number)
                            <option value="{{ $id }}" @selected((string) old('purchase_requisition_id', $quotation?->purchase_requisition_id) === (string) $id)>{{ $number }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('purchase_requisition_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.quotation_date') }}
                    <x-ui.input type="date" name="quotation_date" :value="old('quotation_date', $quotation?->quotation_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" class="mt-2" required />
                    @error('quotation_date')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.valid_until') }}
                    <x-ui.input type="date" name="valid_until" :value="old('valid_until', $quotation?->valid_until?->format('Y-m-d'))" class="mt-2" />
                    @error('valid_until')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('purchase_quotation.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'DRAFT')>{{ __('purchase_quotation.status_draft') }}</option>
                        <option value="RECEIVED" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'RECEIVED')>{{ __('purchase_quotation.status_received') }}</option>
                        <option value="APPROVED" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'APPROVED')>{{ __('purchase_quotation.status_approved') }}</option>
                        <option value="REJECTED" @selected(old('status', $quotation?->status ?? 'DRAFT') === 'REJECTED')>{{ __('purchase_quotation.status_rejected') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-xl font-semibold">{{ __('purchase_quotation.items') }}</h2>
                    <button type="button" class="rounded-full border border-[#dadce0] px-4 py-2 text-sm font-medium" data-pq-add-item>{{ __('purchase_quotation.add_item') }}</button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[920px]">
                        <div class="grid grid-cols-[2.2fr_1fr_1fr_1.6fr_auto] gap-4 border-b border-[#dadce0] pb-2 text-xs font-semibold uppercase tracking-wide text-[#5f6368]">
                            <span>{{ __('purchase_quotation.product') }}</span>
                            <span>{{ __('purchase_quotation.quantity') }}</span>
                            <span>{{ __('purchase_quotation.unit_price') }}</span>
                            <span>{{ __('purchase_quotation.notes') }}</span>
                            <span class="sr-only">{{ __('purchase_quotation.remove_item') }}</span>
                        </div>

                        <div class="mt-3 space-y-3" data-pq-items-container>
                            @foreach (old('items', $lineRows) as $index => $item)
                                <div class="grid grid-cols-[2.2fr_1fr_1fr_1.6fr_auto] items-start gap-4" data-pq-item-row>
                                    <x-ui.select name="items[{{ $index }}][product_id]" required data-search="on">
                                        <option value="">{{ __('purchase_quotation.select_product') }}</option>
                                        @php($selectedProductId = (int) old('items.'.$index.'.product_id', $item['product_id'] ?? 0))
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected($selectedProductId === $product->id)>{{ $product->sku }} - {{ $product->description ?? '—' }}</option>
                                        @endforeach
                                    </x-ui.select>

                                    <x-ui.input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity]" :value="old('items.'.$index.'.quantity', $item['quantity'] ?? 1)" required />
                                    <x-ui.input name="items[{{ $index }}][unit_price]" :value="old('items.'.$index.'.unit_price', $item['unit_price'] ?? '0,00')" data-currency-mask="brl" inputmode="decimal" required />
                                    <x-ui.input name="items[{{ $index }}][notes]" :value="old('items.'.$index.'.notes', $item['notes'] ?? null)" />
                                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-pq-remove-item aria-label="{{ __('purchase_quotation.remove_item') }}" title="{{ __('purchase_quotation.remove_item') }}">
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
                {{ __('purchase_quotation.notes') }}
                <x-ui.textarea name="notes" class="mt-2" rows="4">{{ old('notes', $quotation?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('purchasing.quotations.show', $quotation) : route('purchasing.quotations.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('purchase_quotation.save') : __('purchase_quotation.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="pq-item-template">
    <div class="grid grid-cols-[2.2fr_1fr_1fr_1.6fr_auto] items-start gap-4" data-pq-item-row>
        <x-ui.select name="items[__INDEX__][product_id]" required data-search="on">
            <option value="">{{ __('purchase_quotation.select_product') }}</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->description ?? '—' }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity]" value="1" required />
        <x-ui.input name="items[__INDEX__][unit_price]" value="0,00" data-currency-mask="brl" inputmode="decimal" required />
        <x-ui.input name="items[__INDEX__][notes]" />
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-pq-remove-item aria-label="{{ __('purchase_quotation.remove_item') }}" title="{{ __('purchase_quotation.remove_item') }}">
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
    const container = document.querySelector('[data-pq-items-container]');
    const template = document.getElementById('pq-item-template');
    const addButton = document.querySelector('[data-pq-add-item]');

    if (!container || !template || !addButton) {
        return;
    }

    const bindRow = (row) => {
        const removeButton = row.querySelector('[data-pq-remove-item]');

        if (removeButton) {
            removeButton.addEventListener('click', () => {
                if (container.querySelectorAll('[data-pq-item-row]').length === 1) {
                    return;
                }

                row.remove();
            });
        }
    };

    container.querySelectorAll('[data-pq-item-row]').forEach(bindRow);

    addButton.addEventListener('click', () => {
        const index = container.querySelectorAll('[data-pq-item-row]').length;
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
