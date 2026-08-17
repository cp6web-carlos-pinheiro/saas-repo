@extends('layouts.global-admin')
@php($editing = $company !== null)
@section('title', ($editing ? __('global_company.edit') : __('global_company.create')).' | '.__('global_company.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$editing ? __('global_company.edit') : __('global_company.create')"
        :breadcrumbs="[['label' => __('global_company.title'), 'href' => route('global-admin.companies.index')], ['label' => $editing ? __('global_company.edit') : __('global_company.create')]]"
    />

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        @if ($errors->any())
            <x-ui.alert class="mb-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.companies.update', $company) : route('global-admin.companies.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field :label="__('global_company.name')" for="name" required :error="$errors->first('name')">
                <x-ui.input id="name" name="name" :value="old('name', $company?->name)" required />
            </x-ui.field>

            <x-ui.field :label="__('global_company.code')" for="code" required :error="$errors->first('code')">
                <x-ui.input id="code" name="code" :value="old('code', $company?->code)" required />
            </x-ui.field>

            @if ($editing)
                <x-ui.checkbox name="is_active" value="1" :checked="old('is_active', $company->is_active)">{{ __('global_company.active') }}</x-ui.checkbox>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.companies.show', $company) : route('global-admin.companies.index')" variant="secondary" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_company.save') : __('global_company.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection