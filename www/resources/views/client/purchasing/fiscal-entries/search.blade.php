@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_fiscal_entry'))
@section('client-page-title', __('purchase_fiscal_entry.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('purchase_fiscal_entry.title') }}</h1>
        <x-ui.button :href="route('purchasing.fiscal-entries.create')" variant="brand-primary" class="rounded-full">{{ __('purchase_fiscal_entry.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="entry-search" class="sr-only">{{ __('purchase_fiscal_entry.search') }}</label>
            <x-ui.input id="entry-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('purchase_fiscal_entry.search') }}" />
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('purchase_fiscal_entry.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.fiscal-entries.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_fiscal_entry.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'DRAFT']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'DRAFT' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_fiscal_entry.status_draft') }}</a>
            <a href="{{ $filterUrl(['status' => 'POSTED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'POSTED' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_fiscal_entry.status_posted') }}</a>
            <a href="{{ $filterUrl(['status' => 'CANCELLED']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'CANCELLED' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('purchase_fiscal_entry.status_cancelled') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3">{{ __('purchase_fiscal_entry.number') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_fiscal_entry.document_number') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_fiscal_entry.supplier') }}</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('entry_date') }}">{{ __('purchase_fiscal_entry.entry_date') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('status') }}">{{ __('purchase_fiscal_entry.status') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('amount_cents') }}">{{ __('purchase_fiscal_entry.amount') }} ↕</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0" onclick="window.location='{{ route('purchasing.fiscal-entries.show', $entry) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('purchasing.fiscal-entries.show', $entry) }}'; }">
                            <td class="px-3 py-4 text-[#5f6368]">{{ $entry->id }}</td>
                            <td class="px-3 py-4">{{ $entry->entry_number }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $entry->document_number ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $entry->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $entry->entry_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ __('purchase_fiscal_entry.status_'.strtolower($entry->status)) }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ number_format(((int) $entry->amount_cents) / 100, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-10 text-center text-[#5f6368]">{{ __('purchase_fiscal_entry.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $entries->links() }}</div>
    </x-ui.panel>
</div>
@endsection
