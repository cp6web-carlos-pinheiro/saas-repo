@extends('layouts.global-admin')
@section('title', ($tutorial->title ?: $tutorial->route_name).' | '.__('global_tutorial.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$tutorial->route_name"
        :subtitle="__('global_tutorial.details')"
        :breadcrumbs="[['label' => __('global_tutorial.title'), 'href' => route('global-admin.tutorials.index')], ['label' => $tutorial->title ?: $tutorial->route_name]]"
    />

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        <x-ui.definition-grid cols="sm:grid-cols-2 xl:grid-cols-3">
            <x-ui.definition-item label="ID">{{ $tutorial->id }}</x-ui.definition-item>
            <x-ui.definition-item-date :label="__('global_tutorial.created_at')" :value="$tutorial->created_at" />
            <x-ui.definition-item-date :label="__('global_tutorial.updated_at')" :value="$tutorial->updated_at" />
        </x-ui.definition-grid>

        <x-ui.panel class="mt-8" padding="p-4">
            <div class="prose prose-sm mt-3 max-w-none text-[var(--ui-text)]">
                {!! $tutorial->content_html !!}
            </div>
        </x-ui.panel>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.tutorials.index')" variant="secondary" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.tutorials.edit', $tutorial)" variant="primary" class="rounded-full">
                <x-ui.icon name="pencil" size="sm" /> {{ __('global_tutorial.edit') }}
            </x-ui.button>

            <x-ui.confirm-button
                :action="route('global-admin.tutorials.destroy', $tutorial)"
                :label="__('global_tutorial.remove')"
                :confirm-title="__('global_tutorial.confirm_delete_title')"
                :confirm-text="__('global_tutorial.confirm_delete_text', ['name' => $tutorial->route_name])"
                :confirm-label="__('global_tutorial.confirm_delete_confirm')"
                :cancel-label="__('global_tutorial.confirm_delete_cancel')"
                class="rounded-full"
            />
        </div>
    </x-ui.panel>
</div>
@endsection