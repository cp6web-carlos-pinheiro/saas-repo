@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_calendar'))
@section('client-page-title', __('ui.production_calendar'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.production_calendar') }}</h1>
        <x-ui.button :href="route('production.calendar.create')" variant="brand-primary" class="rounded-full">Novo dia</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5" method="GET">
            <label class="block text-sm font-medium sm:col-span-2">Centro de trabalho
                <x-ui.select class="mt-2" name="work_center_id" data-search="on">
                    <option value="">Todos</option>
                    @foreach ($workCenters as $center)
                        <option value="{{ $center->id }}" @selected($workCenterId === (int) $center->id)>{{ $center->code }} - {{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">De
                <x-ui.input class="mt-2" type="date" name="from_date" :value="$fromDate" required />
            </label>
            <label class="block text-sm font-medium">Até
                <x-ui.input class="mt-2" type="date" name="to_date" :value="$toDate" required />
            </label>
            <div class="flex items-end">
                <x-ui.button type="submit" variant="surface-muted" class="rounded-full" :full="true">Filtrar</x-ui.button>
            </div>
        </form>

        <form method="POST" action="{{ route('production.calendar.generate') }}" class="mt-4">
            @csrf
            <input type="hidden" name="work_center_id" value="{{ $workCenterId }}" />
            <input type="hidden" name="from_date" value="{{ $fromDate }}" />
            <input type="hidden" name="to_date" value="{{ $toDate }}" />
            <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Gerar calendário no período</x-ui.button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-2">Data</th>
                        <th class="px-3 py-2">Centro de trabalho</th>
                        <th class="px-3 py-2">Dia útil</th>
                        <th class="px-3 py-2">Capacidade</th>
                        <th class="px-3 py-2">Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($days as $day)
                        <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" tabindex="0"
                            onclick="window.location='{{ route('production.calendar.show', $day) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('production.calendar.show', $day) }}'; }"
                        >
                            <td class="px-3 py-2">{{ $day->calendar_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">{{ $day->workCenter?->code }} - {{ $day->workCenter?->name }}</td>
                            <td class="px-3 py-2">{{ $day->is_working_day ? 'Sim' : 'Não' }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $day->available_capacity, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ $day->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[#5f6368]">Sem dados para o filtro informado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $days->links() }}</div>
    </x-ui.panel>
</div>
@endsection
