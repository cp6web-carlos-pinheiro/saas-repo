@extends('layouts.client-area')

@section('title', $product->sku.' | '.__('bom.structures_title'))
@section('client-page-title', __('bom.structures_title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $product->sku }}</h1>
            <p class="mt-1 text-sm text-[#5f6368]">{{ $product->description ?? '—' }}</p>
        </div>
        <x-ui.button :href="route('bom.material-lists.create', ['product_id' => $product->id])" variant="brand-primary" class="rounded-full">{{ __('bom.create_revision') }}</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
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
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0" onclick="window.location='{{ route('bom.material-lists.show', $revision) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('bom.material-lists.show', $revision) }}'; }">
                            <td class="px-3 py-4 font-semibold">{{ $revision->version_number }}</td>
                            <td class="px-3 py-4">
                                <span class="rounded-full px-2 py-1 text-xs {{ $revision->status === 'DRAFT' ? 'bg-slate-100 text-slate-600' : ($revision->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ __('bom.status_'.$revision->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $revision->effective_from?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $revision->effective_to?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $revision->items_count }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $revision->description ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[#5f6368]">{{ __('bom.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('bom.structures.index')" variant="surface-muted" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
            <x-ui.button :href="route('bom.material-lists.index', ['search' => $product->sku])" variant="surface-muted" class="rounded-full">{{ __('ui.bom_revisions') }}</x-ui.button>
        </div>
    </x-ui.panel>
</div>
@endsection