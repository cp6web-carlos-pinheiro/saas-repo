@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_requisition'))
@section('client-page-title', __('purchase_requisition.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $requisition->requisition_number }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.requisitions.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.requisitions.edit', $requisition)" variant="primary" class="rounded-full">{{ __('purchase_requisition.edit') }}</x-ui.button>
            <x-ui.confirm-button :action="route('purchasing.requisitions.destroy', $requisition)" method="DELETE" class="rounded-full" :label="__('purchase_requisition.remove')" :confirm-title="__('purchase_requisition.confirm_delete_title')" :confirm-text="__('purchase_requisition.confirm_delete_text', ['name' => $requisition->requisition_number])" :confirm-label="__('purchase_requisition.confirm_delete_confirm')" :cancel-label="__('purchase_requisition.confirm_delete_cancel')" />
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
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
            <x-ui.table :caption="__('purchase_requisition.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
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
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-3">{{ $line->product?->sku }} - {{ $line->product?->description }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->warehouse?->code ?? '—' }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ number_format((float) $line->requested_quantity, 6, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->need_by_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->order_date?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
