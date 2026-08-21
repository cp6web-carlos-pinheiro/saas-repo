@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.production_calendar'))
@section('client-page-title', __('ui.production_calendar'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('ui.production_calendar') }}">
        <x-slot:actions>
        <x-ui.button :href="route('production.calendar.create')" variant="primary" class="rounded-full">{{ __('production.calendar.new_short') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5" method="GET">
            <label class="block text-sm font-medium sm:col-span-2">{{ __('production.work_center') }}
                <x-ui.select class="mt-2" name="work_center_id" data-search="on">
                    <option value="">{{ __('production.all') }}</option>
                    @foreach ($workCenters as $center)
                        <option value="{{ $center->id }}" @selected($workCenterId === (int) $center->id)>{{ $center->code }} - {{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </label>
            <label class="block text-sm font-medium">{{ __('production.calendar.from') }}
                <x-ui.date-picker class="mt-2" name="from_date" :value="$fromDate" required />
            </label>
            <label class="block text-sm font-medium">{{ __('production.calendar.until') }}
                <x-ui.date-picker class="mt-2" name="to_date" :value="$toDate" required />
            </label>
            <div class="flex items-end">
                <x-ui.button type="submit" variant="secondary" class="rounded-full" :full="true">{{ __('production.filter') }}</x-ui.button>
            </div>
        </form>

        <form method="POST" action="{{ route('production.calendar.generate') }}" class="mt-4">
            @csrf
            <x-ui.input type="hidden" name="work_center_id" :value="$workCenterId" unstyled />
            <x-ui.input type="hidden" name="from_date" :value="$fromDate" unstyled />
            <x-ui.input type="hidden" name="to_date" :value="$toDate" unstyled />
            <x-ui.button type="submit" variant="primary" class="rounded-full">{{ __('production.calendar.generate') }}</x-ui.button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('ui.production_calendar')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header class="py-2" column="calendar_date" :label="__('production.date')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="work_center" :label="__('production.work_center')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="is_working_day" :label="__('production.work_centers.working_day')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="available_capacity" :label="__('production.capacity')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="notes" :label="__('production.notes')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($days as $day)
                        <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                        >
                            <td class="px-3 py-2"><a href="{{ route('production.calendar.show', $day) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $day->calendar_date?->format('d/m/Y') }}</a></td>
                            <td class="px-3 py-2">{{ $day->workCenter?->code }} - {{ $day->workCenter?->name }}</td>
                            <td class="px-3 py-2">{{ $day->is_working_day ? __('production.yes') : __('production.no') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $day->available_capacity, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ $day->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.calendar.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $days->links() }}</div>
    </x-ui.panel>
</div>
@endsection
