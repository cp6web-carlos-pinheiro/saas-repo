@extends('layouts.client-area')

@php($editing = $version !== null)

@section('title', __('ui.module_production').' | '.__('ui.module_routing'))
@section('client-page-title', $editing ? __('production.routing.edit') : __('production.routing.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('production.routing.edit') : __('production.routing.new') }}</h1>
        <x-ui.button :href="$editing ? route('production.routing.show', $version) : route('production.routing.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.routing.update', $version) : route('production.routing.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('production.product') }}" for="product-id" :required="true" :error="$errors->first('product_id')">
                <x-ui.select class="mt-2" name="product_id" required data-search="on" :data-placeholder="__('production.select')" data-ajax-url="{{ route('production.products.search') }}" data-minimum-input-length="1" id="product-id" :aria-describedby="$errors->has('product_id') ? 'product-id-error' : null">
                    <option value="">{{ __('production.select') }}</option>
                    @if ($selectedProduct)
                        <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->sku }} - {{ $selectedProduct->description }}</option>
                    @endif
                </x-ui.select>
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.field label="{{ __('production.version') }}" for="version-number" :required="true" :error="$errors->first('version_number')">
                    <x-ui.input class="mt-2" name="version_number" type="number" min="1" :value="old('version_number', $version?->version_number)" required  id="version-number" :aria-describedby="$errors->has('version_number') ? 'version-number-error' : null"/>
                </x-ui.field>
                <x-ui.field label="{{ __('production.effective_from') }}" for="effective-from" :error="$errors->first('effective_from')">
                    <x-ui.input class="mt-2" name="effective_from" type="date" :value="old('effective_from', optional($version?->effective_from)->format('Y-m-d'))"  id="effective-from" :aria-describedby="$errors->has('effective_from') ? 'effective-from-error' : null"/>
                </x-ui.field>
                <x-ui.field label="{{ __('production.effective_to') }}" for="effective-to" :error="$errors->first('effective_to')">
                    <x-ui.input class="mt-2" name="effective_to" type="date" :value="old('effective_to', optional($version?->effective_to)->format('Y-m-d'))"  id="effective-to" :aria-describedby="$errors->has('effective_to') ? 'effective-to-error' : null"/>
                </x-ui.field>
            </div>

            <x-ui.field label="{{ __('production.description') }}" for="description" :error="$errors->first('description')">
                <x-ui.input class="mt-2" name="description" maxlength="255" :value="old('description', $version?->description)"  id="description" :aria-describedby="$errors->has('description') ? 'description-error' : null"/>
            </x-ui.field>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('production.routing.show', $version) : route('production.routing.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('production.save') : __('production.routing.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
