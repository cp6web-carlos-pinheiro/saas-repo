@extends('layouts.client-area')

@section('title', __('ui.module_production_mrp').' | '.__('ui.bom_revisions'))
@section('client-page-title', __('bom.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $bom->product?->sku ?? __('bom.title') }}</h1>
            <p class="mt-1 text-sm text-[#5f6368]">{{ $bom->product?->description ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('bom.material-lists.index')" variant="material-back" class="rounded-full">{{ __('bom.back') }}</x-ui.button>
            <x-ui.button :href="route('bom.material-lists.edit', $bom)" variant="material-edit" class="rounded-full">{{ __('bom.edit') }}</x-ui.button>

            <form method="POST" action="{{ route('bom.material-lists.destroy', $bom) }}" data-admin-delete-confirm data-admin-name="{{ $bom->product?->sku ?? __('bom.title') }} v{{ $bom->version_number }}" data-confirm-title="{{ __('bom.confirm_delete_title') }}" data-confirm-text="{{ __('bom.confirm_delete_text') }}" data-confirm-confirm="{{ __('bom.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('bom.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('bom.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('bom.product')">{{ $bom->product?->sku ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('bom.version_number')">{{ $bom->version_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('bom.status')">{{ __('bom.status_'.$bom->status) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('bom.effective_from')">{{ $bom->effective_from?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('bom.effective_to')">{{ $bom->effective_to?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-3" :label="__('bom.description')">{{ $bom->description ?? '—' }}</x-ui.definition-item>
        </x-ui.definition-grid>

        <div class="mt-8 overflow-x-auto rounded-2xl border border-[#dadce0]">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">{{ __('bom.line_no') }}</th>
                        <th class="px-3 py-3">{{ __('bom.component_product') }}</th>
                        <th class="px-3 py-3">{{ __('bom.quantity_per') }}</th>
                        <th class="px-3 py-3">{{ __('bom.scrap_factor') }}</th>
                        <th class="px-3 py-3">{{ __('bom.uom') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bom->items as $item)
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-4 font-semibold">{{ $item->line_no }}</td>
                            <td class="px-3 py-4">
                                <div class="font-medium">{{ $item->componentProduct?->sku ?? '—' }}</div>
                                <div class="text-xs text-[#5f6368]">{{ $item->componentProduct?->description ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $item->quantity_per }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $item->scrap_factor }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $item->uom ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[#5f6368]">{{ __('bom.no_items') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </x-ui.panel>
</div>
@endsection