@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_calendar'))
@section('client-page-title', __('ui.production_calendar'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">Dia {{ $day->calendar_date?->format('d/m/Y') }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.calendar.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('production.calendar.edit', $day)" variant="material-edit" class="rounded-full">Editar</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item label="Centro de trabalho">{{ $day->workCenter?->code }} - {{ $day->workCenter?->name }}</x-ui.definition-item>
            <x-ui.definition-item-date label="Data" :value="$day->calendar_date" />
            <x-ui.definition-item label="Dia útil">{{ $day->is_working_day ? 'Sim' : 'Não' }}</x-ui.definition-item>
            <x-ui.definition-item label="Capacidade disponível">{{ number_format((float) $day->available_capacity, 2, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item label="Observações">{{ $day->notes ?: '—' }}</x-ui.definition-item>
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
