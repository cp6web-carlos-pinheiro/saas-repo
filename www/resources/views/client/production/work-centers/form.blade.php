@extends('layouts.client-area')

@php($editing = $workCenter !== null)

@section('title', __('ui.module_production').' | '.__('ui.work_centers'))
@section('client-page-title', $editing ? __('production.work_centers.edit') : __('production.work_centers.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('production.work_centers.edit') : __('production.work_centers.new') }}</h1>
        <x-ui.button :href="$editing ? route('production.work-centers.show', $workCenter) : route('production.work-centers.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.work-centers.update', $workCenter) : route('production.work-centers.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('production.plant') }}" for="plant-id" :required="true" :error="$errors->first('plant_id')">
                <x-ui.select class="mt-2" name="plant_id" required data-search="on" id="plant-id" :aria-describedby="$errors->has('plant_id') ? 'plant-id-error' : null">
                    <option value="">{{ __('production.select') }}</option>
                    @foreach ($plants as $plant)
                        <option value="{{ $plant->id }}" @selected((string) old('plant_id', $workCenter?->plant_id) === (string) $plant->id)>{{ $plant->code }} - {{ $plant->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.field label="{{ __('production.code') }}" for="code" :required="true" :error="$errors->first('code')">
                    <x-ui.input class="mt-2" name="code" :value="old('code', $workCenter?->code)" required  id="code" :aria-describedby="$errors->has('code') ? 'code-error' : null"/>
                </x-ui.field>
                <x-ui.field label="{{ __('production.name') }}" for="name" :required="true" :error="$errors->first('name')">
                    <x-ui.input class="mt-2" name="name" :value="old('name', $workCenter?->name)" required  id="name" :aria-describedby="$errors->has('name') ? 'name-error' : null"/>
                </x-ui.field>
                <x-ui.field label="{{ __('production.type') }}" for="resource-type" :required="true" :error="$errors->first('resource_type')">
                    <x-ui.select class="mt-2" name="resource_type" required data-search="off" id="resource-type" :aria-describedby="$errors->has('resource_type') ? 'resource-type-error' : null">
                        <option value="MACHINE" @selected(old('resource_type', $workCenter?->resource_type ?? 'MACHINE') === 'MACHINE')>{{ __('production.work_centers.machine') }}</option>
                        <option value="LINE" @selected(old('resource_type', $workCenter?->resource_type ?? 'MACHINE') === 'LINE')>{{ __('production.work_centers.line') }}</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.field label="{{ __('production.capacity_per_day') }}" for="capacity-per-day" :required="true" :error="$errors->first('capacity_per_day')">
                    <x-ui.input class="mt-2" name="capacity_per_day" type="number" step="0.01" min="0" :value="old('capacity_per_day', $workCenter?->capacity_per_day)" required  id="capacity-per-day" :aria-describedby="$errors->has('capacity_per_day') ? 'capacity-per-day-error' : null"/>
                </x-ui.field>
                <x-ui.field label="{{ __('production.efficiency') }} (%)" for="efficiency-factor" :required="true" :error="$errors->first('efficiency_factor')">
                    <x-ui.input class="mt-2" name="efficiency_factor" type="number" step="0.01" min="0" max="1000" :value="old('efficiency_factor', $workCenter?->efficiency_factor)" required  id="efficiency-factor" :aria-describedby="$errors->has('efficiency_factor') ? 'efficiency-factor-error' : null"/>
                </x-ui.field>
                <div class="self-end pb-2">
                    <x-ui.input type="hidden" name="is_active" value="0" unstyled />
                    <x-ui.checkbox name="is_active" value="1" :checked="(bool) old('is_active', $workCenter?->is_active ?? true)">{{ __('production.active') }}</x-ui.checkbox>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('production.work-centers.show', $workCenter) : route('production.work-centers.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('production.save') : __('production.work_centers.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
