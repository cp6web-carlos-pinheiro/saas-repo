@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_register'))
@section('client-page-title', __('product.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $product->sku }}" subtitle="{{ $product->description }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('products.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="info" class="rounded-full">{{ __('ui.product_versions') }}</x-ui.button>
            <x-ui.button :href="route('products.edit', $product)" variant="primary" class="rounded-full">{{ __('product.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('products.destroy', $product) }}" data-admin-delete-confirm data-admin-name="{{ $product->sku }}" data-confirm-title="{{ __('product.confirm_delete_title') }}" data-confirm-text="{{ __('product.confirm_delete_text') }}" data-confirm-confirm="{{ __('product.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('product.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('product.remove') }}</x-ui.button>
            </form>
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('product.sku')">{{ $product->sku }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.product_type')">{{ __('product.types.'.$product->product_type) }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('product.description')">{{ $product->description }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.uom')">{{ $product->uom }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.safety_stock')">{{ $product->safety_stock }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.lead_time_days')">{{ $product->lead_time_days }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.lot_control')">{{ $product->lot_control ? __('ui.yes') : __('ui.no') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.serial_control')">{{ $product->serial_control ? __('ui.yes') : __('ui.no') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('product.lifecycle_status')">{{ __('product.lifecycle_statuses.'.$product->lifecycle_status) }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('product.status')" :value="$product->is_active ? __('product.active') : __('product.inactive')" :tone="$product->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date class="sm:col-span-2 xl:col-span-2" :label="__('product.created_at')" :value="$product->created_at" />
        </x-ui.definition-grid>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
                <h3 class="font-semibold">{{ __('product.technical_attributes') }}</h3>
                <pre class="mt-2 overflow-x-auto text-xs text-[var(--ui-text-muted)]">{{ json_encode($product->technical_attributes ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
                <h3 class="font-semibold">{{ __('product.commercial_attributes') }}</h3>
                <pre class="mt-2 overflow-x-auto text-xs text-[var(--ui-text-muted)]">{{ json_encode($product->commercial_attributes ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
                <h3 class="font-semibold">{{ __('product.alternate_uoms') }}</h3>
                <pre class="mt-2 overflow-x-auto text-xs text-[var(--ui-text-muted)]">{{ json_encode($product->alternate_uoms ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </x-ui.panel>
</div>
@endsection
