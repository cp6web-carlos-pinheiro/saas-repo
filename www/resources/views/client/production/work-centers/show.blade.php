@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.work_centers'))
@section('client-page-title', __('ui.work_centers'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $workCenter->code }} - {{ $workCenter->name }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.work-centers.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('production.work-centers.edit', $workCenter)" variant="primary" class="rounded-full">{{ __('production.edit') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('production.plant')">{{ $workCenter->plant?->code }} - {{ $workCenter->plant?->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.type')">{{ __('production.work_centers.'.strtolower($workCenter->resource_type)) }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.capacity_per_day')">{{ number_format((float) $workCenter->capacity_per_day, 2, ',', '.') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.efficiency')">{{ number_format((float) $workCenter->efficiency_factor, 2, ',', '.') }}%</x-ui.definition-item>
            <x-ui.definition-item :label="__('production.status')">{{ $workCenter->is_active ? __('production.active') : __('production.inactive') }}</x-ui.definition-item>
        </x-ui.definition-grid>
    </x-ui.panel>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.work_centers.add_shift') }}</h2>
            <form method="POST" action="{{ route('production.work-centers.shifts.store', $workCenter) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="block text-sm font-medium">{{ __('production.work_centers.shift_name') }}
                    <x-ui.input class="mt-2" name="name" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.work_centers.shift_capacity') }}
                    <x-ui.input class="mt-2" name="capacity_hours" type="number" min="0" step="0.01" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.work_centers.shift_start') }}
                    <x-ui.input class="mt-2" name="shift_start" type="time" required />
                </label>
                <label class="block text-sm font-medium">{{ __('production.work_centers.shift_end') }}
                    <x-ui.input class="mt-2" name="shift_end" type="time" required />
                </label>
                <div class="sm:col-span-2">
                    <x-ui.checkbox name="is_active" value="1" :checked="true">{{ __('production.active') }}</x-ui.checkbox>
                </div>
                <x-ui.button type="submit" variant="primary" class="rounded-full sm:col-span-2">{{ __('production.work_centers.save_shift') }}</x-ui.button>
            </form>
        </x-ui.panel>

        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.work_centers.registered_shifts') }}</h2>
            <div class="mt-4 space-y-2 text-sm">
                @forelse ($workCenter->shifts as $shift)
                    <div class="rounded-xl border border-[var(--ui-border)] p-3">
                        <div><strong>{{ $shift->name }}</strong> ({{ $shift->shift_start }} - {{ $shift->shift_end }})</div>
                        <div class="text-[var(--ui-text-muted)]">{{ __('production.work_centers.shift_summary', ['capacity' => number_format((float) $shift->capacity_hours, 2, ',', '.'), 'status' => $shift->is_active ? __('production.active') : __('production.inactive')]) }}</div>
                    </div>
                @empty
                    <div class="text-[var(--ui-text-muted)]">{{ __('production.work_centers.no_shifts') }}</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">{{ __('production.work_centers.latest_calendar_days') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <x-ui.table :caption="__('production.work_centers.latest_calendar_days')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-2">{{ __('production.date') }}</th>
                        <th class="px-3 py-2">{{ __('production.work_centers.working_day') }}</th>
                        <th class="px-3 py-2">{{ __('production.capacity') }}</th>
                        <th class="px-3 py-2">{{ __('production.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workCenter->calendarDays as $day)
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-2">{{ $day->calendar_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">{{ $day->is_working_day ? __('production.yes') : __('production.no') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $day->available_capacity, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ $day->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.work_centers.no_days') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
