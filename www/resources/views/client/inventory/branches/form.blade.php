@extends('layouts.client-area')

@php($editing = $branch !== null)

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_branches'))
@section('client-page-title', $editing ? __('branch.edit') : __('branch.create'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('branch.edit') : __('branch.create') }}</h1>
        <x-ui.button :href="$editing ? route('inventory.branches.show', $branch) : route('inventory.branches.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('inventory.branches.update', $branch) : route('inventory.branches.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('branch.name') }}
                    <x-ui.input name="name" :value="old('name', $branch?->name)" class="mt-2" required />
                    @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium">
                    {{ __('branch.code') }}
                    <x-ui.input name="code" :value="old('code', $branch?->code)" class="mt-2" required />
                    @error('code')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('branch.status') }}
                <x-ui.select name="is_active" class="mt-2" required data-search="off">
                    <option value="1" @selected((string) old('is_active', $branch?->is_active ?? true) === '1')>{{ __('branch.active') }}</option>
                    <option value="0" @selected((string) old('is_active', $branch?->is_active ?? true) === '0')>{{ __('branch.inactive') }}</option>
                </x-ui.select>
                @error('is_active')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('inventory.branches.show', $branch) : route('inventory.branches.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('branch.save') : __('branch.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
