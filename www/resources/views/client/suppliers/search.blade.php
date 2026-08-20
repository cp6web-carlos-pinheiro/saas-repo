@extends('layouts.client-area')

@section('title', __('ui.module_purchasing').' | '.__('ui.purchasing_suppliers'))
@section('client-page-title', __('supplier.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('supplier.title') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('purchasing.suppliers.create')" variant="primary" class="rounded-full">{{ __('supplier.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="supplier-search" class="sr-only">{{ __('supplier.search') }}</label>
            <x-ui.input id="supplier-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('supplier.search') }}" />
            <x-ui.input type="hidden" name="person_type" :value="$personType" unstyled />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('supplier.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.suppliers.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'person_type' => $personType, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['person_type' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('supplier.all_types') }}</a>
            <a href="{{ $filterUrl(['person_type' => 'PJ']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PJ' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('supplier.person_type_pj') }}</a>
            <a href="{{ $filterUrl(['person_type' => 'PF']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PF' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('supplier.person_type_pf') }}</a>
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('supplier.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('supplier.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('supplier.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('supplier.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('supplier.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="person_type" :label="__('supplier.person_type')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="email" :label="__('supplier.email')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="phone" :label="__('supplier.phone')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('supplier.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('supplier.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr
                            class="cursor-pointer border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                            tabindex="0"
                            onclick="window.location='{{ route('purchasing.suppliers.show', $supplier) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('purchasing.suppliers.show', $supplier) }}'; }"
                        >
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $supplier->id }}</td>
                            <td class="px-3 py-4">{{ $supplier->name }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $supplier->person_type === 'PF' ? __('supplier.person_type_pf') : __('supplier.person_type_pj') }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $supplier->email ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $supplier->phone ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('supplier.status')"
                                    :value="$supplier->status === 'ACTIVE' ? __('supplier.active') : __('supplier.inactive')"
                                    :tone="$supplier->status === 'ACTIVE' ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $supplier->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('supplier.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $suppliers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
