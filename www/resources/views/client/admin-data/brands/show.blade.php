@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('admin_data_brands.title'))
@section('client-page-title', __('admin_data_brands.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $brand->name }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('admin-data.brands.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('admin-data.brands.edit', $brand)" variant="material-edit" class="rounded-full">{{ __('admin_data_brands.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('admin-data.brands.destroy', $brand) }}" data-admin-delete-confirm data-admin-name="{{ $brand->name }}" data-confirm-title="{{ __('admin_data.confirm_delete_title') }}" data-confirm-text="{{ __('admin_data.confirm_delete_text') }}" data-confirm-confirm="{{ __('admin_data.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('admin_data.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('admin_data_brands.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('admin_data.reference')">#{{ $brand->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.name')">{{ $brand->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.description')">{{ $brand->description ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('admin_data.status')" :value="$brand->is_active ? __('admin_data.active') : __('admin_data.inactive')" :tone="$brand->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date :label="__('admin_data.created_at')" :value="$brand->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
