@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_calendar'))
@section('client-page-title', __('ui.production_calendar'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <h1 class="font-display text-3xl font-bold">{{ __('ui.production_calendar') }}</h1>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block text-sm font-medium">Centro de trabalho
                <x-ui.select class="mt-2" name="work_center_id" data-search="on" required>
                    <option value="">Selecione</option>
                    @foreach ($workCenters as $center)
                        <option value="{{ $center->id }}" @selected($workCenterId === (int) $center->id)>{{ $center->code }} - {{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">De
                <x-ui.input class="mt-2" type="date" name="from_date" :value="$fromDate" required />
            </label>
            <label class="block text-sm font-medium">Ate
                <x-ui.input class="mt-2" type="date" name="to_date" :value="$toDate" required />
            </label>
            <div class="flex items-end">
                <x-ui.button type="submit" variant="surface-muted" class="rounded-full" :full="true">Consultar</x-ui.button>
            </div>
        </form>

        <form method="POST" action="{{ route('production.calendar.generate') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <input type="hidden" name="work_center_id" value="{{ $workCenterId }}" />
            <input type="hidden" name="from_date" value="{{ $fromDate }}" />
            <input type="hidden" name="to_date" value="{{ $toDate }}" />
            <div class="lg:col-span-4">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Gerar calendario no periodo</x-ui.button>
            </div>
        </form>
    </x-ui.panel>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Atualizar dia</h2>
            <form method="POST" action="{{ route('production.calendar.days.upsert') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="work_center_id" value="{{ $workCenterId }}" />
                <label class="block text-sm font-medium">Data
                    <x-ui.input class="mt-2" type="date" name="calendar_date" required />
                </label>
                <label class="block text-sm font-medium">Capacidade disponivel
                    <x-ui.input class="mt-2" type="number" step="0.01" min="0" name="available_capacity" />
                </label>
                <label class="block text-sm font-medium sm:col-span-2">Observacoes
                    <x-ui.input class="mt-2" name="notes" maxlength="255" />
                </label>
                <label class="inline-flex items-center gap-2 text-sm sm:col-span-2">
                    <input type="hidden" name="is_working_day" value="0" />
                    <input type="checkbox" name="is_working_day" value="1" checked /> Dia util
                </label>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full sm:col-span-2">Salvar dia</x-ui.button>
            </form>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Dias no periodo</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                            <th class="px-3 py-2">Data</th>
                            <th class="px-3 py-2">Dia util</th>
                            <th class="px-3 py-2">Capacidade</th>
                            <th class="px-3 py-2">Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($days as $day)
                            <tr class="border-b border-[#f1f3f4]">
                                <td class="px-3 py-2">{{ $day->calendar_date?->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ $day->is_working_day ? 'Sim' : 'Nao' }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $day->available_capacity, 2, ',', '.') }}</td>
                                <td class="px-3 py-2">{{ $day->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-[#5f6368]">Sem dados para o filtro informado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
</div>
@endsection
