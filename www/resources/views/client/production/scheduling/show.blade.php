@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', __('ui.module_scheduling'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('production.scheduling.result') }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.scheduling.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('production.scheduling.edit', ['run' => $runKey])" variant="primary" class="rounded-full">{{ __('production.scheduling.edit_parameters') }}</x-ui.button>
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('production.scheduling.reference_date')">{{ $input['reference_date'] ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.scheduling.mode')">{{ __('ui.scheduling_mode_'.($input['mode'] ?? 'finite')) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.scheduling.direction')">{{ __('ui.scheduling_direction_'.($input['direction'] ?? 'forward')) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.scheduling.rule')">{{ __('ui.scheduling_rule_'.($input['sequencing_rule'] ?? 'priority_due_date')) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.scheduling.selected_orders')">{{ is_array($input['production_order_ids'] ?? null) ? count($input['production_order_ids']) : 0 }}</x-ui.definition-item>
        </x-ui.definition-grid>
    </x-ui.panel>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">{{ __('production.scheduling.calculated_sequence') }}</h2>
        <div class="mt-4 space-y-4">
            @foreach (($result['orders'] ?? []) as $scheduledOrder)
                <div class="rounded-xl border border-[var(--ui-border)] p-4">
                    <div class="font-medium">{{ $scheduledOrder['order_number'] ?? '—' }} | {{ __('production.scheduling.sale', ['reference' => $scheduledOrder['sales_order_reference'] ?? '—']) }} | {{ __('ui.scheduling_mode_'.($scheduledOrder['mode'] ?? 'finite')) }} | {{ __('ui.scheduling_direction_'.($scheduledOrder['direction'] ?? 'forward')) }}</div>
                    <div class="mt-2 overflow-x-auto">
                        <x-ui.table :caption="__('ui.module_scheduling')">
                            <thead>
                                <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                                    <th class="px-2 py-1">{{ __('production.sequence') }}</th>
                                    <th class="px-2 py-1">{{ __('production.operation') }}</th>
                                    <th class="px-2 py-1">{{ __('production.scheduling.center') }}</th>
                                    <th class="px-2 py-1">{{ __('production.scheduling.start') }}</th>
                                    <th class="px-2 py-1">{{ __('production.scheduling.end') }}</th>
                                    <th class="px-2 py-1">{{ __('production.scheduling.duration') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (($scheduledOrder['operations'] ?? []) as $operation)
                                    <tr class="border-b border-[var(--ui-border)]">
                                        <td class="px-2 py-1">{{ $operation['sequence'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['operation_no'] ?? '—' }} - {{ $operation['operation_name'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['work_center_id'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['start_at'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ $operation['end_at'] ?? '—' }}</td>
                                        <td class="px-2 py-1">{{ isset($operation['duration_minutes']) ? \App\Support\Duration::formatMinutes((float) $operation['duration_minutes']) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.panel>
</div>
@endsection
