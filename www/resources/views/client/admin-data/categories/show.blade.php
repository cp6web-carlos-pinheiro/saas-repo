@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('admin_data_categories.title'))
@section('client-page-title', __('admin_data_categories.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ $category->name }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('admin-data.categories.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('admin-data.categories.edit', $category)" variant="primary" class="rounded-full">{{ __('admin_data_categories.edit') }}</x-ui.button>
            <x-ui.confirm-button :action="route('admin-data.categories.destroy', $category)" method="DELETE" class="rounded-full" :label="__('admin_data_categories.remove')" :confirm-title="__('admin_data.confirm_delete_title')" :confirm-text="__('admin_data.confirm_delete_text', ['name' => $category->name])" :confirm-label="__('admin_data.confirm_delete_confirm')" :cancel-label="__('admin_data.confirm_delete_cancel')" />
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('admin_data.reference')">#{{ $category->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.name')">{{ $category->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.description')">{{ $category->description ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('admin_data.status')" :value="$category->is_active ? __('admin_data.active') : __('admin_data.inactive')" :tone="$category->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date :label="__('admin_data.created_at')" :value="$category->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
