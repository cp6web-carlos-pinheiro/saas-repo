@extends('layouts.client-area')

@section('title', __('ui.module_production_mrp').' | '.__('ui.bom_revisions'))
@section('client-page-title', __('bom.title'))

@section('client-content')
@php
    $selectedStatus = old('status', $bom?->status ?? 'DRAFT');
    $itemRows = old('items', $itemsForm);
    $productsById = $products->keyBy('id');
@endphp

<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('bom.edit') : __('bom.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('bom.material-lists.show', $bom) : route('bom.material-lists.index')" variant="material-back" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('bom.material-lists.update', $bom) : route('bom.material-lists.store') }}" class="space-y-8">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            @if ($editing)
                <x-ui.input type="hidden" name="product_id" :value="old('product_id', $bom->product_id)" unstyled />

                <div class="rounded-2xl border border-[#dadce0] bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">{{ $bom->product?->sku }}</h2>
                            <p class="text-sm text-[#5f6368]">{{ $bom->product?->description ?? '—' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs {{ $bom->status === 'DRAFT' ? 'bg-slate-100 text-slate-600' : ($bom->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ __('bom.status_'.$bom->status) }}
                        </span>
                    </div>
                </div>
            @else
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="product_id">{{ __('bom.select_product') }}</label>
                        <x-ui.select id="product_id" name="product_id" required data-search="on" data-placeholder="{{ __('bom.select_product') }}" data-ajax-url="{{ route('products.search') }}" data-minimum-input-length="1">
                            <option value="">{{ __('bom.select_product') }}</option>
                            @if ($selectedProductId !== null && $productsById->has((int) $selectedProductId))
                                <option value="{{ $selectedProductId }}" selected>
                                    {{ $productsById[(int) $selectedProductId]->sku }} - {{ $productsById[(int) $selectedProductId]->description ?? __('bom.product_hint') }}
                                </option>
                            @endif
                        </x-ui.select>
                        <p class="mt-2 text-sm text-[#5f6368]">{{ __('bom.product_hint') }}</p>
                        @error('product_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endif

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="status">{{ __('bom.status') }}</label>
                    <x-ui.select id="status" name="status" required data-search="off">
                        <option value="DRAFT" @selected($selectedStatus === 'DRAFT')>{{ __('bom.status_DRAFT') }}</option>
                        <option value="APPROVED" @selected($selectedStatus === 'APPROVED')>{{ __('bom.status_APPROVED') }}</option>
                        <option value="OBSOLETE" @selected($selectedStatus === 'OBSOLETE')>{{ __('bom.status_OBSOLETE') }}</option>
                    </x-ui.select>
                    @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="effective_from">{{ __('bom.effective_from') }}</label>
                    <x-ui.input id="effective_from" type="date" name="effective_from" :value="old('effective_from', $bom?->effective_from?->format('Y-m-d'))" />
                    @error('effective_from')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="effective_to">{{ __('bom.effective_to') }}</label>
                    <x-ui.input id="effective_to" type="date" name="effective_to" :value="old('effective_to', $bom?->effective_to?->format('Y-m-d'))" />
                    @error('effective_to')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-3">
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="description">{{ __('bom.description') }}</label>
                    <x-ui.input id="description" name="description" :value="old('description', $bom?->description ?? '')" maxlength="255" />
                    @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold">{{ __('bom.items') }}</h2>
                        <p class="text-sm text-[#5f6368]">{{ __('bom.product_hint') }}</p>
                    </div>
                    <button type="button" class="rounded-full border border-[#dadce0] px-4 py-2 text-sm font-medium" data-bom-add-item>{{ __('bom.add_item') }}</button>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[920px]">
                        <div class="grid grid-cols-[2fr_1fr_1fr_auto] gap-4 border-b border-[#dadce0] pb-2 text-xs font-semibold uppercase tracking-wide text-[#5f6368]">
                            <span>{{ __('bom.component_product') }}</span>
                            <span>{{ __('bom.quantity_per') }}</span>
                            <span>{{ __('bom.uom') }}</span>
                            <span class="sr-only">{{ __('bom.remove_item') }}</span>
                        </div>

                        <div
                            class="mt-3 space-y-3"
                            data-bom-items-container
                            data-component-unit-url-template="{{ route('bom.material-lists.component-products.unit', ['product' => '__PRODUCT__']) }}"
                        >
                            @foreach ($itemRows as $index => $item)
                                @php($selectedUnitId = (int) old('items.'.$index.'.unit_id', $item['unit_id'] ?? 0))
                                @php($selectedUnitLabel = $selectedUnitId > 0 ? ($units[$selectedUnitId] ?? '') : '')
                                <div class="grid grid-cols-[2fr_1fr_1fr_auto] items-start gap-4" data-bom-item-row>
                                    <div>
                                        <x-ui.select name="items[{{ $index }}][component_product_id]" required data-search="on" data-placeholder="{{ __('bom.component_product') }}" data-ajax-url="{{ route('products.search') }}" data-minimum-input-length="1">
                                            <option value="">{{ __('bom.component_product') }}</option>
                                            @php($selectedComponentId = (int) old('items.'.$index.'.component_product_id', $item['component_product_id'] ?? 0))
                                            @if ($selectedComponentId > 0 && $productsById->has($selectedComponentId))
                                                <option value="{{ $selectedComponentId }}" selected>
                                                    {{ $productsById[$selectedComponentId]->sku }} - {{ $productsById[$selectedComponentId]->description ?? '—' }}
                                                </option>
                                            @endif
                                        </x-ui.select>
                                        @error('items.'.$index.'.component_product_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <x-ui.input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity_per]" :value="old('items.'.$index.'.quantity_per', $item['quantity_per'] ?? 1)" required />
                                    </div>

                                    <div>
                                        <x-ui.input type="hidden" name="items[{{ $index }}][unit_id]" :value="$selectedUnitId > 0 ? (string) $selectedUnitId : ''" data-bom-unit-id unstyled />
                                        <x-ui.input type="text" :value="$selectedUnitLabel" data-bom-unit-label readonly />
                                        @error('items.'.$index.'.unit_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-bom-remove-item aria-label="{{ __('bom.remove_item') }}" title="{{ __('bom.remove_item') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                            <span class="sr-only">{{ __('bom.remove_item') }}</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @error('items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </section>

            <div class="flex flex-wrap gap-3">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('bom.save') }}</x-ui.button>
                <x-ui.button :href="$editing ? route('bom.material-lists.show', $bom) : route('bom.material-lists.index')" variant="material-back" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="bom-item-template">
    <div class="grid grid-cols-[2fr_1fr_1fr_auto] items-start gap-4" data-bom-item-row>
        <div>
            <x-ui.select name="items[__INDEX__][component_product_id]" required data-search="on" data-placeholder="{{ __('bom.component_product') }}" data-ajax-url="{{ route('products.search') }}" data-minimum-input-length="1">
                <option value="">{{ __('bom.component_product') }}</option>
            </x-ui.select>
        </div>

        <div>
            <x-ui.input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity_per]" value="1" required />
        </div>

        <div>
            <x-ui.input type="hidden" name="items[__INDEX__][unit_id]" value="" data-bom-unit-id unstyled />
            <x-ui.input type="text" value="" data-bom-unit-label readonly />
        </div>

        <div class="flex justify-end">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dadce0] text-red-600 transition hover:bg-red-50" data-bom-remove-item aria-label="{{ __('bom.remove_item') }}" title="{{ __('bom.remove_item') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 6h18" />
                    <path d="M8 6V4h8v2" />
                    <path d="M19 6l-1 14H6L5 6" />
                    <path d="M10 11v6" />
                    <path d="M14 11v6" />
                </svg>
                <span class="sr-only">{{ __('bom.remove_item') }}</span>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-bom-items-container]');
    const template = document.getElementById('bom-item-template');
    const addButton = document.querySelector('[data-bom-add-item]');
    const unitUrlTemplate = container?.getAttribute('data-component-unit-url-template') ?? '';

    if (!container || !template || !addButton) {
        return;
    }

    const updateRow = (row, index) => {
        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(/items\[(?:__INDEX__|\d+)\]/, `items[${index}]`);
        });
    };

    const resetRow = (row) => {
        row.querySelectorAll('input').forEach((input) => {
            if (input.type === 'number') {
                input.value = '1';
            } else {
                input.value = '';
            }
        });

        row.querySelectorAll('select').forEach((select) => {
            select.selectedIndex = 0;
        });
    };

    const setUnitFields = (row, unitId, unitLabel) => {
        const unitIdInput = row.querySelector('[data-bom-unit-id]');
        const unitLabelInput = row.querySelector('[data-bom-unit-label]');

        if (unitIdInput) {
            unitIdInput.value = unitId ? String(unitId) : '';
        }

        if (unitLabelInput) {
            unitLabelInput.value = unitLabel ?? '';
        }
    };

    const fetchComponentUnit = async (productId) => {
        if (!unitUrlTemplate || productId <= 0) {
            return null;
        }

        const url = unitUrlTemplate.replace('__PRODUCT__', String(productId));

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch (_) {
            return null;
        }
    };

    const syncRowUnitFromComponent = async (row) => {
        const componentSelect = row.querySelector('[name$="[component_product_id]"]');

        if (!componentSelect) {
            return;
        }

        const productId = Number.parseInt(componentSelect.value || '0', 10);

        if (productId <= 0) {
            setUnitFields(row, '', '');
            return;
        }

        const payload = await fetchComponentUnit(productId);

        if (!payload) {
            setUnitFields(row, '', '');
            return;
        }

        setUnitFields(row, payload.unit_id ?? '', payload.unit_label ?? payload.uom ?? '');
    };

    const bindComponentSelect = (row) => {
        const componentSelect = row.querySelector('[name$="[component_product_id]"]');

        if (!componentSelect) {
            return;
        }

        componentSelect.addEventListener('change', () => {
            void syncRowUnitFromComponent(row);
        });
    };

    const bindRemove = (row) => {
        const button = row.querySelector('[data-bom-remove-item]');

        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            const rows = container.querySelectorAll('[data-bom-item-row]');

            if (rows.length <= 1) {
                resetRow(row);
                return;
            }

            row.remove();
            container.querySelectorAll('[data-bom-item-row]').forEach((currentRow, index) => updateRow(currentRow, index));
        });
    };

    const bindExistingRows = () => {
        container.querySelectorAll('[data-bom-item-row]').forEach((row, index) => {
            updateRow(row, index);
            bindRemove(row);
            bindComponentSelect(row);

            const unitIdInput = row.querySelector('[data-bom-unit-id]');

            if (!unitIdInput || unitIdInput.value === '') {
                void syncRowUnitFromComponent(row);
            }
        });
    };

    addButton.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('[data-bom-item-row]');
        const nextIndex = container.querySelectorAll('[data-bom-item-row]').length;

        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace('__INDEX__', String(nextIndex));
        });

        bindRemove(row);
        bindComponentSelect(row);
        container.appendChild(fragment);
        container.querySelectorAll('[data-bom-item-row]').forEach((currentRow, index) => updateRow(currentRow, index));
    });

    bindExistingRows();
});
</script>
@endsection
