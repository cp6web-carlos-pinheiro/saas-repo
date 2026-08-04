@extends('layouts.client-area')

@php($editing = $workCenter !== null)

@section('title', __('ui.module_production').' | '.__('ui.work_centers'))
@section('client-page-title', $editing ? 'Editar centro de trabalho' : 'Novo centro de trabalho')

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? 'Editar centro de trabalho' : 'Novo centro de trabalho' }}</h1>
        <x-ui.button :href="$editing ? route('production.work-centers.show', $workCenter) : route('production.work-centers.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.work-centers.update', $workCenter) : route('production.work-centers.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">Planta
                <x-ui.select class="mt-2" name="plant_id" required data-search="on">
                    <option value="">Selecione</option>
                    @foreach ($plants as $plant)
                        <option value="{{ $plant->id }}" @selected((string) old('plant_id', $workCenter?->plant_id) === (string) $plant->id)>{{ $plant->code }} - {{ $plant->name }}</option>
                    @endforeach
                </x-ui.select>
            </label>

            <div class="grid gap-5 sm:grid-cols-3">
                <label class="block text-sm font-medium">Código
                    <x-ui.input class="mt-2" name="code" :value="old('code', $workCenter?->code)" required />
                </label>
                <label class="block text-sm font-medium">Nome
                    <x-ui.input class="mt-2" name="name" :value="old('name', $workCenter?->name)" required />
                </label>
                <label class="block text-sm font-medium">Tipo
                    <x-ui.select class="mt-2" name="resource_type" required data-search="off">
                        <option value="MACHINE" @selected(old('resource_type', $workCenter?->resource_type ?? 'MACHINE') === 'MACHINE')>MACHINE</option>
                        <option value="LINE" @selected(old('resource_type', $workCenter?->resource_type ?? 'MACHINE') === 'LINE')>LINE</option>
                    </x-ui.select>
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <label class="block text-sm font-medium">Capacidade/dia
                    <x-ui.input class="mt-2" name="capacity_per_day" type="number" step="0.01" min="0" :value="old('capacity_per_day', $workCenter?->capacity_per_day)" required />
                </label>
                <label class="block text-sm font-medium">Eficiência (%)
                    <x-ui.input class="mt-2" name="efficiency_factor" type="number" step="0.01" min="0" max="1000" :value="old('efficiency_factor', $workCenter?->efficiency_factor)" required />
                </label>
                <label class="inline-flex items-center gap-2 self-end pb-2 text-sm">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $workCenter?->is_active ?? true)) /> Ativo
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('production.work-centers.show', $workCenter) : route('production.work-centers.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? 'Salvar' : 'Criar centro' }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
