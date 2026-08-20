@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_quotation'))
@section('client-page-title', __('purchase_quotation.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('purchase_quotation.title') }}">
        <x-slot:actions>
        <x-ui.button :href="route('purchasing.quotations.create')" variant="primary" class="rounded-full">{{ __('purchase_quotation.create') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="quotation-search" class="sr-only">{{ __('purchase_quotation.search') }}</label>
            <x-ui.input id="quotation-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('purchase_quotation.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('purchase_quotation.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.quotations.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_quotation.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_quotation.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'RECEIVED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'RECEIVED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_quotation.status_received') }}</a>
            <a href="{{ $filterUrl(['status' => 'APPROVED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'APPROVED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_quotation.status_approved') }}</a>
            <a href="{{ $filterUrl(['status' => 'REJECTED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'REJECTED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_quotation.status_rejected') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('purchase_quotation.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="quotation_number" :label="__('purchase_quotation.number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="supplier" :label="__('purchase_quotation.supplier')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="quotation_date" :label="__('purchase_quotation.quotation_date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('purchase_quotation.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="amount_cents" :label="__('purchase_quotation.amount')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('purchasing.quotations.show', $quotation) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $quotation->id }}</a></td>
                            <td class="px-3 py-4">{{ $quotation->quotation_number }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $quotation->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $quotation->quotation_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ __('purchase_quotation.status_'.strtolower($quotation->status)) }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ number_format(((int) $quotation->amount_cents) / 100, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('purchase_quotation.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $quotations->links() }}</div>
    </x-ui.panel>
</div>
@endsection
