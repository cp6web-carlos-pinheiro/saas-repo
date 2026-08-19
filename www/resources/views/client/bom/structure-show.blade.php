@extends('layouts.client-area')

@section('title', __('ui.module_production_mrp').' | '.__('ui.bom_structures'))
@section('client-page-title', __('bom.structures_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $product->sku }}</h1>
            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ $product->description ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('bom.structures.index')" variant="secondary" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
            <x-ui.button :href="route('bom.material-lists.index', ['search' => $product->sku])" variant="info" class="rounded-full">{{ __('ui.bom_revisions') }}</x-ui.button>
            <x-ui.button :href="route('bom.material-lists.create', ['product_id' => $product->id])" variant="primary" class="rounded-full">{{ __('bom.create_revision') }}</x-ui.button>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <div class="overflow-x-auto">
            <x-ui.table :caption="__('bom.items')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-3">{{ __('bom.version_number') }}</th>
                        <th class="px-3 py-3">{{ __('bom.status') }}</th>
                        <th class="px-3 py-3">{{ __('bom.effective_from') }}</th>
                        <th class="px-3 py-3">{{ __('bom.effective_to') }}</th>
                        <th class="px-3 py-3">{{ __('bom.items') }}</th>
                        <th class="px-3 py-3">{{ __('bom.description') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($revisions as $revision)
                        <tr class="cursor-pointer border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]" tabindex="0" onclick="window.location='{{ route('bom.material-lists.show', $revision) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('bom.material-lists.show', $revision) }}'; }">
                            <td class="px-3 py-4 font-semibold">{{ $revision->version_number }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('bom.status')"
                                    :value="__('bom.status_'.$revision->status)"
                                    :tone="$revision->status === 'APPROVED' ? 'success' : ($revision->status === 'OBSOLETE' ? 'warning' : 'neutral')"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $revision->effective_from?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $revision->effective_to?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $revision->items_count }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $revision->description ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('bom.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

    </x-ui.panel>
</div>
@endsection