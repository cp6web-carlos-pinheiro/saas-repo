@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_receipt'))
@section('client-page-title', __('purchase_receipt.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $receipt->receipt_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.receipts.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            @if ($receipt->status === 'POSTED')
                <form method="POST" action="{{ route('purchasing.receipts.reverse', $receipt) }}" data-admin-reverse-confirm data-reverse-title="{{ __('purchase_receipt.confirm_reverse_title') }}" data-reverse-text="{{ __('purchase_receipt.confirm_reverse_text') }}" data-reverse-confirm="{{ __('purchase_receipt.confirm_reverse_confirm') }}" data-reverse-cancel="{{ __('purchase_receipt.confirm_reverse_cancel') }}" data-reverse-category-label="{{ __('purchase_receipt.reverse_category') }}" data-reverse-category-required="{{ __('purchase_receipt.reverse_category_required') }}" data-reverse-category-fiscal="{{ __('purchase_receipt.reverse_category_fiscal') }}" data-reverse-category-supplier="{{ __('purchase_receipt.reverse_category_supplier') }}" data-reverse-category-master-data="{{ __('purchase_receipt.reverse_category_master_data') }}" data-reverse-reason-label="{{ __('purchase_receipt.reverse_reason') }}" data-reverse-reason-placeholder="{{ __('purchase_receipt.reverse_reason_placeholder') }}" data-reverse-reason-required="{{ __('purchase_receipt.reverse_reason_required') }}">
                    @csrf
                    <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_receipt.reverse') }}</x-ui.button>
                </form>
            @else
                <x-ui.button :href="route('purchasing.receipts.edit', $receipt)" variant="material-edit" class="rounded-full">{{ __('purchase_receipt.edit') }}</x-ui.button>
                <form method="POST" action="{{ route('purchasing.receipts.destroy', $receipt) }}" data-admin-delete-confirm data-admin-name="{{ $receipt->receipt_number }}" data-confirm-title="{{ __('purchase_receipt.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_receipt.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_receipt.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_receipt.confirm_delete_cancel') }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_receipt.remove') }}</x-ui.button>
                </form>
            @endif
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('purchase_receipt.reference')">#{{ $receipt->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.number')">{{ $receipt->receipt_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.supplier')">{{ $receipt->supplier?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.order')">{{ $receipt->purchaseOrder?->purchase_order_number ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.status')">{{ __('purchase_receipt.status_'.strtolower($receipt->status)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.receipt_date')">{{ $receipt->receipt_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            @php($reverseCategory = data_get($receipt->metadata, 'reversal.category'))
            <x-ui.definition-item :label="__('purchase_receipt.reverse_category')">{{ $reverseCategory ? __('purchase_receipt.reverse_category_'.$reverseCategory) : '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.reverse_reason')">{{ data_get($receipt->metadata, 'reversal.reason', '—') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_receipt.notes')">{{ $receipt->notes ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('purchase_receipt.created_at')" :value="$receipt->created_at" />
        </x-ui.definition-grid>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">{{ __('purchase_receipt.product') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_receipt.warehouse') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_receipt.quantity_received') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_receipt.lot_number') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_receipt.stock_movement') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->lines as $line)
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-3">{{ $line->product?->sku }} - {{ $line->product?->description }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->warehouse?->code ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ number_format((float) $line->quantity_received, 6, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->lot_number ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#5f6368]">{{ $line->stock_ledger_movement_id ? '#'.$line->stock_ledger_movement_id : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.panel>
</div>
@endsection
