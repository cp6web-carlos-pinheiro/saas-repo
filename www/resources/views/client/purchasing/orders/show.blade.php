@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_order'))
@section('client-page-title', __('purchase_order.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $order->purchase_order_number }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('purchasing.orders.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('purchasing.orders.edit', $order)" variant="primary" class="rounded-full">{{ __('purchase_order.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('purchasing.orders.destroy', $order) }}" data-admin-delete-confirm data-admin-name="{{ $order->purchase_order_number }}" data-confirm-title="{{ __('purchase_order.confirm_delete_title') }}" data-confirm-text="{{ __('purchase_order.confirm_delete_text') }}" data-confirm-confirm="{{ __('purchase_order.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('purchase_order.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('purchase_order.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
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

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('purchase_order.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-3">{{ __('purchase_order.product') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_order.warehouse') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_order.quantity') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_order.unit_price') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_order.need_by_date') }}</th>
                        <th class="px-3 py-3">{{ __('purchase_order.promised_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->lines as $line)
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-3">{{ $line->product?->sku }} - {{ $line->product?->description }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->warehouse?->code ?? '—' }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ number_format((float) $line->quantity_ordered, 6, ',', '.') }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->unit_price !== null ? number_format((float) $line->unit_price, 2, ',', '.') : '—' }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->need_by_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-3 text-[var(--ui-text-muted)]">{{ $line->promised_date?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
