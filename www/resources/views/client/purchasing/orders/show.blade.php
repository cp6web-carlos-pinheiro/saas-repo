@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_order'))
@section('client-page-title', __('purchase_order.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $order->purchase_order_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.orders.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.orders.edit', $order)" variant="material-edit" class="rounded-full">{{ __('purchase_order.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('purchasing.orders.destroy', $order) }}" data-admin-delete-confirm data-admin-name="{{ $order->purchase_order_number }}" data-confirm-title="{{ __('purchase_order.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_order.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_order.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_order.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('purchase_order.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('purchase_order.reference')">#{{ $order->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.number')">{{ $order->purchase_order_number }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.supplier')">{{ $order->supplier?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.requisition')">{{ $order->requisition?->requisition_number ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.status')">{{ __('purchase_order.status_'.strtolower($order->status)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.order_date')">{{ $order->order_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.expected_delivery_date')">{{ $order->expected_delivery_date?->format('d/m/Y') ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.lines_count')">{{ $order->lines_count }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('purchase_order.notes')">{{ $order->notes ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('purchase_order.created_at')" :value="$order->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
