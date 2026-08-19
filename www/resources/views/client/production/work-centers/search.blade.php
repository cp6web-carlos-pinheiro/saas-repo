@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.work_centers'))
@section('client-page-title', __('ui.work_centers'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.work_centers') }}</h1>
        <x-ui.button :href="route('production.work-centers.create')" variant="primary" class="rounded-full">{{ __('production.work_centers.new_short') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <x-ui.input name="search" :value="$search" class="min-w-0 flex-1" :placeholder="__('production.work_centers.search')" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('production.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('production.work-centers.index', array_merge(['search' => $search, 'status' => $status], $overrides)))
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('production.all') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('production.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('production.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('ui.work_centers')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header class="py-2" column="code" :label="__('production.code')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="name" :label="__('production.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="plant" :label="__('production.plant')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="resource_type" :label="__('production.type')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="capacity_per_day" :label="__('production.capacity_per_day')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="efficiency_factor" :label="__('production.efficiency')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="is_active" :label="__('production.status')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($centers as $center)
                        <tr class="cursor-pointer border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]" tabindex="0"
                            onclick="window.location='{{ route('production.work-centers.show', $center) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('production.work-centers.show', $center) }}'; }"
                        >
                            <td class="px-3 py-2">{{ $center->code }}</td>
                            <td class="px-3 py-2">{{ $center->name }}</td>
                            <td class="px-3 py-2">{{ $center->plant?->code }} - {{ $center->plant?->name }}</td>
                            <td class="px-3 py-2">{{ __('production.work_centers.'.strtolower($center->resource_type)) }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $center->capacity_per_day, 2, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $center->efficiency_factor, 2, ',', '.') }}%</td>
                            <td class="px-3 py-2">{{ $center->is_active ? __('production.active') : __('production.inactive') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.work_centers.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $centers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
