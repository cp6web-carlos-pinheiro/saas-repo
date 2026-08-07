@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', __('ui.module_scheduling'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">Resultado da programação</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.scheduling.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('production.scheduling.edit', ['run' => $runKey])" variant="material-edit" class="rounded-full">Editar parâmetros</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <x-ui.definition-grid>
            <x-ui.definition-item label="Data de referência">{{ $input['reference_date'] ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item label="Modo">{{ strtoupper((string) ($input['mode'] ?? '—')) }}</x-ui.definition-item>
            <x-ui.definition-item label="Direção">{{ strtoupper((string) ($input['direction'] ?? '—')) }}</x-ui.definition-item>
            <x-ui.definition-item label="Regra">{{ $input['sequencing_rule'] ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item label="Ordens selecionadas">{{ is_array($input['production_order_ids'] ?? null) ? count($input['production_order_ids']) : 0 }}</x-ui.definition-item>
        </x-ui.definition-grid>
    </x-ui.panel>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Sequenciamento calculado</h2>
        <div class="mt-4 space-y-4">
            @foreach (($result['orders'] ?? []) as $scheduledOrder)
                <div class="rounded-xl border border-[#dadce0] p-4">
                    <div class="font-medium">{{ $scheduledOrder['order_number'] ?? '—' }} | Venda: {{ $scheduledOrder['sales_order_reference'] ?? '—' }} | {{ strtoupper((string) ($scheduledOrder['mode'] ?? '—')) }} | {{ strtoupper((string) ($scheduledOrder['direction'] ?? '—')) }}</div>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                                    <th class="px-2 py-1">Seq</th>
                                    <th class="px-2 py-1">Operação</th>
                                    <th class="px-2 py-1">Centro</th>
                                    <th class="px-2 py-1">Início</th>
                                    <th class="px-2 py-1">Fim</th>
                                    <th class="px-2 py-1">Duração (HH:MM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (($scheduledOrder['operations'] ?? []) as $operation)
                                    <tr class="border-b border-[#f1f3f4]">
                                        <td class="px-2 py-1">{{ $operation['sequence'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['operation_no'] ?? '—' }} - {{ $operation['operation_name'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['work_center_id'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['start_at'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['end_at'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ isset($operation['duration_minutes']) ? \App\Support\Duration::formatMinutes((float) $operation['duration_minutes']) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.panel>
</div>
@endsection
