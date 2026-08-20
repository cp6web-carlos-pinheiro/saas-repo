@extends('layouts.client-area')

@section('title', __('ui.module_users').' | '.__('ui.manage_accesses'))
@section('client-page-title', __('company_access.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <x-ui.page-heading title="{{ __('company_access.title') }}">
        <x-slot:actions>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.button :href="route('company-access.users.create')" variant="primary" class="rounded-full">{{ __('company_access.create') }}</x-ui.button>
        </div>
        </x-slot:actions>
    </x-ui.page-heading>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->has('customer'))
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first('customer') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <form class="flex gap-3" method="GET">
            <label for="customer-search" class="sr-only">{{ __('company_access.search') }}</label>
            <x-ui.input id="customer-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('company_access.search') }}" />
            <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('company_access.filter') }}</x-ui.button>
        </form>

        @php($sortUrl = fn ($column) => route('company-access.users.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

        <div class="mt-6 overflow-x-auto">
            <x-ui.table :caption="__('company_access.title')">
                <thead>
                    <tr class="border-b border-[var(--ui-border)] text-left text-[var(--ui-text-muted)]">
                        <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="name" :label="__('company_access.name')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="email" :label="__('company_access.email')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="is_active" :label="__('company_access.status')" :sort="$sort" :direction="$direction" />
                        <x-ui.sortable-header column="created_at" :label="__('company_access.created_at')" :sort="$sort" :direction="$direction" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr
                            class="border-b border-[var(--ui-border)] transition hover:bg-[var(--ui-surface-muted)]"
                        >
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]"><a href="{{ route('company-access.users.show', $customer) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ui-focus)]">{{ $customer->id }}</a></td>
                            <td class="px-3 py-4 font-semibold">{{ $customer->name }}</td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->email }}</td>
                            <td class="px-3 py-4">
                                <x-ui.definition-item-status
                                    :label="__('company_access.status')"
                                    :value="$customer->is_active ? __('company_access.active') : __('company_access.inactive')"
                                    :tone="$customer->is_active ? 'success' : 'neutral'"
                                    inline
                                />
                            </td>
                            <td class="px-3 py-4 text-[var(--ui-text-muted)]">{{ $customer->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-muted)]">{{ __('company_access.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-6">{{ $customers->links() }}</div>
    </x-ui.panel>
</div>
@endsection
