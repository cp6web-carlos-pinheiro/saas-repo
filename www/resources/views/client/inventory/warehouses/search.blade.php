@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_warehouses'))
@section('client-page-title', __('warehouse.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('warehouse.title') }}</h1>
        <x-ui.button :href="route('inventory.warehouses.create')" variant="brand-primary" class="rounded-full">{{ __('warehouse.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="warehouse-search" class="sr-only">{{ __('warehouse.search') }}</label>
            <x-ui.input id="warehouse-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('warehouse.search') }}" />
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('warehouse.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('inventory.warehouses.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('warehouse.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('warehouse.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('warehouse.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
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
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('inventory.warehouses.show', $warehouse) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('inventory.warehouses.show', $warehouse) }}'; }"
                        >
                            <td class="px-3 py-4 text-[#5f6368]">{{ $warehouse->id }}</td>
                            <td class="px-3 py-4">{{ $warehouse->name }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $warehouse->code }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $warehouse->plant?->code }} - {{ $warehouse->plant?->name }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('warehouse.status')"
                                    :value="$warehouse->is_active ? __('warehouse.active') : __('warehouse.inactive')"
                                    :tone="$warehouse->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $warehouse->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-[#5f6368]">{{ __('warehouse.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $warehouses->links() }}</div>
    </x-ui.panel>
</div>
@endsection
