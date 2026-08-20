@extends('layouts.client-area')

@section('title', __('ui.module_sales'))
@section('client-page-title', __('sale.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    @php
        $isLockedForEditing = in_array($sale->operational_status, ['INVOICED', 'SHIPPED', 'DELIVERED'], true);
        $nextOperationalStatus = match (true) {
            $sale->status !== 'CONFIRMED' => null,
            $sale->operational_status === 'PENDING' => 'PICKING',
            $sale->operational_status === 'PICKING' => 'INVOICED',
            $sale->operational_status === 'INVOICED' => 'SHIPPED',
            $sale->operational_status === 'SHIPPED' => 'DELIVERED',
            default => null,
        };

        $nextOperationalStatusLabel = match ($nextOperationalStatus) {
            'PICKING' => __('sale.operational_status_picking'),
            'INVOICED' => __('sale.operational_status_invoiced'),
            'SHIPPED' => __('sale.operational_status_shipped'),
            'DELIVERED' => __('sale.operational_status_delivered'),
            default => null,
        };
    @endphp

    <x-ui.page-heading title="{{ __('sale.reference_label', ['id' => $sale->id]) }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('sales.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>

            <x-ui.button :href="route('sales.production-status', $sale)" variant="info" class="rounded-full">{{ __('sale.production_status.view') }}</x-ui.button>

            @if (! $isLockedForEditing)
                <x-ui.button :href="route('sales.edit', $sale)" variant="primary" class="rounded-full">{{ __('sale.edit') }}</x-ui.button>
            @endif

            @if ($nextOperationalStatus !== null)
                <form method="POST" action="{{ route('sales.transition', $sale) }}">
                    @csrf
                    <x-ui.input type="hidden" name="next_operational_status" :value="$nextOperationalStatus" unstyled />
                    <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('sale.advance_to', ['status' => $nextOperationalStatusLabel]) }}</x-ui.button>
                </form>
            @endif

            @if (! $isLockedForEditing)
                <form method="POST" action="{{ route('sales.destroy', $sale) }}" data-admin-delete-confirm data-admin-name="{{ __('sale.reference_label', ['id' => $sale->id]) }}" data-confirm-title="{{ __('sale.confirm_delete_title') }}" data-confirm-text="{{ __('sale.confirm_delete_text') }}" data-confirm-confirm="{{ __('sale.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('sale.confirm_delete_cancel') }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" class="rounded-full">{{ __('sale.remove') }}</x-ui.button>
                </form>
            @endif
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert class="mt-5" variant="error">{{ session('error') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('sale.reference')">#{{ $sale->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('sale.customer')">{{ $sale->customer?->name ?? __('sale.customer_removed') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('sale.sale_date')">{{ $sale->sale_date->format('d/m/Y') }}</x-ui.definition-item>
            <x-ui.definition-item-status
                :label="__('sale.status')"
                :value="match ($sale->status) {
                    'CONFIRMED' => __('sale.status_confirmed'),
                    'CANCELLED' => __('sale.status_cancelled'),
                    default => __('sale.status_draft'),
                }"
                :tone="match ($sale->status) {
                    'CONFIRMED' => 'success',
                    'CANCELLED' => 'danger',
                    default => 'warning',
                }"
            />
            <x-ui.definition-item-status
                :label="__('sale.operational_status')"
                :value="match ($sale->operational_status) {
                    'PICKING' => __('sale.operational_status_picking'),
                    'INVOICED' => __('sale.operational_status_invoiced'),
                    'SHIPPED' => __('sale.operational_status_shipped'),
                    'DELIVERED' => __('sale.operational_status_delivered'),
                    default => __('sale.operational_status_pending'),
                }"
                :tone="match ($sale->operational_status) {
                    'DELIVERED' => 'success',
                    'SHIPPED', 'INVOICED' => 'info',
                    'PICKING' => 'warning',
                    default => 'neutral',
                }"
            />
            <x-ui.definition-item-money :label="__('sale.subtotal')" :amount-cents="$sale->subtotal_cents" />
            <x-ui.definition-item-money :label="__('sale.discount_amount')" :amount-cents="$sale->discount_cents" />
            <x-ui.definition-item-money :label="__('sale.amount')" :amount-cents="$sale->amount_cents" />
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('sale.notes')">{{ $sale->notes !== null && $sale->notes !== '' ? $sale->notes : '—' }}</x-ui.definition-item>
            <x-ui.definition-item class="sm:col-span-2 xl:col-span-1" :label="__('sale.cancel_reason')">{{ $sale->cancel_reason !== null && $sale->cancel_reason !== '' ? $sale->cancel_reason : '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('sale.created_at')" :value="$sale->created_at" />
            <x-ui.definition-item-date :label="__('sale.confirmed_at')" :value="$sale->confirmed_at" />
            <x-ui.definition-item :label="__('sale.confirmed_by')">{{ $sale->confirmedBy?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('sale.canceled_at')" :value="$sale->canceled_at" />
            <x-ui.definition-item :label="__('sale.canceled_by')">{{ $sale->canceledBy?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('sale.picking_at')" :value="$sale->picking_at" />
            <x-ui.definition-item :label="__('sale.picking_by')">{{ $sale->pickingBy?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('sale.invoiced_at')" :value="$sale->invoiced_at" />
            <x-ui.definition-item :label="__('sale.invoiced_by')">{{ $sale->invoicedBy?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('sale.shipped_at')" :value="$sale->shipped_at" />
            <x-ui.definition-item :label="__('sale.shipped_by')">{{ $sale->shippedBy?->name ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('sale.delivered_at')" :value="$sale->delivered_at" />
            <x-ui.definition-item :label="__('sale.delivered_by')">{{ $sale->deliveredBy?->name ?? '—' }}</x-ui.definition-item>
        </x-ui.definition-grid>

        <div class="mt-8 overflow-x-auto">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">{{ __('sale.items') }}</h2>
                <span class="text-sm text-[var(--ui-text-muted)]">{{ $sale->lines->count() }} {{ __('sale.items_count') }}</span>
            </div>

            <x-ui.table :caption="__('sale.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-3">{{ __('sale.product') }}</th>
                        <th class="px-3 py-3">{{ __('sale.quantity') }}</th>
                        <th class="px-3 py-3">{{ __('sale.unit_price') }}</th>
                        <th class="px-3 py-3">{{ __('sale.line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sale->lines as $line)
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-4">{{ $line->product?->sku ?? '—' }} - {{ $line->product?->description ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ number_format($line->quantity, 6, ',', '.') }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">R$ {{ number_format($line->unit_price, 2, ',', '.') }}</td>
                            <td class="px-3 py-4 font-semibold">R$ {{ number_format($line->quantity * $line->unit_price, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-[var(--ui-text-muted)]">{{ __('sale.empty_items') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
