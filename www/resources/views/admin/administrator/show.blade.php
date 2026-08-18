@extends('layouts.global-admin')
@section('title', $administrator->name.' | '.__('global_admin.modules.administrators'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$administrator->name"
        :subtitle="__('global_admin.details')"
        :breadcrumbs="[['label' => __('global_admin.modules.administrators'), 'href' => route('global-admin.administrators.index')], ['label' => $administrator->name]]"
    >
        <x-slot:actions>
            <x-ui.definition-item-status
                :label="__('global_admin.status')"
                :value="$administrator->is_active ? __('global_admin.active') : __('global_admin.inactive')"
                :tone="$administrator->is_active ? 'success' : 'neutral'"
                inline
            />
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        <x-ui.definition-grid cols="sm:grid-cols-2">
            <x-ui.definition-item :label="__('global_admin.email')">{{ $administrator->email }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('global_admin.created_at')" :value="$administrator->created_at" />
        </x-ui.definition-grid>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.administrators.index')" variant="secondary" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.administrators.edit', $administrator)" variant="primary" class="rounded-full">
                <x-ui.icon name="pencil" size="sm" /> {{ __('global_admin.edit') }}
            </x-ui.button>

            <x-ui.confirm-button
                :action="route('global-admin.administrators.destroy', $administrator)"
                :label="__('global_admin.remove')"
                :confirm-title="__('global_admin.confirm_delete_title')"
                :confirm-text="__('global_admin.confirm_delete_text', ['name' => $administrator->name])"
                :confirm-label="__('global_admin.confirm_delete_confirm')"
                :cancel-label="__('global_admin.confirm_delete_cancel')"
                class="rounded-full"
            />
        </div>
    </x-ui.panel>
</div>
@endsection