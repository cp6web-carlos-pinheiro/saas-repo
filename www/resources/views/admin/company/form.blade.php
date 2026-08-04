@extends('layouts.global-admin')
@php($editing = $company !== null)
@section('title', ($editing ? __('global_company.edit') : __('global_company.create')).' | '.__('global_company.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_company.title'), 'href' => route('global-admin.companies.index')], ['label' => $editing ? __('global_company.edit') : __('global_company.create')]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('global_company.edit') : __('global_company.create') }}</h1>

        @if ($errors->any())
            <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.companies.update', $company) : route('global-admin.companies.store') }}" class="mt-6 space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('global_company.name') }}
                <x-ui.input name="name" :value="old('name', $company?->name)" required @class(['mt-2', 'border-red-500' => $errors->has('name'), 'border-[#dadce0]' => ! $errors->has('name')]) />
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_company.code') }}
                <x-ui.input name="code" :value="old('code', $company?->code)" required @class(['mt-2', 'border-red-500' => $errors->has('code'), 'border-[#dadce0]' => ! $errors->has('code')]) />
                @error('code')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            @if ($editing)
                <label class="flex items-center gap-2 rounded-xl border border-[#dadce0] bg-white px-4 py-3 text-sm text-[#202124]">
                    <input type="hidden" name="is_active" value="0" />
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $company->is_active)) class="h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
                    <span>{{ __('global_company.active') }}</span>
                </label>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.companies.show', $company) : route('global-admin.companies.index')" variant="surface-muted" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="brand-primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_company.save') : __('global_company.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
