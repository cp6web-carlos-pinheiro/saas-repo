@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_receipt'))
@section('client-page-title', __('purchase_receipt.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('purchase_receipt.title') }}">
        <x-slot:actions>
        <x-ui.button :href="route('purchasing.receipts.create')" variant="primary" class="rounded-full">{{ __('purchase_receipt.create') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="receipt-search" class="sr-only">{{ __('purchase_receipt.search') }}</label>
            <x-ui.input id="receipt-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('purchase_receipt.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('purchase_receipt.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.receipts.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_receipt.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_receipt.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'POSTED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'POSTED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_receipt.status_posted') }}</a>
            <a href="{{ $filterUrl(['status' => 'CANCELLED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CANCELLED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_receipt.status_cancelled') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('purchase_receipt.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="receipt_number" :label="__('purchase_receipt.number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="supplier" :label="__('purchase_receipt.supplier')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="purchase_order" :label="__('purchase_receipt.order')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="receipt_date" :label="__('purchase_receipt.receipt_date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('purchase_receipt.status')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('purchasing.receipts.show', $receipt) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $receipt->id }}</a></td>
                            <td class="px-3 py-4">{{ $receipt->receipt_number }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $receipt->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $receipt->purchaseOrder?->purchase_order_number ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $receipt->receipt_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ __('purchase_receipt.status_'.strtolower($receipt->status)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('purchase_receipt.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $receipts->links() }}</div>
    </x-ui.panel>
</div>
@endsection
