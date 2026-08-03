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
            <x-ui.button :href="route('customers.create')" variant="brand-primary" class="rounded-full">{{ __('customer.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="customer-search" class="sr-only">{{ __('customer.search') }}</label>
            <x-ui.input id="customer-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('customer.search') }}" />
            <input type="hidden" name="person_type" value="{{ $personType }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('customer.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('customers.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'person_type' => $personType, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['person_type' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>Todos os tipos</a>
            <a href="{{ $filterUrl(['person_type' => 'PJ']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PJ' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('customer.person_type_pj') }}</a>
            <a href="{{ $filterUrl(['person_type' => 'PF']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PF' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('customer.person_type_pf') }}</a>
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>Todos os status</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('customer.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('customer.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('name') }}">{{ __('customer.name') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('person_type') }}">{{ __('customer.person_type') }} ↕</a></th>
                        <th class="px-3 py-3">{{ __('customer.tax_id') }}</th>
                        <th class="px-3 py-3">{{ __('customer.email') }}</th>
                        <th class="px-3 py-3">{{ __('customer.phone') }}</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('status') }}">{{ __('customer.status') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('created_at') }}">{{ __('customer.created_at') }} ↕</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('customers.show', $customer) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('customers.show', $customer) }}'; }"
                        >
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->id }}</td>
                            <td class="px-3 py-4">{{ $customer->name }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->person_type === 'PF' ? __('customer.person_type_pf') : __('customer.person_type_pj') }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->tax_id ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->email ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('customer.status')"
                                    :value="$customer->status === 'ACTIVE' ? __('customer.active') : __('customer.inactive')"
                                    :tone="$customer->status === 'ACTIVE' ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[#5f6368]">{{ __('customer.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $customers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
