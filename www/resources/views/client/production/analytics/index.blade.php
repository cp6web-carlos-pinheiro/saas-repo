@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('production.analytics.title'))
@section('client-page-title', __('production.analytics.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('production.analytics.title') }}">
        <x-slot:actions>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('production.orders.index')" variant="secondary" class="rounded-full">{{ __('production.analytics.orders') }}</x-ui.button>
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <label class="text-sm font-medium">{{ __('production.analytics.period') }}
                <x-ui.select class="mt-2" name="days" data-search="off">
                    @foreach ([7, 14, 30, 60, 90, 180] as $days)
                        <option value="{{ $days }}" @selected($period === $days)>{{ __('production.analytics.days', ['count' => $days]) }}</option>
                    @endforeach
                </x-ui.select>
            </label>
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('production.analytics.update') }}</x-ui.button>
        </form>
    </x-ui.panel>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.analytics.plan_adherence') }}</div>
            <div class="mt-2 text-3xl font-bold text-[var(--ui-primary-text)]">{{ number_format($planAdherence, 2, ',', '.') }}%</div>
        </x-ui.panel>
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.analytics.quality_rate') }}</div>
            <div class="mt-2 text-3xl font-bold text-[var(--ui-success)]">{{ number_format($qualityRate, 2, ',', '.') }}%</div>
        </x-ui.panel>
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.analytics.setup_time') }}</div>
            <div class="mt-2 text-3xl font-bold text-[var(--ui-warning-text)]">{{ \App\Support\Duration::formatMinutes($setupMinutes) }}</div>
        </x-ui.panel>
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-5">
            <div class="text-xs uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.analytics.process_time') }}</div>
            <div class="mt-2 text-3xl font-bold text-[var(--ui-info)]">{{ \App\Support\Duration::formatMinutes($processMinutes) }}</div>
        </x-ui.panel>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.analytics.orders_by_status') }}</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-[var(--ui-border)] p-4">{{ __('production.status_labels.DRAFT') }}: <strong>{{ $statusCards['draft'] }}</strong></div>
                <div class="rounded-xl border border-[var(--ui-border)] p-4">{{ __('production.status_labels.RELEASED') }}: <strong>{{ $statusCards['released'] }}</strong></div>
                <div class="rounded-xl border border-[var(--ui-border)] p-4">{{ __('production.status_labels.IN_PROGRESS') }}: <strong>{{ $statusCards['in_progress'] }}</strong></div>
                <div class="rounded-xl border border-[var(--ui-border)] p-4">{{ __('production.status_labels.COMPLETED') }}: <strong>{{ $statusCards['completed'] }}</strong></div>
            </div>

            <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('production.orders.inspection_checkpoints') }}</h3>
            <div class="mt-3 flex flex-wrap gap-3">
                <span class="rounded-full border border-[var(--ui-border)] px-3 py-1 text-sm">{{ __('production.status_labels.APPROVED') }}: <strong>{{ $approvedCount }}</strong></span>
                <span class="rounded-full border border-[var(--ui-border)] px-3 py-1 text-sm">{{ __('production.status_labels.PENDING') }}: <strong>{{ $pendingCount }}</strong></span>
                <span class="rounded-full border border-[var(--ui-border)] px-3 py-1 text-sm">{{ __('production.status_labels.REJECTED') }}: <strong>{{ $rejectedCount }}</strong></span>
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-6">
            <h2 class="font-display text-xl font-bold">{{ __('production.analytics.scrap_by_day') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <x-ui.table :caption="__('production.analytics.title')">
                    <thead>
                        <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                            <th class="px-3 py-2">{{ __('production.date') }}</th>
                            <th class="px-3 py-2">{{ __('production.analytics.total_scrap') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scrapByDay as $row)
                            <tr class="border-b border-[var(--ui-border)]">
                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $row->total_scrap, 3, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.analytics.no_period_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6">
        <h2 class="font-display text-xl font-bold">{{ __('production.analytics.efficiency_by_operation') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <x-ui.table :caption="__('production.analytics.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <th class="px-3 py-2">{{ __('production.operation') }}</th>
                        <th class="px-3 py-2">{{ __('production.analytics.good_quantity') }}</th>
                        <th class="px-3 py-2">{{ __('production.orders.scrap_quantity') }}</th>
                        <th class="px-3 py-2">{{ __('production.analytics.process_time_hhmm') }}</th>
                        <th class="px-3 py-2">{{ __('production.analytics.productivity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($operationEfficiency as $row)
                        @php
                            $processMinutes = max(0.000001, (float) $row->process_minutes);
                            $productivity = (float) $row->good_qty / $processMinutes;
                        @endphp
                        <tr class="border-b border-[var(--ui-border)]">
                            <td class="px-3 py-2">{{ $row->operation_no }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $row->good_qty, 3, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $row->scrap_qty, 3, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ \App\Support\Duration::formatMinutes((float) $row->process_minutes) }}</td>
                            <td class="px-3 py-2">{{ number_format($productivity, 4, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.analytics.no_operation_data') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>
</div>
@endsection
