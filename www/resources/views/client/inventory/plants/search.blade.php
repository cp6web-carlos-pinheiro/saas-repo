@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_plants'))
@section('client-page-title', __('plant.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('plant.title') }}</h1>
        <x-ui.button :href="route('inventory.plants.create')" variant="primary" class="rounded-full">{{ __('plant.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="plant-search" class="sr-only">{{ __('plant.search') }}</label>
            <x-ui.input id="plant-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('plant.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('plant.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('inventory.plants.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('plant.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('plant.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('plant.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('plant.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('plant.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="code" :label="__('plant.code')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="timezone" :label="__('plant.timezone')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="is_active" :label="__('plant.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('plant.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plants as $plant)
                        <tr
                            class="cursor-pointer border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                            tabindex="0"
                            onclick="window.location='{{ route('inventory.plants.show', $plant) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('inventory.plants.show', $plant) }}'; }"
                        >
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $plant->id }}</td>
                            <td class="px-3 py-4">{{ $plant->name }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $plant->code }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $plant->timezone }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('plant.status')"
                                    :value="$plant->is_active ? __('plant.active') : __('plant.inactive')"
                                    :tone="$plant->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $plant->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('plant.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $plants->links() }}</div>
    </x-ui.panel>
</div>
@endsection
