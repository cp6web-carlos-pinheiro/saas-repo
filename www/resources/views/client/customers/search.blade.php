@extends('layouts.client-area')

@section('title', __('ui.module_customers'))
@section('client-page-title', __('customer.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('customer.title') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('customers.create')" variant="primary" class="rounded-full">{{ __('customer.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="customer-search" class="sr-only">{{ __('customer.search') }}</label>
            <x-ui.input id="customer-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('customer.search') }}" />
            <x-ui.input type="hidden" name="person_type" :value="$personType" unstyled />
            <x-ui.input type="hidden" name="status" :value="$status" unstyled />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('customer.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('customers.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'person_type' => $personType, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['person_type' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('customer.all_types') }}</a>
            <a href="{{ $filterUrl(['person_type' => 'PJ']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PJ' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('customer.person_type_pj') }}</a>
            <a href="{{ $filterUrl(['person_type' => 'PF']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PF' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('customer.person_type_pf') }}</a>
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('customer.all_statuses') }}</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('customer.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[var(--ui-primary)] bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]' : 'border-[var(--ui-border)] text-[var(--ui-text-muted)] hover:bg-[var(--ui-surface-hover)]'])>{{ __('customer.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('customer.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('customer.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="person_type" :label="__('customer.person_type')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="email" :label="__('customer.email')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="phone" :label="__('customer.phone')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="status" :label="__('customer.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('customer.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr
                            class="cursor-pointer border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                            tabindex="0"
                            onclick="window.location='{{ route('customers.show', $customer) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('customers.show', $customer) }}'; }"
                        >
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->id }}</td>
                            <td class="px-3 py-4">{{ $customer->name }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->person_type === 'PF' ? __('customer.person_type_pf') : __('customer.person_type_pj') }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->email ?? '—' }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('customer.status')"
                                    :value="$customer->status === 'ACTIVE' ? __('customer.active') : __('customer.inactive')"
                                    :tone="$customer->status === 'ACTIVE' ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('customer.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $customers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
