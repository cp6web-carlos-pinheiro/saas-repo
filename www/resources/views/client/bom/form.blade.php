@extends('layouts.client-area')

@section('title', ($editing ? __('bom.edit') : __('bom.create')).' | '.__('ui.app_name'))
@section('client-page-title', __('bom.title'))

@section('client-content')
@php
    $selectedStatus = old('status', $bom?->status ?? 'DRAFT');
    $itemRows = old('items', $itemsForm);
@endphp

<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('bom.edit') : __('bom.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('bom.material-lists.show', $bom) : route('bom.material-lists.index')" variant="surface-muted" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
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
                <input type="hidden" name="product_id" value="{{ old('product_id', $bom->product_id) }}">

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
                        <select id="product_id" name="product_id" class="w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                            <option value="">{{ __('bom.select_product') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                    {{ $product->sku }} - {{ $product->description ?? __('bom.product_hint') }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-[#5f6368]">{{ __('bom.product_hint') }}</p>
                        @error('product_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="status">{{ __('bom.status') }}</label>
                    <select id="status" name="status" class="w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                        <option value="DRAFT" @selected($selectedStatus === 'DRAFT')>{{ __('bom.status_DRAFT') }}</option>
                        <option value="APPROVED" @selected($selectedStatus === 'APPROVED')>{{ __('bom.status_APPROVED') }}</option>
                        <option value="OBSOLETE" @selected($selectedStatus === 'OBSOLETE')>{{ __('bom.status_OBSOLETE') }}</option>
                    </select>
                    @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="effective_from">{{ __('bom.effective_from') }}</label>
                    <input id="effective_from" type="date" name="effective_from" value="{{ old('effective_from', $bom?->effective_from?->format('Y-m-d')) }}" class="w-full rounded-xl border border-[#dadce0] px-4 py-3">
                    @error('effective_from')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="effective_to">{{ __('bom.effective_to') }}</label>
                    <input id="effective_to" type="date" name="effective_to" value="{{ old('effective_to', $bom?->effective_to?->format('Y-m-d')) }}" class="w-full rounded-xl border border-[#dadce0] px-4 py-3">
                    @error('effective_to')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="description">{{ __('bom.description') }}</label>
                    <input id="description" name="description" value="{{ old('description', $bom?->description ?? '') }}" class="w-full rounded-xl border border-[#dadce0] px-4 py-3" maxlength="255">
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

                <div class="space-y-4" data-bom-items-container>
                    @foreach ($itemRows as $index => $item)
                        <article class="rounded-2xl border border-[#dadce0] bg-white p-4" data-bom-item-row>
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <h3 class="font-semibold" data-bom-item-title>{{ __('bom.items') }} {{ $index + 1 }}</h3>
                                <button type="button" class="text-sm font-medium text-red-600" data-bom-remove-item>{{ __('bom.remove_item') }}</button>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-5">
                                <label class="block text-sm font-medium text-[#5f6368]">
                                    {{ __('bom.line_no') }}
                                    <input type="number" min="1" name="items[{{ $index }}][line_no]" value="{{ old('items.'.$index.'.line_no', $item['line_no'] ?? $index + 1) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                                </label>

                                <label class="block text-sm font-medium text-[#5f6368] md:col-span-2">
                                    {{ __('bom.component_product') }}
                                    <select name="items[{{ $index }}][component_product_id]" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                                        <option value="">{{ __('bom.component_product') }}</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected((string) old('items.'.$index.'.component_product_id', $item['component_product_id'] ?? '') === (string) $product->id)>
                                                {{ $product->sku }} - {{ $product->description ?? '—' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.component_product_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </label>

                                <label class="block text-sm font-medium text-[#5f6368]">
                                    {{ __('bom.quantity_per') }}
                                    <input type="number" step="0.000001" min="0.000001" name="items[{{ $index }}][quantity_per]" value="{{ old('items.'.$index.'.quantity_per', $item['quantity_per'] ?? 1) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                                </label>

                                <label class="block text-sm font-medium text-[#5f6368]">
                                    {{ __('bom.scrap_factor') }}
                                    <input type="number" step="0.0001" min="0" name="items[{{ $index }}][scrap_factor]" value="{{ old('items.'.$index.'.scrap_factor', $item['scrap_factor'] ?? 0) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3">
                                </label>

                                <label class="block text-sm font-medium text-[#5f6368]">
                                    {{ __('bom.uom') }}
                                    <input type="text" name="items[{{ $index }}][uom]" value="{{ old('items.'.$index.'.uom', $item['uom'] ?? '') }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" maxlength="20">
                                </label>
                            </div>
                        </article>
                    @endforeach
                </div>

                @error('items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </section>

            <div class="flex flex-wrap gap-3">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('bom.save') }}</x-ui.button>
                <x-ui.button :href="$editing ? route('bom.material-lists.show', $bom) : route('bom.material-lists.index')" variant="surface-muted" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<template id="bom-item-template">
    <article class="rounded-2xl border border-[#dadce0] bg-white p-4" data-bom-item-row>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold" data-bom-item-title>{{ __('bom.items') }}</h3>
            <button type="button" class="text-sm font-medium text-red-600" data-bom-remove-item>{{ __('bom.remove_item') }}</button>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-5">
            <label class="block text-sm font-medium text-[#5f6368]">
                {{ __('bom.line_no') }}
                <input type="number" min="1" name="items[__INDEX__][line_no]" value="1" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
            </label>

            <label class="block text-sm font-medium text-[#5f6368] md:col-span-2">
                {{ __('bom.component_product') }}
                <select name="items[__INDEX__][component_product_id]" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                    <option value="">{{ __('bom.component_product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->description ?? '—' }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-medium text-[#5f6368]">
                {{ __('bom.quantity_per') }}
                <input type="number" step="0.000001" min="0.000001" name="items[__INDEX__][quantity_per]" value="1" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
            </label>

            <label class="block text-sm font-medium text-[#5f6368]">
                {{ __('bom.scrap_factor') }}
                <input type="number" step="0.0001" min="0" name="items[__INDEX__][scrap_factor]" value="0" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3">
            </label>

            <label class="block text-sm font-medium text-[#5f6368]">
                {{ __('bom.uom') }}
                <input type="text" name="items[__INDEX__][uom]" value="" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" maxlength="20">
            </label>
        </div>
    </article>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-bom-items-container]');
    const template = document.getElementById('bom-item-template');
    const addButton = document.querySelector('[data-bom-add-item]');

    if (!container || !template || !addButton) {
        return;
    }

    const updateRow = (row, index) => {
        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(/items\[(?:__INDEX__|\d+)\]/, `items[${index}]`);
        });

        const title = row.querySelector('[data-bom-item-title]');

        if (title) {
            title.textContent = `{{ __('bom.items') }} ${index + 1}`;
        }
    };

    const resetRow = (row) => {
        row.querySelectorAll('input').forEach((input) => {
            if (input.type === 'number') {
                input.value = input.name.includes('line_no') ? '1' : (input.name.includes('quantity_per') ? '1' : '0');
            } else {
                input.value = '';
            }
        });

        row.querySelectorAll('select').forEach((select) => {
            select.selectedIndex = 0;
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
        container.appendChild(fragment);
        container.querySelectorAll('[data-bom-item-row]').forEach((currentRow, index) => updateRow(currentRow, index));
    });

    bindExistingRows();
});
</script>
@endsection