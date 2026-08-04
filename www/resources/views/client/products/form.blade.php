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

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="lifecycle_status">{{ __('product.lifecycle_status') }}</label>
                <x-ui.select id="lifecycle_status" name="lifecycle_status" required data-search="off">
                    @foreach (__('product.lifecycle_statuses') as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected(old('lifecycle_status', $product->lifecycle_status ?? 'ACTIVE') === $statusValue)>{{ $statusLabel }}</option>
                    @endforeach
                </x-ui.select>
                @error('lifecycle_status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="description">{{ __('product.description') }}</label>
                <x-ui.input id="description" name="description" :value="old('description', $product->description ?? '')" required maxlength="255" />
                @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="unit_id">{{ __('product.unit_id') }}</label>
                <x-ui.select id="unit_id" name="unit_id" data-search="on" required>
                    <option value="">{{ __('product.select_unit') }}</option>
                    @foreach ($units as $id => $label)
                        <option value="{{ $id }}" @selected((string) old('unit_id', $product->unit_id ?? '') === (string) $id)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <p class="mt-2 text-sm text-[#5f6368]">{{ __('product.uom') }} será preenchida automaticamente com base na unidade selecionada.</p>
                @error('unit_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="category_id">{{ __('product.category_id') }}</label>
                <x-ui.select id="category_id" name="category_id" data-search="on">
                    <option value="">{{ __('product.select_category') }}</option>
                    @foreach ($categories as $id => $label)
                        <option value="{{ $id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $id)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="brand_id">{{ __('product.brand_id') }}</label>
                <x-ui.select id="brand_id" name="brand_id" data-search="on">
                    <option value="">{{ __('product.select_brand') }}</option>
                    @foreach ($brands as $id => $label)
                        <option value="{{ $id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $id)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                @error('brand_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
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

            <div class="grid gap-4 md:col-span-2 md:grid-cols-3">
                <label class="flex items-center gap-2 rounded-xl border border-[#dadce0] bg-white px-4 py-3 text-sm text-[#202124]">
                    <input type="hidden" name="lot_control" value="0" />
                    <input id="lot_control_checkbox" type="checkbox" name="lot_control" value="1" @checked(old('lot_control', $product->lot_control ?? false)) class="h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
                    <span>{{ __('product.lot_control') }}</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-[#dadce0] bg-white px-4 py-3 text-sm text-[#202124]">
                    <input type="hidden" name="serial_control" value="0" />
                    <input id="serial_control_checkbox" type="checkbox" name="serial_control" value="1" @checked(old('serial_control', $product->serial_control ?? false)) class="h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
                    <span>{{ __('product.serial_control') }}</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-[#dadce0] bg-white px-4 py-3 text-sm text-[#202124]">
                    <input type="hidden" name="is_active" value="0" />
                    <input id="is_active_checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
                    <span>{{ __('product.active') }}</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="alternate_uoms_json">{{ __('product.alternate_uoms') }}</label>
                <x-ui.textarea id="alternate_uoms_json" name="alternate_uoms_json" rows="3" class="font-mono text-sm">{{ old('alternate_uoms_json', json_encode($product->alternate_uoms ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
                @error('alternate_uoms_json')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="technical_attributes_json">{{ __('product.technical_attributes') }}</label>
                <x-ui.textarea id="technical_attributes_json" name="technical_attributes_json" rows="4" class="font-mono text-sm">{{ old('technical_attributes_json', json_encode($product->technical_attributes ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
                @error('technical_attributes_json')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="commercial_attributes_json">{{ __('product.commercial_attributes') }}</label>
                <x-ui.textarea id="commercial_attributes_json" name="commercial_attributes_json" rows="4" class="font-mono text-sm">{{ old('commercial_attributes_json', json_encode($product->commercial_attributes ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
                @error('commercial_attributes_json')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="image_urls_json">{{ __('product.image_urls') }}</label>
                <x-ui.textarea id="image_urls_json" name="image_urls_json" rows="3" class="font-mono text-sm">{{ old('image_urls_json', json_encode($product->image_urls ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
                @error('image_urls_json')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-[#5f6368]" for="attachment_urls_json">{{ __('product.attachment_urls') }}</label>
                <x-ui.textarea id="attachment_urls_json" name="attachment_urls_json" rows="3" class="font-mono text-sm">{{ old('attachment_urls_json', json_encode($product->attachment_urls ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
                @error('attachment_urls_json')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('ui.save') }}</x-ui.button>
                <x-ui.button :href="$product ? route('products.show', $product) : route('products.index')" variant="surface-muted" class="rounded-full">{{ __('ui.cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection