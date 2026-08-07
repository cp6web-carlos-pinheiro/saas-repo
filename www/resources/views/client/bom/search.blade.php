@extends('layouts.client-area')

@section('title', __('ui.module_production_mrp').' | '.__('ui.bom_revisions'))
@section('client-page-title', __('ui.bom_revisions'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('ui.bom_revisions') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('bom.material-lists.create')" variant="brand-primary" class="rounded-full">{{ __('bom.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="bom-search" class="sr-only">{{ __('bom.search') }}</label>
            <x-ui.input id="bom-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('bom.search') }}" />
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('bom.filter') }}</x-ui.button>
        </form>

        @php($sortUrl = fn ($column) => route('bom.material-lists.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="product" :label="__('bom.product')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="version_number" :label="__('bom.version_number')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('bom.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="effective_from" :label="__('bom.effective_from')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="effective_to" :label="__('bom.effective_to')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="items_count" :label="__('bom.items')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="description" :label="__('bom.description')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($boms as $bom)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0" onclick="window.location='{{ route('bom.material-lists.show', $bom) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('bom.material-lists.show', $bom) }}'; }">
                            <td class="px-3 py-4 text-[#5f6368]">{{ $bom->id }}</td>
                            <td class="px-3 py-4">
                                <div class="font-semibold">{{ $bom->product?->sku ?? '—' }}</div>
                                <div class="text-xs text-[#5f6368]">{{ $bom->product?->description ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-4 font-semibold">{{ $bom->version_number }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('bom.status')"
                                    :value="__('bom.status_'.$bom->status)"
                                    :tone="$bom->status === 'APPROVED' ? 'success' : ($bom->status === 'OBSOLETE' ? 'warning' : 'neutral')"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $bom->effective_from?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $bom->effective_to?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $bom->items_count }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $bom->description ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[#5f6368]">{{ __('bom.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $boms->links() }}</div>
    </x-ui.panel>
</div>
@endsection
