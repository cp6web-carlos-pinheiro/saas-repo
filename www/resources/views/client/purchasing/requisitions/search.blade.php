@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', __('purchase_requisition.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('purchase_requisition.title') }}</h1>
        <x-ui.button :href="route('purchasing.requisitions.create')" variant="brand-primary" class="rounded-full">{{ __('purchase_requisition.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="requisition-search" class="sr-only">{{ __('purchase_requisition.search') }}</label>
            <x-ui.input id="requisition-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('purchase_requisition.search') }}" />
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('purchase_requisition.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.requisitions.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_requisition.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_requisition.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'APPROVED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'APPROVED' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_requisition.status_approved') }}</a>
            <a href="{{ $filterUrl(['status' => 'CANCELLED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CANCELLED' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_requisition.status_cancelled') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
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
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0" onclick="window.location='{{ route('purchasing.requisitions.show', $requisition) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('purchasing.requisitions.show', $requisition) }}'; }">
                            <td class="px-3 py-4 text-[#5f6368]">{{ $requisition->id }}</td>
                            <td class="px-3 py-4">{{ $requisition->requisition_number }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $requisition->required_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ __('purchase_requisition.status_'.strtolower($requisition->status)) }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $requisition->lines_count }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $requisition->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[#5f6368]">{{ __('purchase_requisition.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $requisitions->links() }}</div>
    </x-ui.panel>
</div>
@endsection
