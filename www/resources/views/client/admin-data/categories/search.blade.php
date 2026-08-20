@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('admin_data_categories.title'))
@section('client-page-title', __('admin_data_categories.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('admin_data_categories.title') }}</h1>
        <x-ui.button :href="route('admin-data.categories.create')" variant="primary" class="rounded-full">{{ __('admin_data_categories.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="categories-search" class="sr-only">{{ __('admin_data_categories.search') }}</label>
            <x-ui.input id="categories-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('admin_data_categories.search') }}" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('admin_data.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('admin-data.categories.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('admin_data.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('admin_data.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('admin_data.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('admin_data_categories.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('admin_data.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="is_active" :label="__('admin_data.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('admin_data.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr
                            class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                        >
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('admin-data.categories.show', $category) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $category->id }}</a></td>
                            <td class="px-3 py-4">{{ $category->name }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('admin_data.status')"
                                    :value="$category->is_active ? __('admin_data.active') : __('admin_data.inactive')"
                                    :tone="$category->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $category->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('admin_data_categories.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $categories->links() }}</div>
    </x-ui.panel>
</div>
@endsection
