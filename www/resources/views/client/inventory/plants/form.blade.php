@extends('layouts.client-area')

@php($editing = $plant !== null)

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_plants'))
@section('client-page-title', $editing ? __('plant.edit') : __('plant.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('plant.edit') : __('plant.create') }}</h1>
        <x-ui.button :href="$editing ? route('inventory.plants.show', $plant) : route('inventory.plants.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('inventory.plants.update', $plant) : route('inventory.plants.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('plant.name') }}
                    <x-ui.input name="name" :value="old('name', $plant?->name)" class="mt-2" required />
                    @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('plant.code') }}
                    <x-ui.input name="code" :value="old('code', $plant?->code)" class="mt-2" required />
                    @error('code')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('plant.branch') }}
                    <x-ui.select name="branch_id" class="mt-2" data-search="on">
                        <option value="">{{ __('plant.select_branch') }}</option>
                        @foreach ($branches as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('branch_id', $plant?->branch_id) === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('branch_id')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('plant.timezone') }}
                    <x-ui.input name="timezone" :value="old('timezone', $plant?->timezone ?? 'UTC')" class="mt-2" required maxlength="50" />
                    @error('timezone')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('plant.status') }}
                    <x-ui.select name="is_active" class="mt-2" required data-search="off">
                        <option value="1" @selected((string) old('is_active', $plant?->is_active ?? true) === '1')>{{ __('plant.active') }}</option>
                        <option value="0" @selected((string) old('is_active', $plant?->is_active ?? true) === '0')>{{ __('plant.inactive') }}</option>
                    </x-ui.select>
                    @error('is_active')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('inventory.plants.show', $plant) : route('inventory.plants.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('plant.save') : __('plant.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
