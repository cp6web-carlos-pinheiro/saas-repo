@extends('layouts.client-area')

@php($editing = $plant !== null)

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_plants'))
@section('client-page-title', $editing ? __('plant.edit') : __('plant.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $editing ? __('plant.edit') : __('plant.create') }}">
        <x-slot:actions>
        <x-ui.button :href="$editing ? route('inventory.plants.show', $plant) : route('inventory.plants.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('inventory.plants.update', $plant) : route('inventory.plants.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field label="{{ __('plant.name') }}" for="name" :required="true" :error="$errors->first('name')">
                <x-ui.input name="name" :value="old('name', $plant?->name)" class="mt-2" required  id="name" :aria-describedby="$errors->has('name') ? 'name-error' : null"/>
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="{{ __('plant.timezone') }}" for="timezone" :required="true" :error="$errors->first('timezone')">
                    <x-ui.input name="timezone" :value="old('timezone', $plant?->timezone ?? 'UTC')" class="mt-2" required maxlength="50"  id="timezone" :aria-describedby="$errors->has('timezone') ? 'timezone-error' : null"/>
                </x-ui.field>

                <x-ui.field label="{{ __('plant.status') }}" for="is-active" :required="true" :error="$errors->first('is_active')">
                    <x-ui.select name="is_active" class="mt-2" required data-search="off" id="is-active" :aria-describedby="$errors->has('is_active') ? 'is-active-error' : null">
                        <option value="1" @selected((string) old('is_active', $plant?->is_active ?? true) === '1')>{{ __('plant.active') }}</option>
                        <option value="0" @selected((string) old('is_active', $plant?->is_active ?? true) === '0')>{{ __('plant.inactive') }}</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('inventory.plants.show', $plant) : route('inventory.plants.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('plant.save') : __('plant.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
