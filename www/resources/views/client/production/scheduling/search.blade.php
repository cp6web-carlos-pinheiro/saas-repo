@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', __('ui.module_scheduling'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.module_scheduling') }}</h1>
        <x-ui.button :href="route('production.scheduling.create')" variant="primary" class="rounded-full">{{ __('production.scheduling.new') }}</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <p class="text-sm text-[var(--ui-text-muted)]">{{ __('production.scheduling.description') }}</p>
    </x-ui.panel>
</div>
@endsection
