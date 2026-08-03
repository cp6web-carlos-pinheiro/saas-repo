@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_register'))
@section('client-page-title', __('product.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $product ? __('product.edit') : __('product.create') }}</h1>
        </div>
        <x-ui.button :href="$product ? route('products.show', $product) : route('products.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form method="POST" action="{{ $product ? route('products.update', $product) : route('products.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf
            @if ($product)
                @method('PUT')
            @endif

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="sku">{{ __('product.sku') }}</label>
                <x-ui.input id="sku" name="sku" :value="old('sku', $product->sku ?? '')" required maxlength="80" />
                @error('sku')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="product_type">{{ __('product.product_type') }}</label>
                <x-ui.select id="product_type" name="product_type" required data-search="off">
                    @foreach (__('product.types') as $typeValue => $typeLabel)
                        <option value="{{ $typeValue }}" @selected(old('product_type', $product->product_type ?? 'FG') === $typeValue)>{{ $typeLabel }}</option>
                    @endforeach
                </x-ui.select>
                @error('product_type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="description">{{ __('product.description') }}</label>
                <x-ui.input id="description" name="description" :value="old('description', $product->description ?? '')" required maxlength="255" />
                @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="uom">{{ __('product.uom') }}</label>
                <x-ui.input id="uom" name="uom" :value="old('uom', $product->uom ?? '')" required maxlength="20" />
                @error('uom')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="safety_stock">{{ __('product.safety_stock') }}</label>
                <x-ui.input id="safety_stock" type="number" min="0" name="safety_stock" :value="old('safety_stock', $product->safety_stock ?? 0)" required />
                @error('safety_stock')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="lead_time_days">{{ __('product.lead_time_days') }}</label>
                <x-ui.input id="lead_time_days" type="number" min="0" name="lead_time_days" :value="old('lead_time_days', $product->lead_time_days ?? 0)" required />
                @error('lead_time_days')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-6 md:col-span-2">
                <label class="inline-flex items-center gap-2">
                    <x-ui.input type="hidden" name="lot_control" value="0" unstyled />
                    <x-ui.input type="checkbox" name="lot_control" value="1" @checked(old('lot_control', $product->lot_control ?? false)) unstyled />
                    <span>{{ __('product.lot_control') }}</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <x-ui.input type="hidden" name="serial_control" value="0" unstyled />
                    <x-ui.input type="checkbox" name="serial_control" value="1" @checked(old('serial_control', $product->serial_control ?? false)) unstyled />
                    <span>{{ __('product.serial_control') }}</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <x-ui.input type="hidden" name="is_active" value="0" unstyled />
                    <x-ui.input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) unstyled />
                    <span>{{ __('product.active') }}</span>
                </label>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('ui.save') }}</x-ui.button>
                <x-ui.button :href="$product ? route('products.show', $product) : route('products.index')" variant="surface-muted" class="rounded-full">{{ __('ui.cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection