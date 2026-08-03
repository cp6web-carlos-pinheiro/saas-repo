@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.manage_accesses'))
@section('client-page-title', __('company_access.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('company_access.title') }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('company-access.users.create')" variant="brand-primary" class="rounded-full">{{ __('company_access.create') }}</x-ui.button>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->has('customer'))
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first('customer') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="customer-search" class="sr-only">{{ __('company_access.search') }}</label>
            <input id="customer-search" name="search" value="{{ $search }}" class="min-w-0 flex-1 rounded-xl border border-[#dadce0] px-4 py-3" placeholder="{{ __('company_access.search') }}">
            <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('company_access.filter') }}</x-ui.button>
        </form>

        @php($sortUrl = fn ($column) => route('company-access.users.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('name') }}">{{ __('company_access.name') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('email') }}">{{ __('company_access.email') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('is_active') }}">{{ __('company_access.status') }} ↕</a></th>
                        <th class="px-3 py-3"><a href="{{ $sortUrl('created_at') }}">{{ __('company_access.created_at') }} ↕</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr
                            class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
                            tabindex="0"
                            onclick="window.location='{{ route('company-access.users.show', $customer) }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('company-access.users.show', $customer) }}'; }"
                        >
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->id }}</td>
                            <td class="px-3 py-4 font-semibold">{{ $customer->name }}</td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->email }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('company_access.status')"
                                    :value="$customer->is_active ? __('company_access.active') : __('company_access.inactive')"
                                    :tone="$customer->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[#5f6368]">{{ $customer->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[#5f6368]">{{ __('company_access.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $customers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
