@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_orders'))
@section('client-page-title', __('production.orders.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('production.orders.new') }}">
        <x-slot:actions>
        <x-ui.button :href="route('production.orders.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ route('production.orders.store') }}" class="space-y-5">
            @csrf
            @if ($creationContext)
                <x-ui.input type="hidden" name="sale_id" :value="$creationContext['sale_id']" unstyled />
                <x-ui.input type="hidden" name="sale_line_id" :value="$creationContext['sale_line_id']" unstyled />
                <x-ui.input type="hidden" name="dependency_level" :value="$creationContext['dependency_level']" unstyled />
                <x-ui.alert variant="info">{{ __('production.orders.sale_context', ['sale' => $creationContext['sale_id']]) }}</x-ui.alert>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="{{ __('production.product') }}" for="product-id" :required="true" :error="$errors->first('product_id')">
                    <x-ui.select name="product_id" class="mt-2" required data-search="on" :data-placeholder="__('production.select_product')" data-ajax-url="{{ route('production.products.search') }}" data-minimum-input-length="1" id="product-id" :aria-describedby="$errors->has('product_id') ? 'product-id-error' : null">
                        <option value="">{{ __('production.select_product') }}</option>
                        @if ($selectedProduct)
                            <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->sku }} - {{ $selectedProduct->description }}</option>
                        @endif
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="{{ __('production.orders.input_warehouse') }}" for="warehouse-id" :error="$errors->first('warehouse_id')">
                    <x-ui.select name="warehouse_id" class="mt-2" data-search="on" id="warehouse-id" :aria-describedby="$errors->has('warehouse_id') ? 'warehouse-id-error' : null">
                        <option value="">{{ __('production.select_warehouse') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $initialValues['warehouse_id']) === (string) $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.field label="{{ __('production.orders.planned_quantity') }}" for="quantity-planned" :required="true" :error="$errors->first('quantity_planned')">
                    <x-ui.input name="quantity_planned" type="number" step="0.001" min="0.001" :value="old('quantity_planned', $initialValues['quantity_planned'])" class="mt-2" required  id="quantity-planned" :aria-describedby="$errors->has('quantity_planned') ? 'quantity-planned-error' : null"/>
                </x-ui.field>

                <x-ui.field label="{{ __('production.orders.scheduled_start') }}" for="scheduled-start-date" :error="$errors->first('scheduled_start_date')">
                    <x-ui.input name="scheduled_start_date" type="date" :value="old('scheduled_start_date', $initialValues['scheduled_start_date'])" class="mt-2"  id="scheduled-start-date" :aria-describedby="$errors->has('scheduled_start_date') ? 'scheduled-start-date-error' : null"/>
                </x-ui.field>

                <x-ui.field label="{{ __('production.orders.scheduled_end') }}" for="scheduled-end-date" :error="$errors->first('scheduled_end_date')">
                    <x-ui.input name="scheduled_end_date" type="date" :value="old('scheduled_end_date', $initialValues['scheduled_end_date'])" class="mt-2"  id="scheduled-end-date" :aria-describedby="$errors->has('scheduled_end_date') ? 'scheduled-end-date-error' : null"/>
                </x-ui.field>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="route('production.orders.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ __('production.orders.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
