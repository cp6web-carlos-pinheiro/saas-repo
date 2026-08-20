@extends('layouts.client-area')

@php
    $editing = $sale !== null;
    $productsById = $products->keyBy('id');
@endphp

@section('title', __('ui.module_sales'))
@section('client-page-title', $editing ? __('sale.edit') : __('sale.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('sale.edit_reference', ['id' => $sale->id]) : __('sale.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('sales.show', $sale) : route('sales.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

        @php
            $initialSubtotalCents = collect(old('items', $itemRows))
                ->reduce(function (int $carry, array $item): int {
                    $quantity = (float) ($item['quantity'] ?? 0);
                    $rawPrice = trim((string) ($item['unit_price'] ?? '0'));

                    if (str_contains($rawPrice, ',')) {
                        $rawPrice = str_replace('.', '', $rawPrice);
                        $rawPrice = str_replace(',', '.', $rawPrice);
                    }

                    $unitPrice = is_numeric($rawPrice) ? (float) $rawPrice : 0;

                    return $carry + (int) round(($quantity * $unitPrice) * 100);
                }, 0);
        $initialDiscountCents = (int) round((float) str_replace(',', '.', str_replace('.', '', (string) old('discount_amount', $editing ? number_format(($sale?->discount_cents ?? 0) / 100, 2, ',', '.') : '0,00'))) * 100);
        $initialTotalCents = max(0, $initialSubtotalCents - $initialDiscountCents);
        @endphp

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('sales.update', $sale) : route('sales.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium sm:col-span-2">
                    {{ __('sale.customer') }}
                    <x-ui.select name="customer_id" class="mt-2" required data-search="on">
                        <option value="">{{ __('sale.select_customer') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((int) old('customer_id', $sale?->customer_id) === $customer->id)>
                                {{ $customer->name }}@if ($customer->status !== 'ACTIVE') - {{ __('sale.customer_inactive') }}@endif
                            </option>
                        @endforeach
                    </x-ui.select>
                    @error('customer_id')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('sale.sale_date') }}
                    <x-ui.input name="sale_date" type="date" :value="old('sale_date', $sale?->sale_date?->format('Y-m-d') ?? now()->toDateString())" required @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('sale_date'), 'border-[var(--ui-border)]' => ! $errors->has('sale_date')]) />
                    @error('sale_date')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('sale.status') }}
                    <x-ui.select name="status" class="mt-2" required data-search="off">
                        <option value="DRAFT" @selected(old('status', $sale?->status ?? 'DRAFT') === 'DRAFT')>{{ __('sale.status_draft') }}</option>
                        <option value="CONFIRMED" @selected(old('status', $sale?->status ?? 'DRAFT') === 'CONFIRMED')>{{ __('sale.status_confirmed') }}</option>
                        <option value="CANCELLED" @selected(old('status', $sale?->status ?? 'DRAFT') === 'CANCELLED')>{{ __('sale.status_cancelled') }}</option>
                    </x-ui.select>
                    @error('status')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                </label>

                <div class="sm:col-span-2">
                    <span class="block text-sm font-medium">{{ __('sale.operational_status') }}</span>
                    <div class="mt-2 flex h-11 items-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] px-3 text-sm font-semibold text-[var(--ui-primary-text)]">
                        {{ $editing ? match ($sale?->operational_status) {
                            'PICKING' => __('sale.operational_status_picking'),
                            'INVOICED' => __('sale.operational_status_invoiced'),
                            'SHIPPED' => __('sale.operational_status_shipped'),
                            'DELIVERED' => __('sale.operational_status_delivered'),
                            default => __('sale.operational_status_pending'),
                        } : __('sale.operational_status_pending') }}
                    </div>
                    <p class="mt-2 text-sm text-[var(--ui-text-muted)]">{{ __('sale.operational_status_hint') }}</p>
                </div>

            </div>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold">{{ __('sale.items') }}</h2>
                        <p class="text-sm text-[var(--ui-text-muted)]">{{ __('sale.items_hint') }}</p>
                    </div>
                    <x-ui.button type="button" variant="outline" class="rounded-full" data-sale-add-item><x-ui.icon name="plus" size="sm" /> {{ __('sale.add_item') }}</x-ui.button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[920px]">
                        <div class="grid grid-cols-[2.2fr_1fr_1fr_1fr_auto] gap-4 border-b border-[var(--ui-border)] pb-2 text-xs font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">
                            <span>{{ __('sale.product') }}</span>
                            <span>{{ __('sale.quantity') }}</span>
                            <span>{{ __('sale.unit_price') }}</span>
                            <span>{{ __('sale.line_total') }}</span>
                            <span class="sr-only">{{ __('sale.remove_item') }}</span>
                        </div>

                        <div class="mt-3 space-y-3" data-sale-items-container>
                            @foreach (old('items', $itemRows) as $index => $item)
                                <div class="grid grid-cols-[2.2fr_1fr_1fr_1fr_auto] items-start gap-4" data-sale-item-row>
                                    <div>
                                        <x-ui.select name="items[{{ $index }}][product_id]" required data-search="on" data-placeholder="{{ __('sale.select_product') }}" data-ajax-url="{{ route('sales.products.search') }}" data-minimum-input-length="1">
                                            <option value="">{{ __('sale.select_product') }}</option>
                                            @php($selectedProductId = (int) old('items.'.$index.'.product_id', $item['product_id'] ?? 0))
                                            @if ($selectedProductId > 0 && $productsById->has($selectedProductId))
                                                <option value="{{ $selectedProductId }}" selected>
                                                    {{ $productsById[$selectedProductId]->sku }} - {{ $productsById[$selectedProductId]->description ?? '—' }}@if (! $productsById[$selectedProductId]->is_active) - {{ __('sale.product_inactive') }}@endif
                                                </option>
                                            @endif
                                        </x-ui.select>
                                        @error('items.'.$index.'.product_id')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                                    </div>

                                    <div>
                                        <x-ui.input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity]" :value="old('items.'.$index.'.quantity', $item['quantity'] ?? 1)" required data-sale-line-quantity />
                                        @error('items.'.$index.'.quantity')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                                    </div>

                                    <div>
                                        <x-ui.input type="text" name="items[{{ $index }}][unit_price]" :value="old('items.'.$index.'.unit_price', $item['unit_price'] ?? null)" required inputmode="decimal" data-currency-mask="brl" data-sale-line-price @class(['border-[var(--ui-danger)]' => $errors->has('items.'.$index.'.unit_price')]) />
                                        @error('items.'.$index.'.unit_price')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="flex h-11 items-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] px-3 text-sm font-semibold text-[var(--ui-primary-text)]" data-sale-line-total>
                                        R$ 0,00
                                    </div>

                                    <div class="flex justify-end">
                                        <x-ui.icon-button type="button" icon="trash" variant="danger" data-sale-remove-item :label="__('sale.remove_item')" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @error('items')<span class="block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror

                <div class="grid gap-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] px-4 py-4 md:grid-cols-2">
                    <label class="block text-sm font-medium">
                        {{ __('sale.discount_amount') }}
                        <x-ui.input name="discount_amount" :value="old('discount_amount', $editing ? number_format(($sale?->discount_cents ?? 0) / 100, 2, ',', '.') : '0,00')" class="mt-2" inputmode="decimal" data-currency-mask="brl" data-sale-discount />
                        @error('discount_amount')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                    </label>

                    <div class="flex items-center justify-between rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface)] px-4 py-3">
                        <span class="text-sm font-medium text-[var(--ui-text-muted)]">{{ __('sale.subtotal') }}</span>
                        <strong class="text-base text-[var(--ui-primary-text)]" data-sale-subtotal data-subtotal-cents="{{ $initialSubtotalCents }}">R$ {{ number_format($initialSubtotalCents / 100, 2, ',', '.') }}</strong>
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface)] px-4 py-3">
                        <span class="text-sm font-medium text-[var(--ui-text-muted)]">{{ __('sale.amount') }}</span>
                        <strong class="text-lg text-[var(--ui-primary-text)]" data-sale-grand-total data-total-cents="{{ $initialTotalCents }}">R$ {{ number_format($initialTotalCents / 100, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </section>

            <label class="block text-sm font-medium">
                {{ __('sale.notes') }}
                <x-ui.textarea name="notes" rows="5" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('notes'), 'border-[var(--ui-border)]' => ! $errors->has('notes')])>{{ old('notes', $sale?->notes) }}</x-ui.textarea>
                @error('notes')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('sale.cancel_reason') }}
                <x-ui.textarea name="cancel_reason" rows="4" @class(['mt-2', 'border-[var(--ui-danger)]' => $errors->has('cancel_reason') || $errors->has('status'), 'border-[var(--ui-border)]' => ! $errors->has('cancel_reason') && ! $errors->has('status')])>{{ old('cancel_reason', $sale?->cancel_reason) }}</x-ui.textarea>
                <span class="mt-1 block text-xs text-[var(--ui-text-muted)]">{{ __('sale.cancel_reason_hint') }}</span>
                @error('cancel_reason')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
                @error('status')<span class="mt-1 block text-sm text-[var(--ui-danger)]">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('sales.show', $sale) : route('sales.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('sale.save') : __('sale.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="sale-item-template">
    <div class="grid grid-cols-[2.2fr_1fr_1fr_1fr_auto] items-start gap-4" data-sale-item-row>
        <div>
            <x-ui.select name="items[__INDEX__][product_id]" required data-search="on" data-placeholder="{{ __('sale.select_product') }}" data-ajax-url="{{ route('sales.products.search') }}" data-minimum-input-length="1">
                <option value="">{{ __('sale.select_product') }}</option>
            </x-ui.select>
        </div>

        <div>
            <x-ui.input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity]" value="1" required data-sale-line-quantity />
        </div>

        <div>
            <x-ui.input type="text" name="items[__INDEX__][unit_price]" value="0,00" required inputmode="decimal" data-currency-mask="brl" data-sale-line-price />
        </div>

        <div class="flex h-11 items-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] px-3 text-sm font-semibold text-[var(--ui-primary-text)]" data-sale-line-total>
            R$ 0,00
        </div>

        <div class="flex justify-end">
            <x-ui.icon-button type="button" icon="trash" variant="danger" data-sale-remove-item :label="__('sale.remove_item')" />
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-sale-items-container]');
    const template = document.getElementById('sale-item-template');
    const addButton = document.querySelector('[data-sale-add-item]');
    const grandTotal = document.querySelector('[data-sale-grand-total]');
    const subtotalElement = document.querySelector('[data-sale-subtotal]');
    const discountInput = document.querySelector('[data-sale-discount]');
    if (!container || !template || !addButton || !grandTotal || !subtotalElement || !discountInput) {
        return;
    }

    const formatMoney = (cents) => `R$ ${(cents / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const parseMoneyToCents = (value) => {
        const digits = String(value || '').replace(/\D/g, '');
        return digits === '' ? 0 : Number.parseInt(digits, 10);
    };

    const formatMoneyInput = (input) => {
        const cents = parseMoneyToCents(input.value);
        input.value = (cents / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const updateRowTotal = (row) => {
        const quantityInput = row.querySelector('[data-sale-line-quantity]');
        const priceInput = row.querySelector('[data-sale-line-price]');
        const totalElement = row.querySelector('[data-sale-line-total]');

        if (!quantityInput || !priceInput || !totalElement) {
            return 0;
        }

        const quantity = Number.parseFloat(quantityInput.value || '0');
        const unitPriceCents = parseMoneyToCents(priceInput.value);
        const lineTotalCents = Number.isFinite(quantity) ? Math.round(quantity * unitPriceCents) : 0;

        totalElement.textContent = formatMoney(lineTotalCents);

        return lineTotalCents;
    };

    const updateGrandTotal = () => {
        const rows = container.querySelectorAll('[data-sale-item-row]');
        let subtotalCents = 0;

        for (const row of rows) {
            subtotalCents += updateRowTotal(row);
        }

        const discountCents = parseMoneyToCents(discountInput.value);
        const totalCents = Math.max(0, subtotalCents - discountCents);

        subtotalElement.dataset.subtotalCents = String(subtotalCents);
        subtotalElement.textContent = formatMoney(subtotalCents);
        grandTotal.dataset.totalCents = String(totalCents);
        grandTotal.textContent = formatMoney(totalCents);
    };

    const bindRow = (row) => {
        const quantityInput = row.querySelector('[data-sale-line-quantity]');
        const priceInput = row.querySelector('[data-sale-line-price]');
        const removeButton = row.querySelector('[data-sale-remove-item]');

        if (priceInput) {
            formatMoneyInput(priceInput);
            priceInput.addEventListener('input', () => {
                formatMoneyInput(priceInput);
                updateGrandTotal();
            });
            priceInput.addEventListener('blur', () => {
                formatMoneyInput(priceInput);
                updateGrandTotal();
            });
        }

        if (quantityInput) {
            quantityInput.addEventListener('input', updateGrandTotal);
            quantityInput.addEventListener('change', updateGrandTotal);
        }

        if (removeButton) {
            removeButton.addEventListener('click', () => {
                if (container.querySelectorAll('[data-sale-item-row]').length === 1) {
                    return;
                }

                row.remove();
                updateGrandTotal();
            });
        }

        updateRowTotal(row);
    };

    let nextIndex = container.querySelectorAll('[data-sale-item-row]').length;

    for (const row of container.querySelectorAll('[data-sale-item-row]')) {
        bindRow(row);
    }

    for (const field of [discountInput]) {
        formatMoneyInput(field);
        field.addEventListener('input', () => {
            formatMoneyInput(field);
            updateGrandTotal();
        });
        field.addEventListener('blur', () => {
            formatMoneyInput(field);
            updateGrandTotal();
        });
    }

    addButton.addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
        nextIndex += 1;

        container.insertAdjacentHTML('beforeend', html);

        const rows = container.querySelectorAll('[data-sale-item-row]');
        const row = rows[rows.length - 1];

        if (row instanceof HTMLElement) {
            bindRow(row);
        }

        if (typeof window.initializeUiSelects === 'function') {
            window.initializeUiSelects();
        }

        updateGrandTotal();
    });

    updateGrandTotal();
});
</script>
@endsection