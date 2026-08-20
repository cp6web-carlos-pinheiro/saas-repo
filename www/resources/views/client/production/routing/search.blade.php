@extends('layouts.client-area')

@section('title', __('ui.module_production').' | '.__('ui.module_routing'))
@section('client-page-title', __('ui.module_routing'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ __('ui.module_routing') }}</h1>
        <x-ui.button :href="route('production.routing.create')" variant="primary" class="rounded-full">{{ __('production.routing.new_version') }}</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <x-ui.input name="search" :value="$search" class="min-w-0 flex-1" :placeholder="__('production.routing.search')" />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('production.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('production.routing.index', array_merge(['search' => $search, 'status' => $status], $overrides)))
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('production.all') }}</a>
            @foreach (['DRAFT', 'APPROVED', 'OBSOLETE'] as $option)
                <a href="{{ $filterUrl(['status' => $option]) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === $option ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('production.status_labels.'.$option) }}</a>
            @endforeach
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('ui.module_routing')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header class="py-2" column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="product" :label="__('production.product')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="version_number" :label="__('production.version')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="status" :label="__('production.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header class="py-2" column="operations_count" :label="__('production.routing.operations')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                        >
                            <td class="px-3 py-2"><a href="{{ route('production.routing.show', $version) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $version->id }}</a></td>
                            <td class="px-3 py-2">{{ $version->product?->sku }} - {{ $version->product?->description }}</td>
                            <td class="px-3 py-2">{{ $version->version_number }}</td>
                            <td class="px-3 py-2">{{ __('production.status_labels.'.$version->status) }}</td>
                            <td class="px-3 py-2">{{ $version->operations_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-[var(--ui-text-muted)]">{{ __('production.routing.empty_versions') }}</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $versions->links() }}</div>
    </x-ui.panel>
</div>
@endsection
