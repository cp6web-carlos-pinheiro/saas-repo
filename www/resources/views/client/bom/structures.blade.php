@extends('layouts.client-area')

@section('title', __('ui.module_production_mrp').' | '.__('ui.bom_structures'))
@section('client-page-title', __('bom.structures_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('bom.structures_title') }}</h1>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="structure-search" class="sr-only">{{ __('bom.search') }}</label>
            <x-ui.input id="structure-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('bom.search') }}" />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('bom.filter') }}</x-ui.button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('bom.structures_title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="sku" :label="__('bom.product')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="bom_headers_count" :label="__('bom.total_revisions')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="approved_bom_headers_count" :label="__('bom.approved_revisions')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="bom_headers_max_version_number" :label="__('bom.latest_revision')" :sort="$sort" :direction="$direction" />
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($structures as $structure)
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-4">
                                <div class="font-semibold">{{ $structure->sku }}</div>
                                <div class="text-xs text-[var(--ui-text-muted)]">{{ $structure->description ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $structure->bom_headers_count }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $structure->approved_bom_headers_count }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $structure->bom_headers_max_version_number ?? '—' }}</td>
                            <td class="px-3 py-4 text-right">
                                <x-ui.button :href="route('bom.structures.show', $structure)" variant="secondary" class="rounded-full">{{ __('bom.open_structure') }}</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('bom.structures_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $structures->links() }}</div>
    </x-ui.panel>
</div>
@endsection
