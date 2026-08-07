@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_orders'))
@section('client-page-title', __('ui.production_orders'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.production_orders') }}</h1>
        <x-ui.button :href="route('production.orders.create')" variant="brand-primary" class="rounded-full">{{ __('production.orders.new_short') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="grid gap-3 md:grid-cols-3" method="GET">
            <x-ui.input name="search" :value="$search" :placeholder="__('production.orders.search')" />
            <x-ui.select name="status" data-search="off">
                <option value="">{{ __('production.all_statuses') }}</option>
                @foreach (['DRAFT','RELEASED','IN_PROGRESS','PARTIALLY_COMPLETED','COMPLETED','CANCELLED'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ __('production.status_labels.'.$option) }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('production.filter') }}</x-ui.button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <x-ui.sortable-header column="order_number" :label="__('production.orders.number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="sales_order" :label="__('production.orders.sale_number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="product" :label="__('production.product')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="warehouse" :label="__('production.warehouse')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="quantity_planned" :label="__('production.orders.planned')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="quantity_produced" :label="__('production.orders.produced')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="quantity_scrapped" :label="__('production.orders.scrap')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('production.status')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('production.orders.show', $order) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('production.orders.show', $order) }}'; }"
                        >
                            <td class="px-3 py-4 font-medium text-[#174ea6]">{{ $order->order_number }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $order->sales_order_reference ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <div>{{ $order->product?->description ?? '—' }}</div>
                                <div class="text-xs text-[#5f6368]">{{ $order->product?->sku ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $order->warehouse?->code }} {{ $order->warehouse?->name ? '- '.$order->warehouse->name : '' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ number_format((float) $order->quantity_planned, 3, ',', '.') }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ number_format((float) $order->quantity_produced, 3, ',', '.') }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ number_format((float) $order->quantity_scrapped, 3, ',', '.') }}</td>
                            <td class="px-3 py-4"><span class="rounded-full border border-[#dadce0] px-2.5 py-1 text-xs">{{ __('production.status_labels.'.$order->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[#5f6368]">{{ __('production.orders.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </x-ui.panel>
</div>
@endsection
