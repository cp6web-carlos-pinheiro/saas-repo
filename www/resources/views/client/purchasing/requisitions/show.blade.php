@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', __('purchase_requisition.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $requisition->requisition_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.requisitions.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.requisitions.edit', $requisition)" variant="material-edit" class="rounded-full">{{ __('purchase_requisition.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('purchasing.requisitions.destroy', $requisition) }}" data-admin-delete-confirm data-admin-name="{{ $requisition->requisition_number }}" data-confirm-title="{{ __('purchase_requisition.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_requisition.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_requisition.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_requisition.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_requisition.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('purchase_requisition.reference')">#{{ $requisition->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.number')">{{ $requisition->requisition_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.status')">{{ __('purchase_requisition.status_'.strtolower($requisition->status)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.required_date')">{{ $requisition->required_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.source_type')">{{ $requisition->source_type ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.lines_count')">{{ $requisition->lines_count }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_requisition.notes')">{{ $requisition->notes ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('purchase_requisition.created_at')" :value="$requisition->created_at" />
        </x-ui.definition-grid>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">{{ __('purchase_requisition.product') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_requisition.warehouse') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_requisition.supplier') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_requisition.quantity') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_requisition.need_by_date') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_requisition.order_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requisition->lines as $line)
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-3">{{ $line->product?->sku }} - {{ $line->product?->description }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->warehouse?->code ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ number_format((float) $line->requested_quantity, 6, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->need_by_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->order_date?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.panel>
</div>
@endsection
