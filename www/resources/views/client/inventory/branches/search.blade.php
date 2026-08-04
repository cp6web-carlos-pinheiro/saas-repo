@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_branches'))
@section('client-page-title', __('branch.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('branch.title') }}</h1>
        <x-ui.button :href="route('inventory.branches.create')" variant="brand-primary" class="rounded-full">{{ __('branch.create') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="branch-search" class="sr-only">{{ __('branch.search') }}</label>
            <x-ui.input id="branch-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('branch.search') }}" />
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('branch.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('inventory.branches.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('branch.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('branch.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('branch.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('name') }}">{{ __('branch.name') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('code') }}">{{ __('branch.code') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('is_active') }}">{{ __('branch.status') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('created_at') }}">{{ __('branch.created_at') }} ↕</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('inventory.branches.show', $branch) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('inventory.branches.show', $branch) }}'; }"
                        >
                            <td class="px-3 py-4 text-[#5f6368]">{{ $branch->id }}</td>
                            <td class="px-3 py-4">{{ $branch->name }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $branch->code }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('branch.status')"
                                    :value="$branch->is_active ? __('branch.active') : __('branch.inactive')"
                                    :tone="$branch->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $branch->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[#5f6368]">{{ __('branch.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $branches->links() }}</div>
    </x-ui.panel>
</div>
@endsection
