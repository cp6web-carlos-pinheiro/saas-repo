@extends('layouts.client-area')

@php($editing = $unit !== null)

@section('title', __('ui.module_inventory').' | '.__('admin_data_units.title'))
@section('client-page-title', $editing ? __('admin_data_units.edit') : __('admin_data_units.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('admin_data_units.title') }}</h1>
        <x-ui.button :href="$editing ? route('admin-data.units.show', $unit) : route('admin-data.units.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('admin-data.units.update', $unit) : route('admin-data.units.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('admin_data.code') }}
                    <x-ui.input name="code" :value="old('code', $unit?->code)" class="mt-2" required />
                    @error('code')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('admin_data.name') }}
                    <x-ui.input name="name" :value="old('name', $unit?->name)" class="mt-2" required />
                    @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('admin_data.description') }}
                <x-ui.textarea name="description" class="mt-2" rows="5">{{ old('description', $unit?->description) }}</x-ui.textarea>
                @error('description')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('admin_data.status') }}
                <x-ui.select name="is_active" class="mt-2" required data-search="off">
                    <option value="1" @selected((string) old('is_active', $unit?->is_active ?? true) === '1')>{{ __('admin_data.active') }}</option>
                    <option value="0" @selected((string) old('is_active', $unit?->is_active ?? true) === '0')>{{ __('admin_data.inactive') }}</option>
                </x-ui.select>
                @error('is_active')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('admin-data.units.show', $unit) : route('admin-data.units.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('admin_data_units.save') : __('admin_data_units.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
