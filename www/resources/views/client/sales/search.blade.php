@extends('layouts.client-area')

@section('title', __('ui.module_sales'))
@section('client-page-title', __('sale.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('sale.title') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('sales.create')" variant="primary" class="rounded-full">{{ __('sale.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="sale-search" class="sr-only">{{ __('sale.search') }}</label>
            <x-ui.input id="sale-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('sale.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.input type="hidden" name="operational_status" :value="$operationalStatus" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('sale.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('sales.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status, 'operational_status' => $operationalStatus], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'CONFIRMED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CONFIRMED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.status_confirmed') }}</a>
            <a href="{{ $filterUrl(['status' => 'CANCELLED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CANCELLED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.status_cancelled') }}</a>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['operational_status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $operationalStatus === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.all_operational_statuses') }}</a>
            <a href="{{ $filterUrl(['operational_status' => 'PENDING']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $operationalStatus === 'PENDING' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.operational_status_pending') }}</a>
            <a href="{{ $filterUrl(['operational_status' => 'PICKING']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $operationalStatus === 'PICKING' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.operational_status_picking') }}</a>
            <a href="{{ $filterUrl(['operational_status' => 'INVOICED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $operationalStatus === 'INVOICED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.operational_status_invoiced') }}</a>
            <a href="{{ $filterUrl(['operational_status' => 'SHIPPED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $operationalStatus === 'SHIPPED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.operational_status_shipped') }}</a>
            <a href="{{ $filterUrl(['operational_status' => 'DELIVERED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $operationalStatus === 'DELIVERED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('sale.operational_status_delivered') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('sale.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" :label="__('sale.reference')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="customer" :label="__('sale.customer')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="sale_date" :label="__('sale.sale_date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="amount_cents" :label="__('sale.amount')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('sale.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="operational_status" :label="__('sale.operational_status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('sale.created_at')" :sort="$sort" :direction="$direction" />
                        <th class="px-3 py-3 text-right">{{ __('sale.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr
                            class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                        >
                            <td class="px-3 py-4 font-medium"><a href="{{ route('sales.show', $sale) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">#{{ $sale->id }}</a></td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $sale->customer?->name ?? __('sale.customer_removed') }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td class="px-3 py-4 font-semibold">R$ {{ number_format($sale->amount_cents / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('sale.status')"
                                    :value="match ($sale->status) {
                                        'CONFIRMED' => __('sale.status_confirmed'),
                                        'CANCELLED' => __('sale.status_cancelled'),
                                        default => __('sale.status_draft'),
                                    }"
                                    :tone="match ($sale->status) {
                                        'CONFIRMED' => 'success',
                                        'CANCELLED' => 'danger',
                                        default => 'warning',
                                    }"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('sale.operational_status')"
                                    :value="match ($sale->operational_status) {
                                        'PICKING' => __('sale.operational_status_picking'),
                                        'INVOICED' => __('sale.operational_status_invoiced'),
                                        'SHIPPED' => __('sale.operational_status_shipped'),
                                        'DELIVERED' => __('sale.operational_status_delivered'),
                                        default => __('sale.operational_status_pending'),
                                    }"
                                    :tone="match ($sale->operational_status) {
                                        'DELIVERED' => 'success',
                                        'SHIPPED', 'INVOICED' => 'info',
                                        'PICKING' => 'warning',
                                        default => 'neutral',
                                    }"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $sale->created_at->format('d/m/Y') }}</td>
                            <td class="px-3 py-4 text-right" onclick="event.stopPropagation()" onkeydown="event.stopPropagation()">
                                <x-ui.button :href="route('sales.production-status', $sale)" variant="info" size="sm" class="rounded-full whitespace-nowrap">
                                    {{ __('sale.production_status.view') }}
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('sale.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $sales->links() }}</div>
    </x-ui.panel>
</div>
@endsection
