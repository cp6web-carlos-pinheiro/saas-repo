@extends('layouts.client-area')

@php($editing = $runKey !== null)

@section('title', __('ui.module_production').' | '.__('ui.module_scheduling'))
@section('client-page-title', $editing ? __('production.scheduling.edit') : __('production.scheduling.new'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('production.scheduling.edit') : __('production.scheduling.new') }}</h1>
        <x-ui.button :href="$editing ? route('production.scheduling.show', ['run' => $runKey]) : route('production.scheduling.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('production.scheduling.update', ['run' => $runKey]) : route('production.scheduling.run') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 lg:grid-cols-4">
                <label class="block text-sm font-medium">{{ __('ui.scheduling_reference_date') }}
                    <x-ui.input class="mt-2" type="date" name="reference_date" :value="old('reference_date', $input['reference_date'] ?? now()->toDateString())" />
                </label>
                <label class="block text-sm font-medium">{{ __('ui.scheduling_mode') }}
                    <x-ui.select class="mt-2" name="mode" data-search="off">
                        <option value="finite" @selected(old('mode', $input['mode'] ?? 'finite') === 'finite')>{{ __('ui.scheduling_mode_finite') }}</option>
                        <option value="infinite" @selected(old('mode', $input['mode'] ?? 'finite') === 'infinite')>{{ __('ui.scheduling_mode_infinite') }}</option>
                    </x-ui.select>
                </label>
                <label class="block text-sm font-medium">{{ __('ui.scheduling_direction') }}
                    <x-ui.select class="mt-2" name="direction" data-search="off">
                        <option value="forward" @selected(old('direction', $input['direction'] ?? 'forward') === 'forward')>{{ __('ui.scheduling_direction_forward') }}</option>
                        <option value="backward" @selected(old('direction', $input['direction'] ?? 'forward') === 'backward')>{{ __('ui.scheduling_direction_backward') }}</option>
                    </x-ui.select>
                </label>
                <label class="block text-sm font-medium">{{ __('ui.scheduling_sequencing_rule') }}
                    <x-ui.select class="mt-2" name="sequencing_rule" data-search="off">
                        <option value="priority_due_date" @selected(old('sequencing_rule', $input['sequencing_rule'] ?? 'priority_due_date') === 'priority_due_date')>{{ __('ui.scheduling_rule_priority_due_date') }}</option>
                        <option value="due_date_priority" @selected(old('sequencing_rule', $input['sequencing_rule'] ?? 'priority_due_date') === 'due_date_priority')>{{ __('ui.scheduling_rule_due_date_priority') }}</option>
                        <option value="release_date_priority" @selected(old('sequencing_rule', $input['sequencing_rule'] ?? 'priority_due_date') === 'release_date_priority')>{{ __('ui.scheduling_rule_release_date_priority') }}</option>
                        <option value="order_number" @selected(old('sequencing_rule', $input['sequencing_rule'] ?? 'priority_due_date') === 'order_number')>{{ __('ui.scheduling_rule_order_number') }}</option>
                    </x-ui.select>
                </label>
            </div>

            @php($selectedOrders = collect(old('production_order_ids', $input['production_order_ids'] ?? []))->map(static fn ($id) => (int) $id)->all())
            <label class="block text-sm font-medium">{{ __('ui.scheduling_production_orders_multi') }}
                <select class="ui-select mt-2 w-full" name="production_order_ids[]" multiple size="10" required>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}" @selected(in_array((int) $order->id, $selectedOrders, true))>
                            {{ $order->order_number }} | {{ __('production.scheduling.sale', ['reference' => $order->sales_order_reference ?? '—']) }} | {{ __('ui.production_order_status_'.$order->status) }} | {{ optional($order->scheduled_end_date)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('production.scheduling.show', ['run' => $runKey]) : route('production.scheduling.index')" variant="material-back" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ __('ui.scheduling_run') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection
