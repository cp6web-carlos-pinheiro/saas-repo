@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_orders'))
@section('client-page-title', __('production.orders.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('production.orders.new') }}</h1>
        <x-ui.button :href="route('production.orders.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ route('production.orders.store') }}" class="space-y-5">
            @csrf
            @if ($creationContext)
                <input type="hidden" name="sale_id" value="{{ $creationContext['sale_id'] }}">
                <input type="hidden" name="sale_line_id" value="{{ $creationContext['sale_line_id'] }}">
                <input type="hidden" name="dependency_level" value="{{ $creationContext['dependency_level'] }}">
                <x-ui.alert variant="info">{{ __('production.orders.sale_context', ['sale' => $creationContext['sale_id']]) }}</x-ui.alert>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('production.product') }}
                    <x-ui.select name="product_id" class="mt-2" required data-search="on" :data-placeholder="__('production.select_product')" data-ajax-url="{{ route('production.products.search') }}" data-minimum-input-length="1">
                        <option value="">{{ __('production.select_product') }}</option>
                        @if ($selectedProduct)
                            <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->sku }} - {{ $selectedProduct->description }}</option>
                        @endif
                    </x-ui.select>
                    @error('product_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('production.orders.input_warehouse') }}
                    <x-ui.select name="warehouse_id" class="mt-2" data-search="on">
                        <option value="">{{ __('production.select_warehouse') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $initialValues['warehouse_id']) === (string) $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('warehouse_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <label class="block text-sm font-medium">
                    {{ __('production.orders.planned_quantity') }}
                    <x-ui.input name="quantity_planned" type="number" step="0.001" min="0.001" :value="old('quantity_planned', $initialValues['quantity_planned'])" class="mt-2" required />
                    @error('quantity_planned')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('production.orders.scheduled_start') }}
                    <x-ui.input name="scheduled_start_date" type="date" :value="old('scheduled_start_date', $initialValues['scheduled_start_date'])" class="mt-2" />
                    @error('scheduled_start_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('production.orders.scheduled_end') }}
                    <x-ui.input name="scheduled_end_date" type="date" :value="old('scheduled_end_date', $initialValues['scheduled_end_date'])" class="mt-2" />
                    @error('scheduled_end_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="route('production.orders.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ __('production.orders.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
