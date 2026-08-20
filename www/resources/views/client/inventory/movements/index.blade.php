@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('inventory_web.movements'))
@section('client-page-title', __('inventory_web.movements'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading :title="__('inventory_web.movements')" :subtitle="__('inventory_web.movements_description')">
        <x-slot:actions><x-ui.button :href="route('inventory.movements.create')" variant="primary" class="rounded-full">{{ __('inventory_web.new_movement') }}</x-ui.button></x-slot:actions>
    </x-ui.page-heading>
    @if (session('status'))<x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>@endif
    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form method="GET" class="grid gap-3 md:grid-cols-4"><x-ui.select name="warehouse_id" select2="false"><option value="">{{ __('inventory_web.all_warehouses') }}</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($warehouseId === $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>@endforeach</x-ui.select><x-ui.select name="product_id" select2="false"><option value="">{{ __('inventory_web.all_products') }}</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->sku }} — {{ $product->description }}</option>@endforeach</x-ui.select><x-ui.select name="movement_type" select2="false"><option value="">{{ __('inventory_web.all_types') }}</option>@foreach ($movementTypes as $type => $label)<option value="{{ $type }}" @selected($movementType === $type)>{{ $label }}</option>@endforeach</x-ui.select><x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('inventory_web.filter') }}</x-ui.button></form>
        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('inventory_web.movements')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="movement_at" :label="__('inventory_web.date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="movement_type" :label="__('inventory_web.type')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="warehouse" :label="__('inventory_web.warehouse')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="product" :label="__('inventory_web.product')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="text-right" column="quantity" :label="__('inventory_web.quantity')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="reference" :label="__('inventory_web.reference')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="notes" :label="__('inventory_web.notes')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr class="border-b border-[var(--ui-border)]"><td class="px-3 py-4">{{ $movement->movement_at?->format('d/m/Y H:i') }}</td><td class="px-3 py-4">{{ $movementTypes[$movement->movement_type] ?? $movement->movement_type }}</td><td class="px-3 py-4">{{ $movement->warehouse?->code }}</td><td class="px-3 py-4">{{ $movement->product?->sku }} — {{ $movement->product?->description }}</td><td class="px-3 py-4 text-right">{{ number_format($movement->quantity, 3, ',', '.') }} {{ $movement->product?->unit?->code }}</td><td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $movement->reference_type ? $movement->reference_type.' #'.$movement->reference_id : '—' }}</td><td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $movement->notes ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('inventory_web.empty_movements') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
        <div class="mt-6">{{ $movements->links() }}</div>
    </x-ui.panel>
</div>
@endsection
