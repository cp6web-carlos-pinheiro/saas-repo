@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_order'))
@section('client-page-title', __('purchase_order.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('purchase_order.title') }}</h1>
        <x-ui.button :href="route('purchasing.orders.create')" variant="primary" class="rounded-full">{{ __('purchase_order.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="order-search" class="sr-only">{{ __('purchase_order.search') }}</label>
            <x-ui.input id="order-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('purchase_order.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('purchase_order.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.orders.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_order.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_order.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'APPROVED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'APPROVED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_order.status_approved') }}</a>
            <a href="{{ $filterUrl(['status' => 'CANCELLED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CANCELLED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_order.status_cancelled') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('purchase_order.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="purchase_order_number" :label="__('purchase_order.number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="supplier" :label="__('purchase_order.supplier')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="order_date" :label="__('purchase_order.order_date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('purchase_order.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="lines_count" :label="__('purchase_order.lines_count')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('purchase_order.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="cursor-pointer border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]" tabindex="0" onclick="window.location='{{ route('purchasing.orders.show', $order) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('purchasing.orders.show', $order) }}'; }">
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $order->id }}</td>
                            <td class="px-3 py-4">{{ $order->purchase_order_number }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $order->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $order->order_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ __('purchase_order.status_'.strtolower($order->status)) }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $order->lines_count }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('purchase_order.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </x-ui.panel>
</div>
@endsection
