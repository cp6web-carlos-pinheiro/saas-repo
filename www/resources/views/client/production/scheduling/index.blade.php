@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', __('ui.module_scheduling'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <h1 class="font-display text-3xl font-bold">{{ __('ui.module_scheduling') }}</h1>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">Executar programacao</h2>
        <form method="POST" action="{{ route('production.scheduling.run') }}" class="mt-4 grid gap-4 lg:grid-cols-4">
            @csrf
            <label class="block text-sm font-medium">Data de referencia
                <x-ui.input class="mt-2" type="date" name="reference_date" :value="$input['reference_date'] ?? now()->toDateString()" />
            </label>
            <label class="block text-sm font-medium">Modo
                <x-ui.select class="mt-2" name="mode" data-search="off">
                    <option value="finite" @selected(($input['mode'] ?? 'finite') === 'finite')>Finite</option>
                    <option value="infinite" @selected(($input['mode'] ?? 'finite') === 'infinite')>Infinite</option>
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">Direcao
                <x-ui.select class="mt-2" name="direction" data-search="off">
                    <option value="forward" @selected(($input['direction'] ?? 'forward') === 'forward')>Forward</option>
                    <option value="backward" @selected(($input['direction'] ?? 'forward') === 'backward')>Backward</option>
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">Regra de sequenciamento
                <x-ui.select class="mt-2" name="sequencing_rule" data-search="off">
                    <option value="priority_due_date" @selected(($input['sequencing_rule'] ?? 'priority_due_date') === 'priority_due_date')>priority_due_date</option>
                    <option value="due_date_priority" @selected(($input['sequencing_rule'] ?? 'priority_due_date') === 'due_date_priority')>due_date_priority</option>
                    <option value="release_date_priority" @selected(($input['sequencing_rule'] ?? 'priority_due_date') === 'release_date_priority')>release_date_priority</option>
                    <option value="order_number" @selected(($input['sequencing_rule'] ?? 'priority_due_date') === 'order_number')>order_number</option>
                </x-ui.select>
            </label>

            @php($selectedOrders = collect($input['production_order_ids'] ?? [])->map(static fn ($id) => (int) $id)->all())
            <label class="block text-sm font-medium lg:col-span-4">Ordens de producao (multi-selecao)
                <select class="ui-select mt-2 w-full" name="production_order_ids[]" multiple size="8" required>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}" @selected(in_array((int) $order->id, $selectedOrders, true))>
                            {{ $order->order_number }} | {{ $order->status }} | {{ optional($order->scheduled_end_date)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="lg:col-span-4">
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full">Executar programacao</x-ui.button>
            </div>
        </form>
    </x-ui.panel>

    @if (is_array($result ?? null) && ! empty($result['orders']))
        <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">Resultado da programacao</h2>
            <div class="mt-4 space-y-4">
                @foreach ($result['orders'] as $scheduledOrder)
                    <div class="rounded-xl border border-[#dadce0] p-4">
                        <div class="font-medium">{{ $scheduledOrder['order_number'] }} | {{ strtoupper((string) $scheduledOrder['mode']) }} | {{ strtoupper((string) $scheduledOrder['direction']) }}</div>
                        <div class="mt-2 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                                        <th class="px-2 py-1">Seq</th>
                                        <th class="px-2 py-1">Operacao</th>
                                        <th class="px-2 py-1">Centro</th>
                                        <th class="px-2 py-1">Inicio</th>
                                        <th class="px-2 py-1">Fim</th>
                                        <th class="px-2 py-1">Duracao (min)</th>
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
                                            <td class="px-2 py-1">{{ $operation['duration_minutes'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>
    @endif
</div>
@endsection
