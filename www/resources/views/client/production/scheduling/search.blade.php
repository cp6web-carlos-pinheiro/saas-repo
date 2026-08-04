@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', __('ui.module_scheduling'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.module_scheduling') }}</h1>
        <x-ui.button :href="route('production.scheduling.create')" variant="brand-primary" class="rounded-full">Nova programação</x-ui.button>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <p class="text-sm text-[#5f6368]">Este módulo executa programação finita/infinita com base nas ordens liberadas e snapshots de roteamento.</p>
    </x-ui.panel>
</div>
@endsection
