@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_fiscal_entry'))
@section('client-page-title', __('purchase_fiscal_entry.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $entry->entry_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.fiscal-entries.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            @if ($entry->status === 'POSTED')
                <form method="POST" action="{{ route('purchasing.fiscal-entries.reverse', $entry) }}" data-admin-reverse-confirm data-reverse-title="{{ __('purchase_fiscal_entry.confirm_reverse_title') }}" data-reverse-text="{{ __('purchase_fiscal_entry.confirm_reverse_text') }}" data-reverse-confirm="{{ __('purchase_fiscal_entry.confirm_reverse_confirm') }}" data-reverse-cancel="{{ __('purchase_fiscal_entry.confirm_reverse_cancel') }}" data-reverse-category-label="{{ __('purchase_fiscal_entry.reverse_category') }}" data-reverse-category-required="{{ __('purchase_fiscal_entry.reverse_category_required') }}" data-reverse-category-quality="{{ __('purchase_fiscal_entry.reverse_category_quality') }}" data-reverse-category-fiscal="{{ __('purchase_fiscal_entry.reverse_category_fiscal') }}" data-reverse-category-supplier="{{ __('purchase_fiscal_entry.reverse_category_supplier') }}" data-reverse-category-master-data="{{ __('purchase_fiscal_entry.reverse_category_master_data') }}" data-reverse-reason-label="{{ __('purchase_fiscal_entry.reverse_reason') }}" data-reverse-reason-placeholder="{{ __('purchase_fiscal_entry.reverse_reason_placeholder') }}" data-reverse-reason-required="{{ __('purchase_fiscal_entry.reverse_reason_required') }}">
                    @csrf
                    <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_fiscal_entry.reverse') }}</x-ui.button>
                </form>
            @else
                <x-ui.button :href="route('purchasing.fiscal-entries.edit', $entry)" variant="material-edit" class="rounded-full">{{ __('purchase_fiscal_entry.edit') }}</x-ui.button>
                <form method="POST" action="{{ route('purchasing.fiscal-entries.destroy', $entry) }}" data-admin-delete-confirm data-admin-name="{{ $entry->entry_number }}" data-confirm-title="{{ __('purchase_fiscal_entry.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_fiscal_entry.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_fiscal_entry.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_fiscal_entry.confirm_delete_cancel') }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_fiscal_entry.remove') }}</x-ui.button>
                </form>
            @endif
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.reference')">#{{ $entry->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.number')">{{ $entry->entry_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.document_number')">{{ $entry->document_number ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.supplier')">{{ $entry->supplier?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.order')">{{ $entry->purchaseOrder?->purchase_order_number ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.status')">{{ __('purchase_fiscal_entry.status_'.strtolower($entry->status)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.issue_date')">{{ $entry->issue_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.entry_date')">{{ $entry->entry_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.amount')">{{ number_format(((int) $entry->amount_cents) / 100, 2, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.financial_reference')">{{ $entry->financial_reference ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.financial_posted_at')">{{ $entry->financial_posted_at?->format('d/m/Y H:i') ?? '—' }}</x-ui.definition-item>
            @php($reverseCategory = data_get($entry->metadata, 'reversal.category'))
            <x-ui.definition-item :label="__('purchase_fiscal_entry.reverse_category')">{{ $reverseCategory ? __('purchase_fiscal_entry.reverse_category_'.$reverseCategory) : '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.reverse_reason')">{{ data_get($entry->metadata, 'reversal.reason', '—') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_fiscal_entry.notes')">{{ $entry->notes ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('purchase_fiscal_entry.created_at')" :value="$entry->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
