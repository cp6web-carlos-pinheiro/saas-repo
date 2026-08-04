@extends('layouts.global-admin')
@section('title', ($tutorial->title ?: $tutorial->route_name).' | '.__('global_tutorial.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_tutorial.title'), 'href' => route('global-admin.tutorials.index')], ['label' => $tutorial->title ?: $tutorial->route_name]]"/>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-[#5f6368]">{{ __('global_tutorial.details') }}</p>
                <h1 class="font-display text-3xl font-bold">{{ $tutorial->title ?: $tutorial->route_name }}</h1>
                <p class="mt-1 font-mono text-xs text-[#5f6368]">{{ $tutorial->route_name }}</p>
            </div>
        </div>

        <dl class="mt-8 divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">ID</dt>
                <dd class="font-medium">{{ $tutorial->id }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_tutorial.route_name') }}</dt>
                <dd class="font-medium font-mono text-xs md:text-sm">{{ $tutorial->route_name }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_tutorial.page_title') }}</dt>
                <dd class="font-medium">{{ $tutorial->title ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_tutorial.created_at') }}</dt>
                <dd class="font-medium">{{ optional($tutorial->created_at)->format('d/m/Y H:i') ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-[#5f6368]">{{ __('global_tutorial.updated_at') }}</dt>
                <dd class="font-medium">{{ optional($tutorial->updated_at)->format('d/m/Y H:i') ?: '—' }}</dd>
            </div>
        </dl>

        <div class="mt-8 rounded-2xl border border-[#dadce0] bg-white p-4">
            <h2 class="text-sm font-semibold text-[#5f6368]">{{ __('global_tutorial.preview') }}</h2>
            <div class="prose prose-slate mt-3 max-w-none text-sm">
                {!! $tutorial->content_html !!}
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.tutorials.index')" variant="surface-muted" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.tutorials.edit', $tutorial)" variant="brand-primary" class="rounded-full">
                {{ __('global_tutorial.edit') }}
            </x-ui.button>

            <form method="POST" action="{{ route('global-admin.tutorials.destroy', $tutorial) }}" data-admin-delete-confirm data-admin-name="{{ $tutorial->route_name }}" data-confirm-title="{{ __('global_tutorial.confirm_delete_title') }}" data-confirm-text="{{ __('global_tutorial.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_tutorial.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_tutorial.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('global_tutorial.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
