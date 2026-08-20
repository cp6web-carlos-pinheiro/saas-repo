@extends('layouts.client-area')

@php($editing = $unit !== null)

@section('title', __('ui.module_inventory').' | '.__('admin_data_units.title'))
@section('client-page-title', $editing ? __('admin_data_units.edit') : __('admin_data_units.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('admin_data_units.title') }}">
        <x-slot:actions>
        <x-ui.button :href="$editing ? route('admin-data.units.show', $unit) : route('admin-data.units.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('admin-data.units.update', $unit) : route('admin-data.units.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="{{ __('admin_data.code') }}" for="code" :required="true" :error="$errors->first('code')">
                    <x-ui.input name="code" :value="old('code', $unit?->code)" class="mt-2" required  id="code" :aria-describedby="$errors->has('code') ? 'code-error' : null"/>
                </x-ui.field>

                <x-ui.field label="{{ __('admin_data.name') }}" for="name" :required="true" :error="$errors->first('name')">
                    <x-ui.input name="name" :value="old('name', $unit?->name)" class="mt-2" required  id="name" :aria-describedby="$errors->has('name') ? 'name-error' : null"/>
                </x-ui.field>
            </div>

            <x-ui.field label="{{ __('admin_data.description') }}" for="description" :error="$errors->first('description')">
                <x-ui.textarea name="description" class="mt-2" rows="5" id="description" :aria-describedby="$errors->has('description') ? 'description-error' : null">{{ old('description', $unit?->description) }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field label="{{ __('admin_data.status') }}" for="is-active" :required="true" :error="$errors->first('is_active')">
                <x-ui.select name="is_active" class="mt-2" required data-search="off" id="is-active" :aria-describedby="$errors->has('is_active') ? 'is-active-error' : null">
                    <option value="1" @selected((string) old('is_active', $unit?->is_active ?? true) === '1')>{{ __('admin_data.active') }}</option>
                    <option value="0" @selected((string) old('is_active', $unit?->is_active ?? true) === '0')>{{ __('admin_data.inactive') }}</option>
                </x-ui.select>
            </x-ui.field>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('admin-data.units.show', $unit) : route('admin-data.units.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('admin_data_units.save') : __('admin_data_units.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
