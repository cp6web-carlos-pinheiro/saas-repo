@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouses'))
@section('client-page-title', __('warehouse.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('warehouse.title') }}</h1>
        <x-ui.button :href="route('inventory.warehouses.create')" variant="primary" class="rounded-full">{{ __('warehouse.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="warehouse-search" class="sr-only">{{ __('warehouse.search') }}</label>
            <x-ui.input id="warehouse-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('warehouse.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('warehouse.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('inventory.warehouses.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('warehouse.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('warehouse.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('warehouse.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('warehouse.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('warehouse.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="code" :label="__('warehouse.code')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="plant" :label="__('warehouse.plant')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="is_active" :label="__('warehouse.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('warehouse.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warehouses as $warehouse)
                        <tr
                            class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                        >
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('inventory.warehouses.show', $warehouse) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $warehouse->id }}</a></td>
                            <td class="px-3 py-4">{{ $warehouse->name }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $warehouse->code }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $warehouse->plant?->code }} - {{ $warehouse->plant?->name }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('warehouse.status')"
                                    :value="$warehouse->is_active ? __('warehouse.active') : __('warehouse.inactive')"
                                    :tone="$warehouse->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $warehouse->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('warehouse.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $warehouses->links() }}</div>
    </x-ui.panel>
</div>
@endsection
