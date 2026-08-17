@extends('layouts.global-admin')
@section('title', __('global_customer.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <x-ui.page-heading :title="__('global_customer.title')" :subtitle="__('global_customer.eyebrow')">
    <x-slot:actions>
      <x-ui.button :href="route('global-admin.customers.create')" variant="primary" class="rounded-full">
        <x-ui.icon name="plus" size="sm" /> {{ __('global_customer.create') }}
      </x-ui.button>
    </x-slot:actions>
  </x-ui.page-heading>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <form method="GET">
    <x-ui.filter-bar class="mt-6" search-name="search" :search-value="$search" :search-placeholder="__('global_customer.search')" :search-label="__('global_customer.search')">
      <x-slot:actions>
        <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('global_customer.filter') }}</x-ui.button>
      </x-slot:actions>
    </x-ui.filter-bar>
  </form>

  @php($sortUrl = fn ($column) => route('global-admin.customers.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

  <x-ui.panel class="mt-6" padding="p-5 md:p-6">
    @if ($customers->isEmpty())
      <x-ui.empty-state icon="users" :title="__('global_customer.empty')">
        <x-slot:actions>
          <x-ui.button :href="route('global-admin.customers.create')" variant="primary" size="sm">
            <x-ui.icon name="plus" size="sm" /> {{ __('global_customer.create') }}
          </x-ui.button>
        </x-slot:actions>
      </x-ui.empty-state>
    @else
      <x-ui.table :caption="__('global_customer.title')">
        <thead>
          <tr>
            <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="name" :label="__('global_customer.name')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="email" :label="__('global_customer.email')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="company" :label="__('global_customer.company')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="is_active" :label="__('global_customer.status')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="created_at" :label="__('global_customer.created_at')" :sort="$sort" :direction="$direction" />
            <x-ui.table.head align="right"><span class="sr-only">{{ __('ui.actions') }}</span></x-ui.table.head>
          </tr>
        </thead>
        <tbody>
          @foreach ($customers as $customer)
            <x-ui.table.row>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $customer->id }}</x-ui.table.cell>
              <x-ui.table.cell>
                <a href="{{ route('global-admin.customers.show', $customer) }}" class="font-semibold text-[var(--ui-text)] hover:text-[var(--ui-primary)]">
                  {{ $customer->name }}
                </a>
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $customer->email }}</x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $customer->currentCompany?->name ?? __('global_customer.company_unlinked') }}</x-ui.table.cell>
              <x-ui.table.cell>
                <x-ui.definition-item-status
                  :label="__('global_customer.status')"
                  :value="$customer->is_active ? __('global_customer.active') : __('global_customer.inactive')"
                  :tone="$customer->is_active ? 'success' : 'neutral'"
                  inline
                />
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $customer->created_at->format('d/m/Y') }}</x-ui.table.cell>
              <x-ui.table.cell align="right">
                <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $customer->name])">
                  <x-ui.icon-button :href="route('global-admin.customers.show', $customer)" icon="search" :label="__('ui.view')" />
                </x-ui.row-actions>
              </x-ui.table.cell>
            </x-ui.table.row>
          @endforeach
        </tbody>
      </x-ui.table>

      <div class="mt-6">{{ $customers->links() }}</div>
    @endif
  </x-ui.panel>
@endsection