@extends('layouts.global-admin')
@section('title', __('global_company.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <x-ui.page-heading :title="__('global_company.title')" :subtitle="__('global_company.eyebrow')">
    <x-slot:actions>
      <x-ui.button :href="route('global-admin.companies.create')" variant="primary" class="rounded-full">
        <x-ui.icon name="plus" size="sm" /> {{ __('global_company.create') }}
      </x-ui.button>
    </x-slot:actions>
  </x-ui.page-heading>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <form method="GET">
    <x-ui.filter-bar class="mt-6" search-name="search" :search-value="$search" :search-placeholder="__('global_company.search')" :search-label="__('global_company.search')">
      <x-slot:actions>
        <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('global_company.filter') }}</x-ui.button>
      </x-slot:actions>
    </x-ui.filter-bar>
  </form>

  @php($sortUrl = fn ($column) => route('global-admin.companies.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

  <x-ui.panel class="mt-6" padding="p-5 md:p-6">
    @if ($companies->isEmpty())
      <x-ui.empty-state icon="building-factory" :title="__('global_company.empty')">
        <x-slot:actions>
          <x-ui.button :href="route('global-admin.companies.create')" variant="primary" size="sm">
            <x-ui.icon name="plus" size="sm" /> {{ __('global_company.create') }}
          </x-ui.button>
        </x-slot:actions>
      </x-ui.empty-state>
    @else
      <x-ui.table :caption="__('global_company.title')">
        <thead>
          <tr>
            <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="name" :label="__('global_company.name')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="code" :label="__('global_company.code')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="active_plan" :label="__('global_company.active_plan')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="users_count" :label="__('global_company.users_count')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="is_active" :label="__('global_company.status')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="created_at" :label="__('global_company.created_at')" :sort="$sort" :direction="$direction" />
            <x-ui.table.head align="right"><span class="sr-only">{{ __('ui.actions') }}</span></x-ui.table.head>
          </tr>
        </thead>
        <tbody>
          @foreach ($companies as $company)
            <x-ui.table.row>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $company->id }}</x-ui.table.cell>
              <x-ui.table.cell>
                <a href="{{ route('global-admin.companies.show', $company) }}" class="font-semibold text-[var(--ui-text)] hover:text-[var(--ui-primary)]">
                  {{ $company->name }}
                </a>
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $company->code }}</x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $company->active_plan_label ?? __('global_company.no_active_plan') }}</x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $company->users_count }}</x-ui.table.cell>
              <x-ui.table.cell>
                <x-ui.definition-item-status
                  :label="__('global_company.status')"
                  :value="$company->is_active ? __('global_company.active') : __('global_company.inactive')"
                  :tone="$company->is_active ? 'success' : 'neutral'"
                  inline
                />
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $company->created_at->format('d/m/Y') }}</x-ui.table.cell>
              <x-ui.table.cell align="right">
                <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $company->name])">
                  <x-ui.icon-button :href="route('global-admin.companies.show', $company)" icon="search" :label="__('ui.view')" />
                </x-ui.row-actions>
              </x-ui.table.cell>
            </x-ui.table.row>
          @endforeach
        </tbody>
      </x-ui.table>

      <div class="mt-6">{{ $companies->links() }}</div>
    @endif
  </x-ui.panel>
@endsection