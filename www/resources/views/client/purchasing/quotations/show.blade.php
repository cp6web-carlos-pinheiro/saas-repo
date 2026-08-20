@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_quotation'))
@section('client-page-title', __('purchase_quotation.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $quotation->quotation_number }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.quotations.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.quotations.edit', $quotation)" variant="primary" class="rounded-full">{{ __('purchase_quotation.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('purchasing.quotations.destroy', $quotation) }}" data-admin-delete-confirm data-admin-name="{{ $quotation->quotation_number }}" data-confirm-title="{{ __('purchase_quotation.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_quotation.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_quotation.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_quotation.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('purchase_quotation.remove') }}</x-ui.button>
            </form>
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('purchase_quotation.reference')">#{{ $quotation->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.number')">{{ $quotation->quotation_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.supplier')">{{ $quotation->supplier?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.requisition')">{{ $quotation->requisition?->requisition_number ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.status')">{{ __('purchase_quotation.status_'.strtolower($quotation->status)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.quotation_date')">{{ $quotation->quotation_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.valid_until')">{{ $quotation->valid_until?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.amount')">{{ number_format(((int) $quotation->amount_cents) / 100, 2, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_quotation.notes')">{{ $quotation->notes ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('purchase_quotation.created_at')" :value="$quotation->created_at" />
        </x-ui.definition-grid>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('purchase_quotation.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-3">{{ __('purchase_quotation.product') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_quotation.quantity') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_quotation.unit_price') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_quotation.amount') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_quotation.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotation->lines as $line)
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-3">{{ $line->product?->sku }} - {{ $line->product?->description }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ number_format((float) $line->quantity, 6, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ number_format((float) $line->unit_price, 2, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ number_format(((float) $line->quantity * (float) $line->unit_price), 2, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
