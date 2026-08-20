@extends('layouts.client-area')

@section('title', __('ui.module_products').' | '.__('ui.product_versions'))
@section('client-page-title', __('product.versions_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('product.versions_title') }}">
        <x-slot:actions>
        <div class="flex flex-wrap items-center gap-3">
            @if ($selectedProduct !== null)
                <x-ui.button :href="route('products.versions.create', $selectedProduct)" variant="primary" class="rounded-full">{{ __('product.version_create') }}</x-ui.button>
            @endif
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end" method="GET">
            <div>
                <label for="product_id" class="mb-2 block text-sm font-medium text-[var(--ui-text-muted)]">{{ __('product.choose_product') }}</label>
                <x-ui.select
                    id="product_id"
                    name="product_id"
                    data-search="on"
                    data-placeholder="{{ __('product.choose_product') }}"
                    data-ajax-url="{{ route('products.search') }}"
                    data-minimum-input-length="1"
                >
                    <option value="">{{ __('product.choose_product') }}</option>
                    @if ($selectedProduct !== null)
                        <option value="{{ $selectedProduct->id }}" selected>
                            {{ $selectedProduct->sku }} - {{ $selectedProduct->description ?? __('product.no_description') }}
                        </option>
                    @endif
                </x-ui.select>
            </div>
            <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('product.filter') }}</x-ui.button>
        </form>

        @if ($selectedProduct !== null)
            <div class="mt-6 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $selectedProduct->sku }}</h2>
                        <p class="text-sm text-[var(--ui-text-muted)]">{{ $selectedProduct->description ?? '—' }}</p>
                    </div>
                    <x-ui.definition-item-status
                        :label="__('product.status')"
                        :value="$selectedProduct->is_active ? __('product.active') : __('product.inactive')"
                        :tone="$selectedProduct->is_active ? 'success' : 'neutral'"
                        inline
                    />
                </div>

                <div class="mt-5 overflow-x-auto">
                    <x-ui.table :caption="__('product.versions_title')">
                        <thead>
                            <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                                <x-ui.sortable-header column="version_number" :label="__('product.version')" :sort="$sort" :direction="$direction" />
                                <x-ui.sortable-header column="status" :label="__('product.status')" :sort="$sort" :direction="$direction" />
                                <x-ui.sortable-header column="effective_from" :label="__('product.effective_from')" :sort="$sort" :direction="$direction" />
                                <x-ui.sortable-header column="effective_to" :label="__('product.effective_to')" :sort="$sort" :direction="$direction" />
                                <x-ui.sortable-header column="compatibility_rule" :label="__('product.compatibility_rule')" :sort="$sort" :direction="$direction" />
                                <x-ui.sortable-header column="change_summary" :label="__('product.change_summary')" :sort="$sort" :direction="$direction" />
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($versions as $version)
                                <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]">
                                    <td class="px-3 py-4 font-semibold"><a href="{{ route('products.versions.show', [$selectedProduct, $version]) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $version->version_number }}</a></td>
                                    <td class="px-3 py-4">
                                        <x-ui.definition-item-status
                                            :label="__('product.status')"
                                            :value="__('product.version_status_'.$version->status)"
                                            :tone="$version->status === 'APPROVED' ? 'success' : ($version->status === 'OBSOLETE' ? 'warning' : 'neutral')"
                                            inline
                                        />
                                    </td>
                                    <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $version->effective_from?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $version->effective_to?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $version->compatibility_rule }}</td>
                                    <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $version->change_summary ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('product.no_versions') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
            </div>
        @else
            <div class="mt-6 rounded-2xl border border-dashed border-[var(--ui-border)] bg-[var(--ui-surface-muted)] p-6 text-sm text-[var(--ui-text-muted)]">
                {{ __('product.choose_product_hint') }}
            </div>
        @endif
    </x-ui.panel>
</div>
@endsection
