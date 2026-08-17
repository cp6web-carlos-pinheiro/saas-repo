@extends('layouts.global-admin')
@php($editing = $administrator !== null)
@section('title', ($editing ? __('global_admin.edit') : __('global_admin.create')).' | '.__('global_admin.modules.administrators'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$editing ? __('global_admin.edit') : __('global_admin.create')"
        :breadcrumbs="[['label' => __('global_admin.modules.administrators'), 'href' => route('global-admin.administrators.index')], ['label' => $editing ? __('global_admin.edit') : __('global_admin.create')]]"
    />

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        @if ($errors->any())
            <x-ui.alert class="mb-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.administrators.update', $administrator) : route('global-admin.administrators.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field :label="__('global_admin.name')" for="name" :error="$errors->first('name')">
                <x-ui.input id="name" name="name" :value="old('name', $administrator?->name)" required />
            </x-ui.field>

            <x-ui.field :label="__('global_admin.email')" for="email" :error="$errors->first('email')">
                <x-ui.input id="email" name="email" type="email" :value="old('email', $administrator?->email)" required />
            </x-ui.field>

            <x-ui.field :label="$editing ? __('global_admin.new_password') : __('global_admin.password')" for="password" :error="$errors->first('password')">
                <x-ui.input id="password" name="password" type="password" :required="! $editing" />
            </x-ui.field>

            <x-ui.field :label="__('global_admin.password_confirmation')" for="password_confirmation">
                <x-ui.input id="password_confirmation" name="password_confirmation" type="password" :required="! $editing" />
            </x-ui.field>

            @if ($editing)
                <x-ui.checkbox name="is_active" value="1" :checked="old('is_active', $administrator->is_active)" :label="__('global_admin.active')" />
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.administrators.show', $administrator) : route('global-admin.administrators.index')" variant="secondary" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_admin.save') : __('global_admin.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection