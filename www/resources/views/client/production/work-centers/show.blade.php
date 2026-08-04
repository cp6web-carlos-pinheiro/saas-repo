@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.work_centers'))
@section('client-page-title', __('ui.work_centers'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $workCenter->code }} - {{ $workCenter->name }}</h1>
        <x-ui.button :href="route('production.work-centers.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <x-ui.definition-grid>
            <x-ui.definition-item label="Planta">{{ $workCenter->plant?->code }} - {{ $workCenter->plant?->name }}</x-ui.definition-item>
            <x-ui.definition-item label="Tipo">{{ $workCenter->resource_type }}</x-ui.definition-item>
            <x-ui.definition-item label="Capacidade/dia">{{ number_format((float) $workCenter->capacity_per_day, 2, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item label="Eficiência">{{ number_format((float) $workCenter->efficiency_factor, 2, ',', '.') }}%</x-ui.definition-item>
            <x-ui.definition-item label="Status">{{ $workCenter->is_active ? 'Ativo' : 'Inativo' }}</x-ui.definition-item>
        </x-ui.definition-grid>
    </x-ui.panel>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Adicionar turno</h2>
            <form method="POST" action="{{ route('production.work-centers.shifts.store', $workCenter) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="block text-sm font-medium">Nome
                    <x-ui.input class="mt-2" name="name" required />
                </label>
                <label class="block text-sm font-medium">Capacidade (h)
                    <x-ui.input class="mt-2" name="capacity_hours" type="number" min="0" step="0.01" required />
                </label>
                <label class="block text-sm font-medium">Inicio
                    <x-ui.input class="mt-2" name="shift_start" type="time" required />
                </label>
                <label class="block text-sm font-medium">Fim
                    <x-ui.input class="mt-2" name="shift_end" type="time" required />
                </label>
                <label class="inline-flex items-center gap-2 text-sm sm:col-span-2">
                    <input type="checkbox" name="is_active" value="1" checked /> Ativo
                </label>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full sm:col-span-2">Salvar turno</x-ui.button>
            </form>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Turnos cadastrados</h2>
            <div class="mt-4 space-y-2 text-sm">
                @forelse ($workCenter->shifts as $shift)
                    <div class="rounded-xl border border-[#dadce0] p-3">
                        <div><strong>{{ $shift->name }}</strong> ({{ $shift->shift_start }} - {{ $shift->shift_end }})</div>
                        <div class="text-[#5f6368]">Capacidade: {{ number_format((float) $shift->capacity_hours, 2, ',', '.') }}h | {{ $shift->is_active ? 'Ativo' : 'Inativo' }}</div>
                    </div>
                @empty
                    <div class="text-[#5f6368]">Nenhum turno cadastrado.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Ultimos dias de calendario</h2>
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
                    @forelse ($workCenter->calendarDays as $day)
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-2">{{ $day->calendar_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">{{ $day->is_working_day ? 'Sim' : 'Nao' }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $day->available_capacity, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ $day->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-[#5f6368]">Sem dias registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
</div>
@endsection
