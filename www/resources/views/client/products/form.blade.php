@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_register'))
@section('client-page-title', __('product.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $product ? __('product.edit') : __('product.create') }}</h1>
        </div>
        <x-ui.button :href="$product ? route('products.show', $product) : route('products.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form method="POST" action="{{ $product ? route('products.update', $product) : route('products.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf
            @if ($product)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('product.sku') }}" for="sku" :required="true" :error="$errors->first('sku')">
                <x-ui.input id="sku" name="sku" :value="old('sku', $product->sku ?? '')" required maxlength="80"  :aria-describedby="$errors->has('sku') ? 'sku-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('product.product_type') }}" for="product_type" :required="true" :error="$errors->first('product_type')">
                <x-ui.select id="product_type" name="product_type" required data-search="off" :aria-describedby="$errors->has('product_type') ? 'product_type-error' : null">
                    @foreach (__('product.types') as $typeValue => $typeLabel)
                        <option value="{{ $typeValue }}" @selected(old('product_type', $product->product_type ?? 'FG') === $typeValue)>{{ $typeLabel }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="{{ __('product.lifecycle_status') }}" for="lifecycle_status" :required="true" :error="$errors->first('lifecycle_status')">
                <x-ui.select id="lifecycle_status" name="lifecycle_status" required data-search="off" :aria-describedby="$errors->has('lifecycle_status') ? 'lifecycle_status-error' : null">
                    @foreach (__('product.lifecycle_statuses') as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected(old('lifecycle_status', $product->lifecycle_status ?? 'ACTIVE') === $statusValue)>{{ $statusLabel }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.description') }}" for="description" :required="true" :error="$errors->first('description')">
                <x-ui.input id="description" name="description" :value="old('description', $product->description ?? '')" required maxlength="255"  :aria-describedby="$errors->has('description') ? 'description-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('product.unit_id') }}" for="unit_id" :required="true" hint="{{ __('product.uom_auto_fill_hint') }}" :error="$errors->first('unit_id')">
                <x-ui.select id="unit_id" name="unit_id" data-search="on" required :aria-describedby="$errors->has('unit_id') ? 'unit_id-error' : 'unit_id-hint'">
                    <option value="">{{ __('product.select_unit') }}</option>
                    @foreach ($units as $id => $label)
                        <option value="{{ $id }}" @selected((string) old('unit_id', $product->unit_id ?? '') === (string) $id)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="{{ __('product.category_id') }}" for="category_id" :error="$errors->first('category_id')">
                <x-ui.select id="category_id" name="category_id" data-search="on" :aria-describedby="$errors->has('category_id') ? 'category_id-error' : null">
                    <option value="">{{ __('product.select_category') }}</option>
                    @foreach ($categories as $id => $label)
                        <option value="{{ $id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $id)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="{{ __('product.brand_id') }}" for="brand_id" :error="$errors->first('brand_id')">
                <x-ui.select id="brand_id" name="brand_id" data-search="on" :aria-describedby="$errors->has('brand_id') ? 'brand_id-error' : null">
                    <option value="">{{ __('product.select_brand') }}</option>
                    @foreach ($brands as $id => $label)
                        <option value="{{ $id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $id)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="{{ __('product.safety_stock') }}" for="safety_stock" :required="true" :error="$errors->first('safety_stock')">
                <x-ui.input id="safety_stock" type="number" min="0" name="safety_stock" :value="old('safety_stock', $product->safety_stock ?? 0)" required  :aria-describedby="$errors->has('safety_stock') ? 'safety_stock-error' : null"/>
            </x-ui.field>

            <x-ui.field label="{{ __('product.lead_time_days') }}" for="lead_time_days" :required="true" :error="$errors->first('lead_time_days')">
                <x-ui.input id="lead_time_days" type="number" min="0" name="lead_time_days" :value="old('lead_time_days', $product->lead_time_days ?? 0)" required  :aria-describedby="$errors->has('lead_time_days') ? 'lead_time_days-error' : null"/>
            </x-ui.field>

            <div class="grid gap-4 md:col-span-2 md:grid-cols-3">
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface)] px-4 py-3">
                    <x-ui.input type="hidden" name="lot_control" value="0" unstyled />
                    <x-ui.checkbox id="lot_control_checkbox" name="lot_control" value="1" :checked="(bool) old('lot_control', $product->lot_control ?? false)">{{ __('product.lot_control') }}</x-ui.checkbox>
                </div>
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface)] px-4 py-3">
                    <x-ui.input type="hidden" name="serial_control" value="0" unstyled />
                    <x-ui.checkbox id="serial_control_checkbox" name="serial_control" value="1" :checked="(bool) old('serial_control', $product->serial_control ?? false)">{{ __('product.serial_control') }}</x-ui.checkbox>
                </div>
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-surface)] px-4 py-3">
                    <x-ui.input type="hidden" name="is_active" value="0" unstyled />
                    <x-ui.checkbox id="is_active_checkbox" name="is_active" value="1" :checked="(bool) old('is_active', $product->is_active ?? true)">{{ __('product.active') }}</x-ui.checkbox>
                </div>
            </div>

            <x-ui.field class="md:col-span-2" label="{{ __('product.alternate_uoms') }}" for="alternate_uoms_json" :error="$errors->first('alternate_uoms_json')">
                <x-ui.textarea id="alternate_uoms_json" name="alternate_uoms_json" rows="3" class="font-mono text-sm" :aria-describedby="$errors->has('alternate_uoms_json') ? 'alternate_uoms_json-error' : null">{{ old('alternate_uoms_json', json_encode($product->alternate_uoms ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.technical_attributes') }}" for="technical_attributes_json" :error="$errors->first('technical_attributes_json')">
                <x-ui.textarea id="technical_attributes_json" name="technical_attributes_json" rows="4" class="font-mono text-sm" :aria-describedby="$errors->has('technical_attributes_json') ? 'technical_attributes_json-error' : null">{{ old('technical_attributes_json', json_encode($product->technical_attributes ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.commercial_attributes') }}" for="commercial_attributes_json" :error="$errors->first('commercial_attributes_json')">
                <x-ui.textarea id="commercial_attributes_json" name="commercial_attributes_json" rows="4" class="font-mono text-sm" :aria-describedby="$errors->has('commercial_attributes_json') ? 'commercial_attributes_json-error' : null">{{ old('commercial_attributes_json', json_encode($product->commercial_attributes ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.image_urls') }}" for="image_urls_json" :error="$errors->first('image_urls_json')">
                <x-ui.textarea id="image_urls_json" name="image_urls_json" rows="3" class="font-mono text-sm" :aria-describedby="$errors->has('image_urls_json') ? 'image_urls_json-error' : null">{{ old('image_urls_json', json_encode($product->image_urls ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field class="md:col-span-2" label="{{ __('product.attachment_urls') }}" for="attachment_urls_json" :error="$errors->first('attachment_urls_json')">
                <x-ui.textarea id="attachment_urls_json" name="attachment_urls_json" rows="3" class="font-mono text-sm" :aria-describedby="$errors->has('attachment_urls_json') ? 'attachment_urls_json-error' : null">{{ old('attachment_urls_json', json_encode($product->attachment_urls ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</x-ui.textarea>
            </x-ui.field>

            <div class="flex flex-wrap gap-3 md:col-span-2">
                <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('ui.save') }}</x-ui.button>
                <x-ui.button :href="$product ? route('products.show', $product) : route('products.index')" variant="secondary" class="rounded-full">{{ __('ui.cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
