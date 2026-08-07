@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('admin_data_brands.title'))
@section('client-page-title', __('admin_data_brands.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('admin_data_brands.title') }}</h1>
        <x-ui.button :href="route('admin-data.brands.create')" variant="brand-primary" class="rounded-full">{{ __('admin_data_brands.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="brands-search" class="sr-only">{{ __('admin_data_brands.search') }}</label>
            <x-ui.input id="brands-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('admin_data_brands.search') }}" />
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('admin_data.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('admin-data.brands.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('admin_data.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('admin_data.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('admin_data.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('admin_data.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="is_active" :label="__('admin_data.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('admin_data.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('admin-data.brands.show', $brand) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('admin-data.brands.show', $brand) }}'; }"
                        >
                            <td class="px-3 py-4 text-[#5f6368]">{{ $brand->id }}</td>
                            <td class="px-3 py-4">{{ $brand->name }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('admin_data.status')"
                                    :value="$brand->is_active ? __('admin_data.active') : __('admin_data.inactive')"
                                    :tone="$brand->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $brand->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-10 text-center text-[#5f6368]">{{ __('admin_data_brands.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $brands->links() }}</div>
    </x-ui.panel>
</div>
@endsection
