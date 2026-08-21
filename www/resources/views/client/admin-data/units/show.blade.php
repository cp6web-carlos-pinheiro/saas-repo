@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('admin_data_units.title'))
@section('client-page-title', __('admin_data_units.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    @php($isGlobal = $unit->company_id === null)
    <x-ui.page-heading title="{{ $unit->code }} - {{ $unit->name }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('admin-data.units.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            @unless($isGlobal)
                <x-ui.button :href="route('admin-data.units.edit', $unit)" variant="primary" class="rounded-full">{{ __('admin_data_units.edit') }}</x-ui.button>
                <x-ui.confirm-button :action="route('admin-data.units.destroy', $unit)" method="DELETE" class="rounded-full" :label="__('admin_data_units.remove')" :confirm-title="__('admin_data.confirm_delete_title')" :confirm-text="__('admin_data.confirm_delete_text', ['name' => $unit->name])" :confirm-label="__('admin_data.confirm_delete_confirm')" :cancel-label="__('admin_data.confirm_delete_cancel')" />
            @endunless
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    @if ($isGlobal)
        <x-ui.alert class="mt-5" variant="info">{{ __('admin_data_units.global_readonly') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('admin_data.reference')">#{{ $unit->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.code')">{{ $unit->code }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.name')">{{ $unit->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data_units.scope')">{{ $isGlobal ? __('admin_data_units.global_label') : __('admin_data_units.tenant_label') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('admin_data.description')">{{ $unit->description ?: '—' }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('admin_data.status')" :value="$unit->is_active ? __('admin_data.active') : __('admin_data.inactive')" :tone="$unit->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date :label="__('admin_data.created_at')" :value="$unit->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
