@extends('layouts.client-area')

@section('title', __('sale.materials.title'))
@section('client-page-title', __('sale.materials.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    @php
        $formatQuantity = static function (float|int $quantity): string {
            return rtrim(rtrim(number_format((float) $quantity, 6, ',', '.'), '0'), ',');
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#5f6368]">{{ __('sale.reference_label', ['id' => $sale->id]) }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold">{{ __('sale.materials.title') }}</h1>
            <p class="mt-2 text-sm text-[#5f6368]">{{ __('sale.materials.subtitle', ['customer' => $sale->customer?->name ?? __('sale.customer_removed')]) }}</p>
        </div>
        <x-ui.button :href="route('sales.show', $sale)" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    <x-ui.alert class="mt-6" variant="info">{{ __('sale.materials.analysis_hint', ['date' => \Illuminate\Support\Carbon::parse($analysis['reference_date'])->format('d/m/Y')]) }}</x-ui.alert>

    @if ($analysis['missing_boms'] !== [])
        <x-ui.alert class="mt-4" variant="warning">
            <div class="font-semibold">{{ __('sale.materials.missing_bom_title') }}</div>
            <div class="mt-1">{{ __('sale.materials.missing_bom_hint') }}</div>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($analysis['missing_boms'] as $product)
                    <li>{{ $product['sku'] }} - {{ $product['description'] }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @if ($analysis['cycles'] !== [])
        <x-ui.alert class="mt-4" variant="error">{{ __('sale.materials.bom_cycle') }}</x-ui.alert>
    @endif

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-sm text-[#5f6368]">{{ __('sale.materials.in_stock') }}</div>
            <div class="mt-1 text-3xl font-bold text-[#137333]">{{ $analysis['materials_in_stock_count'] }}</div>
        </x-ui.panel>
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-sm text-[#5f6368]">{{ __('sale.materials.to_buy') }}</div>
            <div class="mt-1 text-3xl font-bold text-[#b06000]">{{ $analysis['purchase_items_count'] }}</div>
        </x-ui.panel>
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-sm text-[#5f6368]">{{ __('sale.materials.to_produce') }}</div>
            <div class="mt-1 text-3xl font-bold text-[#174ea6]">{{ $analysis['production_items_count'] }}</div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-0">
        <div class="border-b border-[#dadce0] p-5 md:px-6">
            <h2 class="text-lg font-semibold">{{ __('sale.materials.sold_products') }}</h2>
            <p class="mt-1 text-sm text-[#5f6368]">{{ __('sale.materials.sold_products_hint') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-4 py-3">{{ __('sale.product') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.required') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.already_linked') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.available_to_link') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.need_to_produce') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.warehouses') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($analysis['finished_products'] as $product)
                        <tr class="border-b border-[#f1f3f4] align-top">
                            <td class="px-4 py-4 font-medium">{{ $product['sku'] }} - {{ $product['description'] }}</td>
                            <td class="px-4 py-4">{{ $formatQuantity($product['required_quantity']) }} {{ $product['unit'] }}</td>
                            <td class="px-4 py-4 text-[#137333]">{{ $formatQuantity($product['linked_quantity']) }} {{ $product['unit'] }}</td>
                            <td class="px-4 py-4 text-[#137333]">{{ $formatQuantity($product['available_to_link']) }} {{ $product['unit'] }}</td>
                            <td class="px-4 py-4 font-semibold {{ $product['quantity_to_produce'] > 0 ? 'text-[#174ea6]' : 'text-[#137333]' }}">{{ $formatQuantity($product['quantity_to_produce']) }} {{ $product['unit'] }}</td>
                            <td class="px-4 py-4 text-xs text-[#5f6368]">
                                @forelse ($product['warehouses'] as $warehouse)
                                    <div>{{ $warehouse['code'] }} · {{ $formatQuantity($warehouse['quantity']) }} {{ $product['unit'] }}</div>
                                @empty
                                    —
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.panel>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-0">
        <div class="border-b border-[#dadce0] p-5 md:px-6">
            <h2 class="text-lg font-semibold">{{ __('sale.materials.production_materials') }}</h2>
            <p class="mt-1 text-sm text-[#5f6368]">{{ __('sale.materials.production_materials_hint') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-4 py-3">{{ __('sale.product') }}</th>
                        <th class="px-4 py-3">{{ __('product.product_type') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.level') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.required') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.already_linked') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.available_to_link') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.shortage') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.recommendation') }}</th>
                        <th class="px-4 py-3">{{ __('sale.materials.warehouses') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analysis['materials'] as $material)
                        <tr class="border-b border-[#f1f3f4] align-top">
                            <td class="px-4 py-4 font-medium">{{ $material['sku'] }} - {{ $material['description'] }}</td>
                            <td class="px-4 py-4 text-[#5f6368]">{{ __('product.types.'.$material['product_type']) }}</td>
                            <td class="px-4 py-4 text-[#5f6368]">{{ $material['level'] }}</td>
                            <td class="px-4 py-4">{{ $formatQuantity($material['required_quantity']) }} {{ $material['unit'] }}</td>
                            <td class="px-4 py-4 text-[#137333]">{{ $formatQuantity($material['linked_quantity']) }} {{ $material['unit'] }}</td>
                            <td class="px-4 py-4 text-[#137333]">{{ $formatQuantity($material['available_to_link']) }} {{ $material['unit'] }}</td>
                            <td class="px-4 py-4 font-semibold {{ $material['shortage_quantity'] > 0 ? 'text-[#b06000]' : 'text-[#137333]' }}">{{ $formatQuantity($material['shortage_quantity']) }} {{ $material['unit'] }}</td>
                            <td class="px-4 py-4">
                                @if ($material['shortage_quantity'] <= 0)
                                    <span class="inline-flex rounded-full bg-[#e6f4ea] px-2.5 py-1 text-xs font-semibold text-[#137333]">{{ __('sale.materials.action_covered') }}</span>
                                @elseif ($material['recommended_action'] === 'BUY')
                                    <span class="inline-flex rounded-full bg-[#fef7e0] px-2.5 py-1 text-xs font-semibold text-[#b06000]">{{ __('sale.materials.action_buy') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-[#e8f0fe] px-2.5 py-1 text-xs font-semibold text-[#174ea6]">{{ __('sale.materials.action_produce') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-[#5f6368]">
                                @forelse ($material['warehouses'] as $warehouse)
                                    <div>{{ $warehouse['code'] }} · {{ $formatQuantity($warehouse['quantity']) }} {{ $material['unit'] }}</div>
                                @empty
                                    —
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-[#5f6368]">{{ __('sale.materials.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
</div>
@endsection
