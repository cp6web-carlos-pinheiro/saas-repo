@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('inventory_web.balances'))
@section('client-page-title', __('inventory_web.balances'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('inventory_web.balances') }}" subtitle="{{ __('inventory_web.balances_description') }}">
        <x-slot:actions>
        <x-ui.button :href="route('inventory.movements.create')" variant="primary" class="rounded-full">{{ __('inventory_web.new_movement') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form method="GET" class="grid gap-3 md:grid-cols-3">
            <x-ui.select name="warehouse_id" select2="false">
                <option value="">{{ __('inventory_web.all_warehouses') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($warehouseId === $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select name="product_id" select2="false">
                <option value="">{{ __('inventory_web.all_products') }}</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->sku }} — {{ $product->description }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('inventory_web.filter') }}</x-ui.button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('inventory_web.balances')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="warehouse" :label="__('inventory_web.warehouse')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="product" :label="__('inventory_web.product')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="text-right" column="qty_available" :label="__('inventory_web.available')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="text-right" column="qty_reserved" :label="__('inventory_web.reserved')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="text-right" column="qty_inspection" :label="__('inventory_web.inspection')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="text-right" column="qty_in_transit" :label="__('inventory_web.in_transit')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="last_movement_at" :label="__('inventory_web.last_movement')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($balances as $balance)
                        <tr class="border-b border-[var(--ui-border)]"><td class="px-3 py-4">{{ $balance->warehouse?->code }} — {{ $balance->warehouse?->name }}</td><td class="px-3 py-4">{{ $balance->product?->sku }} — {{ $balance->product?->description }}</td><td class="px-3 py-4 text-right">{{ number_format($balance->qty_available, 3, ',', '.') }} {{ $balance->product?->unit?->code }}</td><td class="px-3 py-4 text-right">{{ number_format($balance->qty_reserved, 3, ',', '.') }}</td><td class="px-3 py-4 text-right">{{ number_format($balance->qty_inspection, 3, ',', '.') }}</td><td class="px-3 py-4 text-right">{{ number_format($balance->qty_in_transit, 3, ',', '.') }}</td><td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $balance->last_movement_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('inventory_web.empty_balances') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
        <div class="mt-6">{{ $balances->links() }}</div>
    </x-ui.panel>
</div>
@endsection
