@extends('layouts.client-area')

@php($editing = $version !== null)

@section('title', __('ui.module_production').' | '.__('ui.module_routing'))
@section('client-page-title', $editing ? 'Editar roteamento' : 'Novo roteamento')

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? 'Editar roteamento' : 'Novo roteamento' }}</h1>
        <x-ui.button :href="$editing ? route('production.routing.show', $version) : route('production.routing.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.routing.update', $version) : route('production.routing.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">Produto
                <x-ui.select class="mt-2" name="product_id" required data-search="on" data-placeholder="Selecione" data-ajax-url="{{ route('production.products.search') }}" data-minimum-input-length="1">
                    <option value="">Selecione</option>
                    @if ($selectedProduct)
                        <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->sku }} - {{ $selectedProduct->description }}</option>
                    @endif
                </x-ui.select>
            </label>

            <div class="grid gap-5 sm:grid-cols-3">
                <label class="block text-sm font-medium">Versão
                    <x-ui.input class="mt-2" name="version_number" type="number" min="1" :value="old('version_number', $version?->version_number)" required />
                </label>
                <label class="block text-sm font-medium">Vigência inicial
                    <x-ui.input class="mt-2" name="effective_from" type="date" :value="old('effective_from', optional($version?->effective_from)->format('Y-m-d'))" />
                </label>
                <label class="block text-sm font-medium">Vigência final
                    <x-ui.input class="mt-2" name="effective_to" type="date" :value="old('effective_to', optional($version?->effective_to)->format('Y-m-d'))" />
                </label>
            </div>

            <label class="block text-sm font-medium">Descrição
                <x-ui.input class="mt-2" name="description" maxlength="255" :value="old('description', $version?->description)" />
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('production.routing.show', $version) : route('production.routing.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? 'Salvar' : 'Criar roteamento' }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
