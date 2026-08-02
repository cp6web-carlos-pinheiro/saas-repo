@extends('layouts.client-area')

@section('title', __('product.versions_title').' | '.__('ui.app_name'))
@section('client-page-title', __('product.versions_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('product.versions_title') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($selectedProduct !== null)
                <x-ui.button :href="route('products.versions.create', $selectedProduct)" variant="brand-primary" class="rounded-full">{{ __('product.version_create') }}</x-ui.button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end" method="GET">
            <div>
                <label for="product_id" class="mb-2 block text-sm font-medium text-[#5f6368]">{{ __('product.choose_product') }}</label>
                <select id="product_id" name="product_id" class="w-full rounded-xl border border-[#dadce0] px-4 py-3">
                    <option value="">{{ __('product.choose_product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected($selectedProduct?->id === $product->id)>
                            {{ $product->sku }} - {{ $product->description ?? __('product.no_description') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('product.filter') }}</x-ui.button>
        </form>

        @if ($selectedProduct !== null)
            <div class="mt-6 rounded-2xl border border-[#dadce0] bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $selectedProduct->sku }}</h2>
                        <p class="text-sm text-[#5f6368]">{{ $selectedProduct->description ?? '—' }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs {{ $selectedProduct->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $selectedProduct->is_active ? __('product.active') : __('product.inactive') }}
                    </span>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                                <th class="px-3 py-3">{{ __('product.version') }}</th>
                                <th class="px-3 py-3">{{ __('product.status') }}</th>
                                <th class="px-3 py-3">{{ __('product.effective_from') }}</th>
                                <th class="px-3 py-3">{{ __('product.effective_to') }}</th>
                                <th class="px-3 py-3">{{ __('product.compatibility_rule') }}</th>
                                <th class="px-3 py-3">{{ __('product.change_summary') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($versions as $version)
                                <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0" onclick="window.location='{{ route('products.versions.show', [$selectedProduct, $version]) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('products.versions.show', [$selectedProduct, $version]) }}'; }">
                                    <td class="px-3 py-4 font-semibold">{{ $version->version_number }}</td>
                                    <td class="px-3 py-4">
                                        <span class="rounded-full px-2 py-1 text-xs {{ $version->status === 'DRAFT' ? 'bg-slate-100 text-slate-600' : ($version->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ __('product.version_status_'.$version->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-[#5f6368]">{{ $version->effective_from?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-3 py-4 text-[#5f6368]">{{ $version->effective_to?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-3 py-4 text-[#5f6368]">{{ $version->compatibility_rule }}</td>
                                    <td class="px-3 py-4 text-[#5f6368]">{{ $version->change_summary ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-[#5f6368]">{{ __('product.no_versions') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mt-6 rounded-2xl border border-dashed border-[#dadce0] bg-[#f8fafd] p-6 text-sm text-[#5f6368]">
                {{ __('product.choose_product_hint') }}
            </div>
        @endif
    </x-ui.panel>
</div>
@endsection