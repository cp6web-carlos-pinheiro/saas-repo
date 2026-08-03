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

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="structure-search" class="sr-only">{{ __('bom.search') }}</label>
            <x-ui.input id="structure-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('bom.search') }}" />
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('bom.filter') }}</x-ui.button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">{{ __('bom.product') }}</th>
                        <th class="px-3 py-3">{{ __('bom.total_revisions') }}</th>
                        <th class="px-3 py-3">{{ __('bom.approved_revisions') }}</th>
                        <th class="px-3 py-3">{{ __('bom.latest_revision') }}</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($structures as $structure)
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-4">
                                <div class="font-semibold">{{ $structure->sku }}</div>
                                <div class="text-xs text-[#5f6368]">{{ $structure->description ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $structure->bom_headers_count }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $structure->approved_bom_headers_count }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $structure->bom_headers_max_version_number ?? '—' }}</td>
                            <td class="px-3 py-4 text-right">
                                <x-ui.button :href="route('bom.structures.show', $structure)" variant="surface-muted" class="rounded-full">{{ __('bom.open_structure') }}</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[#5f6368]">{{ __('bom.structures_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $structures->links() }}</div>
    </x-ui.panel>
</div>
@endsection