@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_register'))
@section('client-page-title', __('product.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('product.title') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('products.create')" variant="brand-primary" class="rounded-full">{{ __('product.create') }}</x-ui.button>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="product-search" class="sr-only">{{ __('product.search') }}</label>
            <x-ui.input id="product-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('product.search') }}" />
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('product.filter') }}</x-ui.button>
        </form>

        @php($sortUrl = fn ($column) => route('products.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('sku') }}">{{ __('product.sku') }} ↕</a></th>
                        <th class="px-3 py-3">{{ __('product.description') }}</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('product_type') }}">{{ __('product.product_type') }} ↕</a></th>
                        <th class="px-3 py-3">{{ __('product.uom') }}</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('is_active') }}">{{ __('product.status') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('lead_time_days') }}">{{ __('product.lead_time_days') }} ↕</a></th>
                        <th class="px-3 py-3">{{ __('ui.product_versions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0" onclick="window.location='{{ route('products.show', $product) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('products.show', $product) }}'; }">
                            <td class="px-3 py-4 text-[#5f6368]">{{ $product->id }}</td>
                            <td class="px-3 py-4 font-semibold">{{ $product->sku }}</td>
                            <td class="px-3 py-4">{{ $product->description ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ __('product.types.'.$product->product_type) }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $product->uom ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('product.status')"
                                    :value="$product->is_active ? __('product.active') : __('product.inactive')"
                                    :tone="$product->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $product->lead_time_days }}</td>
                            <td class="px-3 py-4">
                                <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="material-versions" class="rounded-full">{{ __('ui.product_versions') }}</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[#5f6368]">{{ __('product.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </x-ui.panel>
</div>
@endsection