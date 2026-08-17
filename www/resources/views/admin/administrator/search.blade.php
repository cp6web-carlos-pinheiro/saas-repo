@extends('layouts.global-admin')
@section('title', __('global_admin.modules.administrators').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <x-ui.page-heading :title="__('global_admin.modules.administrators')" :subtitle="__('global_admin.eyebrow')">
    <x-slot:actions>
      <x-ui.button :href="route('global-admin.administrators.create')" variant="primary" class="rounded-full">
        <x-ui.icon name="plus" size="sm" /> {{ __('global_admin.create') }}
      </x-ui.button>
    </x-slot:actions>
  </x-ui.page-heading>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <form method="GET" id="admin-administrator-search">
    <x-ui.filter-bar class="mt-6" search-name="search" :search-value="$search" :search-placeholder="__('global_admin.search')" :search-label="__('global_admin.search')">
      <x-slot:actions>
        <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('global_admin.filter') }}</x-ui.button>
      </x-slot:actions>
    </x-ui.filter-bar>
  </form>

  @php($sortUrl = fn ($column) => route('global-admin.administrators.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

  <x-ui.panel class="mt-6" padding="p-5 md:p-6">
    @if ($administrators->isEmpty())
      <x-ui.empty-state
        icon="users"
        :title="__('global_admin.empty')"
        :description="__('global_admin.eyebrow')"
      >
        <x-slot:actions>
          <x-ui.button :href="route('global-admin.administrators.create')" variant="primary" size="sm">
            <x-ui.icon name="plus" size="sm" /> {{ __('global_admin.create') }}
          </x-ui.button>
        </x-slot:actions>
      </x-ui.empty-state>
    @else
      <x-ui.table :caption="__('global_admin.modules.administrators')">
        <thead>
          <tr>
            <x-ui.sortable-header column="name" :label="__('global_admin.name')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="email" :label="__('global_admin.email')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="is_active" :label="__('global_admin.status')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="created_at" :label="__('global_admin.created_at')" :sort="$sort" :direction="$direction" />
            <x-ui.table.head align="right"><span class="sr-only">{{ __('ui.actions') }}</span></x-ui.table.head>
          </tr>
        </thead>
        <tbody>
          @foreach ($administrators as $administrator)
            <x-ui.table.row>
              <x-ui.table.cell>
                <a href="{{ route('global-admin.administrators.show', $administrator) }}" class="font-semibold text-[var(--ui-text)] hover:text-[var(--ui-primary)]">
                  {{ $administrator->name }}
                </a>
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $administrator->email }}</x-ui.table.cell>
              <x-ui.table.cell>
                <x-ui.definition-item-status
                  :label="__('global_admin.status')"
                  :value="$administrator->is_active ? __('global_admin.active') : __('global_admin.inactive')"
                  :tone="$administrator->is_active ? 'success' : 'neutral'"
                  inline
                />
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $administrator->created_at->format('d/m/Y') }}</x-ui.table.cell>
              <x-ui.table.cell align="right">
                <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $administrator->name])">
                  <x-ui.icon-button :href="route('global-admin.administrators.show', $administrator)" icon="search" :label="__('ui.view')" />
                </x-ui.row-actions>
              </x-ui.table.cell>
            </x-ui.table.row>
          @endforeach
        </tbody>
      </x-ui.table>

      <div class="mt-6">{{ $administrators->links() }}</div>
    @endif
  </x-ui.panel>
@endsection