@extends('layouts.client-area')

@section('title', $product->sku.' | '.__('product.title'))
@section('client-page-title', __('product.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $product->sku }}</h1>
            <p class="mt-1 text-sm text-[#5f6368]">{{ $product->description }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $product->is_active ? __('product.active') : __('product.inactive') }}
        </span>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <dl class="divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.sku') }}</dt>
                <dd class="font-medium">{{ $product->sku }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.description') }}</dt>
                <dd class="font-medium">{{ $product->description }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.product_type') }}</dt>
                <dd class="font-medium">{{ __('product.types.'.$product->product_type) }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.uom') }}</dt>
                <dd class="font-medium">{{ $product->uom }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.safety_stock') }}</dt>
                <dd class="font-medium">{{ $product->safety_stock }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.lead_time_days') }}</dt>
                <dd class="font-medium">{{ $product->lead_time_days }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.lot_control') }}</dt>
                <dd class="font-medium">{{ $product->lot_control ? __('ui.yes') : __('ui.no') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.serial_control') }}</dt>
                <dd class="font-medium">{{ $product->serial_control ? __('ui.yes') : __('ui.no') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('product.created_at') }}</dt>
                <dd class="font-medium">{{ $product->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('products.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="surface-muted" class="rounded-full">{{ __('ui.product_versions') }}</x-ui.button>
            <x-ui.button :href="route('products.edit', $product)" variant="brand-primary" class="rounded-full">{{ __('product.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('products.destroy', $product) }}" data-admin-delete-confirm data-admin-name="{{ $product->sku }}" data-confirm-title="{{ __('product.confirm_delete_title') }}" data-confirm-text="{{ __('product.confirm_delete_text') }}" data-confirm-confirm="{{ __('product.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('product.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('product.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection