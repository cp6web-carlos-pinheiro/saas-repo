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
            <x-ui.button :href="route('purchasing.suppliers.create')" variant="brand-primary" class="rounded-full">{{ __('supplier.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="supplier-search" class="sr-only">{{ __('supplier.search') }}</label>
            <x-ui.input id="supplier-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('supplier.search') }}" />
            <input type="hidden" name="person_type" value="{{ $personType }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('supplier.filter') }}</x-ui.button>
        </form>

        @php($filterUrl = fn ($overrides = []) => route('purchasing.suppliers.index', array_merge(['search' => $search, 'sort' => $sort, 'direction' => $direction, 'person_type' => $personType, 'status' => $status], $overrides)))
        @php($sortUrl = fn ($column) => $filterUrl(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ $filterUrl(['person_type' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>Todos os tipos</a>
            <a href="{{ $filterUrl(['person_type' => 'PJ']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PJ' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('supplier.person_type_pj') }}</a>
            <a href="{{ $filterUrl(['person_type' => 'PF']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $personType === 'PF' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('supplier.person_type_pf') }}</a>
            <a href="{{ $filterUrl(['status' => '']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === '' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>Todos os status</a>
            <a href="{{ $filterUrl(['status' => 'ACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'ACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('supplier.active') }}</a>
            <a href="{{ $filterUrl(['status' => 'INACTIVE']) }}" @class(['rounded-full border px-3 py-1.5 no-underline transition', $status === 'INACTIVE' ? 'border-[#1a73e8] bg-[#e8f0fe] text-[#174ea6]' : 'border-[#dadce0] text-[#5f6368] hover:bg-[#f1f3f4]'])>{{ __('supplier.inactive') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('name') }}">{{ __('supplier.name') }} ↕</a></th>
                        <th class="px-3 py-3">{{ __('supplier.person_type') }}</th>
                        <th class="px-3 py-3">{{ __('supplier.tax_id') }}</th>
                        <th class="px-3 py-3">{{ __('supplier.email') }}</th>
                        <th class="px-3 py-3">{{ __('supplier.phone') }}</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('status') }}">{{ __('supplier.status') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('created_at') }}">{{ __('supplier.created_at') }} ↕</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('purchasing.suppliers.show', $supplier) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('purchasing.suppliers.show', $supplier) }}'; }"
                        >
                            <td class="px-3 py-4 text-[#5f6368]">{{ $supplier->id }}</td>
                            <td class="px-3 py-4">{{ $supplier->name }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $supplier->person_type === 'PF' ? __('supplier.person_type_pf') : __('supplier.person_type_pj') }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $supplier->tax_id ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $supplier->email ?? '—' }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $supplier->phone ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('supplier.status')"
                                    :value="$supplier->status === 'ACTIVE' ? __('supplier.active') : __('supplier.inactive')"
                                    :tone="$supplier->status === 'ACTIVE' ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $supplier->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-[#5f6368]">{{ __('supplier.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $suppliers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
