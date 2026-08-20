@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', __('purchase_requisition.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('purchase_requisition.title') }}">
        <x-slot:actions>
        <x-ui.button :href="route('purchasing.requisitions.create')" variant="primary" class="rounded-full">{{ __('purchase_requisition.create') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="requisition-search" class="sr-only">{{ __('purchase_requisition.search') }}</label>
            <x-ui.input id="requisition-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('purchase_requisition.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('purchase_requisition.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.requisitions.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_requisition.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_requisition.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'APPROVED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'APPROVED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_requisition.status_approved') }}</a>
            <a href="{{ $filterUrl(['status' => 'CANCELLED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CANCELLED' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('purchase_requisition.status_cancelled') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('purchase_requisition.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="requisition_number" :label="__('purchase_requisition.number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="required_date" :label="__('purchase_requisition.required_date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('purchase_requisition.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="lines_count" :label="__('purchase_requisition.lines_count')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('purchase_requisition.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requisitions as $requisition)
                        <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('purchasing.requisitions.show', $requisition) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $requisition->id }}</a></td>
                            <td class="px-3 py-4">{{ $requisition->requisition_number }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $requisition->required_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ __('purchase_requisition.status_'.strtolower($requisition->status)) }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $requisition->lines_count }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $requisition->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('purchase_requisition.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $requisitions->links() }}</div>
    </x-ui.panel>
</div>
@endsection
