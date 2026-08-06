@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('inventory_web.new_movement'))
@section('client-page-title', __('inventory_web.new_movement'))

@section('client-content')
<div class="w-full p-5 md:p-8"><div class="flex items-center justify-between gap-4"><h1 class="font-display text-3xl font-bold">{{ __('inventory_web.new_movement') }}</h1><x-ui.button :href="route('inventory.movements.index')" variant="material-back" class="rounded-full">{{ __('inventory_web.back') }}</x-ui.button></div><x-ui.panel class="mt-6 max-w-4xl border-[#dadce0] shadow-none" padding="p-5 md:p-6"><form method="POST" action="{{ route('inventory.movements.store') }}" class="grid gap-5 md:grid-cols-2">@csrf
    <div><label for="warehouse_id" class="mb-1 block text-sm font-medium">{{ __('inventory_web.warehouse') }}</label><x-ui.select id="warehouse_id" name="warehouse_id" required><option value="">{{ __('inventory_web.select') }}</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} — {{ $warehouse->name }}</option>@endforeach</x-ui.select></div>
    <div><label for="product_id" class="mb-1 block text-sm font-medium">{{ __('inventory_web.product') }}</label><x-ui.select id="product_id" name="product_id" required><option value="">{{ __('inventory_web.select') }}</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->sku }} — {{ $product->description }}</option>@endforeach</x-ui.select></div>
    <div><label for="movement_type" class="mb-1 block text-sm font-medium">{{ __('inventory_web.type') }}</label><x-ui.select id="movement_type" name="movement_type" required select2="false">@foreach ($movementTypes as $type => $label)<option value="{{ $type }}" @selected(old('movement_type', 'RECEIPT') === $type)>{{ $label }}</option>@endforeach</x-ui.select></div>
    <div><label for="quantity" class="mb-1 block text-sm font-medium">{{ __('inventory_web.quantity') }}</label><x-ui.input id="quantity" name="quantity" type="number" min="0.001" step="0.001" :value="old('quantity')" required /></div>
    <div><label for="movement_at" class="mb-1 block text-sm font-medium">{{ __('inventory_web.date') }}</label><x-ui.input id="movement_at" name="movement_at" type="datetime-local" :value="old('movement_at', now()->format('Y-m-d\\TH:i'))" required /></div>
    <div><label for="lot_number" class="mb-1 block text-sm font-medium">{{ __('inventory_web.lot') }}</label><x-ui.input id="lot_number" name="lot_number" :value="old('lot_number')" /></div>
    <div><label for="reference_type" class="mb-1 block text-sm font-medium">{{ __('inventory_web.reference_type') }}</label><x-ui.input id="reference_type" name="reference_type" :value="old('reference_type')" /></div>
    <div><label for="reference_id" class="mb-1 block text-sm font-medium">{{ __('inventory_web.reference_id') }}</label><x-ui.input id="reference_id" name="reference_id" type="number" min="1" :value="old('reference_id')" /></div>
    <div class="md:col-span-2"><label for="notes" class="mb-1 block text-sm font-medium">{{ __('inventory_web.notes') }}</label><x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes') }}</x-ui.textarea></div>
    <div class="md:col-span-2 flex justify-end gap-3"><x-ui.button :href="route('inventory.movements.index')" variant="material-back">{{ __('inventory_web.cancel') }}</x-ui.button><x-ui.button type="submit" variant="brand-primary">{{ __('inventory_web.save') }}</x-ui.button></div>
</form></x-ui.panel></div>
@endsection
