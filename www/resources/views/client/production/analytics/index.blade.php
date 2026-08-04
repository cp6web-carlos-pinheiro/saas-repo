@extends('layouts.client-area')

@section('title', __('ui.module_production').' | Indicadores de produção')
@section('client-page-title', 'Indicadores de produção')

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">Indicadores de produção</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.orders.index')" variant="material-back" class="rounded-full">Ordens de produção</x-ui.button>
        </div>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <label class="text-sm font-medium">Período
                <x-ui.select class="mt-2" name="days" data-search="off">
                    @foreach ([7, 14, 30, 60, 90, 180] as $days)
                        <option value="{{ $days }}" @selected($period === $days)>{{ $days }} dias</option>
                    @endforeach
                </x-ui.select>
            </label>
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">Atualizar</x-ui.button>
        </form>
    </x-ui.panel>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[#5f6368]">Aderência ao plano</div>
            <div class="mt-2 text-3xl font-bold text-[#174ea6]">{{ number_format($planAdherence, 2, ',', '.') }}%</div>
        </x-ui.panel>
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[#5f6368]">Taxa de qualidade</div>
            <div class="mt-2 text-3xl font-bold text-[#137333]">{{ number_format($qualityRate, 2, ',', '.') }}%</div>
        </x-ui.panel>
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[#5f6368]">Tempo de setup</div>
            <div class="mt-2 text-3xl font-bold text-[#b06000]">{{ number_format($setupMinutes, 1, ',', '.') }} min</div>
        </x-ui.panel>
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[#5f6368]">Tempo de processo</div>
            <div class="mt-2 text-3xl font-bold text-[#5e35b1]">{{ number_format($processMinutes, 1, ',', '.') }} min</div>
        </x-ui.panel>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Ordens por status</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-[#dadce0] p-4">Draft: <strong>{{ $statusCards['draft'] }}</strong></div>
                <div class="rounded-xl border border-[#dadce0] p-4">Released: <strong>{{ $statusCards['released'] }}</strong></div>
                <div class="rounded-xl border border-[#dadce0] p-4">In progress: <strong>{{ $statusCards['in_progress'] }}</strong></div>
                <div class="rounded-xl border border-[#dadce0] p-4">Completed: <strong>{{ $statusCards['completed'] }}</strong></div>
            </div>

            <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide text-[#5f6368]">Checkpoints de inspeção</h3>
            <div class="mt-3 flex flex-wrap gap-3">
                <span class="rounded-full border border-[#dadce0] px-3 py-1 text-sm">APPROVED: <strong>{{ $approvedCount }}</strong></span>
                <span class="rounded-full border border-[#dadce0] px-3 py-1 text-sm">PENDING: <strong>{{ $pendingCount }}</strong></span>
                <span class="rounded-full border border-[#dadce0] px-3 py-1 text-sm">REJECTED: <strong>{{ $rejectedCount }}</strong></span>
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Refugo por dia</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                            <th class="px-3 py-2">Data</th>
                            <th class="px-3 py-2">Total refugo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scrapByDay as $row)
                            <tr class="border-b border-[#f1f3f4]">
                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $row->total_scrap, 3, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-3 py-6 text-center text-[#5f6368]">Sem registros no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Eficiência por operação</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-2">Operação</th>
                        <th class="px-3 py-2">Qtd boa</th>
                        <th class="px-3 py-2">Qtd refugo</th>
                        <th class="px-3 py-2">Tempo processo (min)</th>
                        <th class="px-3 py-2">Produtividade (qtd/min)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($operationEfficiency as $row)
                        @php
                            $processMinutes = max(0.000001, (float) $row->process_minutes);
                            $productivity = (float) $row->good_qty / $processMinutes;
                        @endphp
                        <tr class="border-b border-[#f1f3f4]">
                            <td class="px-3 py-2">{{ $row->operation_no }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $row->good_qty, 3, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $row->scrap_qty, 3, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $row->process_minutes, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format($productivity, 4, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[#5f6368]">Sem dados de operação no período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
</div>
@endsection
