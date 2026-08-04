@extends('layouts.global-admin')
@php($editing = $tutorial !== null)
@section('title', ($editing ? __('global_tutorial.edit') : __('global_tutorial.create')).' | '.__('global_tutorial.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_tutorial.title'), 'href' => route('global-admin.tutorials.index')], ['label' => $editing ? __('global_tutorial.edit') : __('global_tutorial.create')]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('global_tutorial.edit') : __('global_tutorial.create') }}</h1>

        @if ($errors->any())
            <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.tutorials.update', $tutorial) : route('global-admin.tutorials.store') }}" class="mt-6 space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('global_tutorial.route_name') }}
                <x-ui.input name="route_name" :value="old('route_name', $tutorial?->route_name)" required @class(['mt-2', 'border-red-500' => $errors->has('route_name'), 'border-[#dadce0]' => ! $errors->has('route_name')]) />
                @error('route_name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_tutorial.page_title') }}
                <x-ui.input name="title" :value="old('title', $tutorial?->title)" @class(['mt-2', 'border-red-500' => $errors->has('title'), 'border-[#dadce0]' => ! $errors->has('title')]) />
                @error('title')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_tutorial.content_html') }}
                <x-ui.textarea name="content_html" rows="16" required @class(['mt-2 font-mono text-xs', 'border-red-500' => $errors->has('content_html'), 'border-[#dadce0]' => ! $errors->has('content_html')])>{{ old('content_html', $tutorial?->content_html) }}</x-ui.textarea>
                @error('content_html')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            @php($previewHtml = old('content_html', $tutorial?->content_html ?? ''))
            @if ($previewHtml !== '')
                <div class="rounded-2xl border border-[#dadce0] bg-white p-4">
                    <h2 class="text-sm font-semibold text-[#5f6368]">{{ __('global_tutorial.preview') }}</h2>
                    <div class="mt-3 prose prose-slate max-w-none text-sm">
                        {!! $previewHtml !!}
                    </div>
                </div>
            @endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.tutorials.show', $tutorial) : route('global-admin.tutorials.index')" variant="surface-muted" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="brand-primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_tutorial.save') : __('global_tutorial.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
