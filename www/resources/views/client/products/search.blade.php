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
            <x-ui.button :href="route('products.export', ['search' => $search, 'sort' => $sort, 'direction' => $direction])" variant="secondary" class="rounded-full">{{ __('product.export_xlsx') }}</x-ui.button>
            <x-ui.button :href="route('products.create')" variant="primary" class="rounded-full">{{ __('product.create') }}</x-ui.button>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="product-search" class="sr-only">{{ __('product.search') }}</label>
            <x-ui.input id="product-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('product.search') }}" />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('product.filter') }}</x-ui.button>
        </form>

        <div class="mt-5 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-[var(--ui-text-muted)]">{{ __('product.import_xlsx') }}</p>
                    <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ __('product.import_help') }}</p>
                </div>
            </div>

            <form class="mt-4 flex flex-wrap items-end gap-3" method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="min-w-0 flex-1">
                    <label class="mb-2 block text-sm font-medium text-[var(--ui-text-muted)]" for="products-xlsx">{{ __('product.import_file') }}</label>
                    <x-ui.input id="products-xlsx" type="file" name="file" accept=".xlsx" required class="w-full" />
                </div>
                <x-ui.button type="submit" variant="primary" class="rounded-xl">{{ __('product.import_xlsx') }}</x-ui.button>
            </form>

            @error('file')
                <p class="mt-3 text-sm text-[var(--ui-danger)]">{{ $message }}</p>
            @enderror
        </div>

        @php($sortUrl = fn ($column) => route('products.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('product.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="sku" :label="__('product.sku')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="description" :label="__('product.description')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="product_type" :label="__('product.product_type')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="uom" :label="__('product.uom')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="is_active" :label="__('product.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="lead_time_days" :label="__('product.lead_time_days')" :sort="$sort" :direction="$direction" />
                        <th class="px-3 py-3">{{ __('ui.product_versions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('products.show', $product) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $product->id }}</a></td>
                            <td class="px-3 py-4 font-semibold">{{ $product->sku }}</td>
                            <td class="px-3 py-4">{{ $product->description ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ __('product.types.'.$product->product_type) }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $product->uom ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('product.status')"
                                    :value="$product->is_active ? __('product.active') : __('product.inactive')"
                                    :tone="$product->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $product->lead_time_days }}</td>
                            <td class="px-3 py-4">
                                <x-ui.button :href="route('products.versions', ['product_id' => $product->id])" variant="info" class="rounded-full">{{ __('ui.product_versions') }}</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('product.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </x-ui.panel>
</div>
@endsection
