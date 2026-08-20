@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', __('ui.module_scheduling'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('ui.module_scheduling') }}">
        <x-slot:actions>
        <x-ui.button :href="route('production.scheduling.create')" variant="primary" class="rounded-full">{{ __('production.scheduling.new') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <p class="text-sm text-[var(--ui-text-muted)]">{{ __('production.scheduling.description') }}</p>
    </x-ui.panel>
</div>
@endsection
