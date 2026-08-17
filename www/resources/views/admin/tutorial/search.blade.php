@extends('layouts.global-admin')
@section('title', __('global_tutorial.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <x-ui.page-heading :title="__('global_tutorial.title')" :subtitle="__('global_tutorial.eyebrow')">
    <x-slot:actions>
      <x-ui.button :href="route('global-admin.tutorials.create')" variant="primary" class="rounded-full">
        <x-ui.icon name="plus" size="sm" /> {{ __('global_tutorial.create') }}
      </x-ui.button>
    </x-slot:actions>
  </x-ui.page-heading>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <form method="GET">
    <x-ui.filter-bar class="mt-6" search-name="search" :search-value="$search" :search-placeholder="__('global_tutorial.search')" :search-label="__('global_tutorial.search')">
      <x-slot:actions>
        <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('global_tutorial.filter') }}</x-ui.button>
      </x-slot:actions>
    </x-ui.filter-bar>
  </form>

  @php($sortUrl = fn ($column) => route('global-admin.tutorials.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

  <x-ui.panel class="mt-6" padding="p-5 md:p-6">
    @if ($tutorials->isEmpty())
      <x-ui.empty-state icon="help-circle" :title="__('global_tutorial.empty')">
        <x-slot:actions>
          <x-ui.button :href="route('global-admin.tutorials.create')" variant="primary" size="sm">
            <x-ui.icon name="plus" size="sm" /> {{ __('global_tutorial.create') }}
          </x-ui.button>
        </x-slot:actions>
      </x-ui.empty-state>
    @else
      <x-ui.table :caption="__('global_tutorial.title')">
        <thead>
          <tr>
            <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="route_name" :label="__('global_tutorial.route_name')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="updated_at" :label="__('global_tutorial.updated_at')" :sort="$sort" :direction="$direction" />
            <x-ui.table.head align="right"><span class="sr-only">{{ __('ui.actions') }}</span></x-ui.table.head>
          </tr>
        </thead>
        <tbody>
          @foreach ($tutorials as $tutorial)
            <x-ui.table.row>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $tutorial->id }}</x-ui.table.cell>
              <x-ui.table.cell class="font-mono text-xs md:text-sm">
                <a href="{{ route('global-admin.tutorials.show', $tutorial) }}" class="text-[var(--ui-text)] hover:text-[var(--ui-primary)]">
                  {{ $tutorial->route_name }}
                </a>
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ optional($tutorial->updated_at)->format('d/m/Y H:i') ?: '—' }}</x-ui.table.cell>
              <x-ui.table.cell align="right">
                <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $tutorial->route_name])">
                  <x-ui.icon-button :href="route('global-admin.tutorials.show', $tutorial)" icon="search" :label="__('ui.view')" />
                </x-ui.row-actions>
              </x-ui.table.cell>
            </x-ui.table.row>
          @endforeach
        </tbody>
      </x-ui.table>

      <div class="mt-6">{{ $tutorials->links() }}</div>
    @endif
  </x-ui.panel>
@endsection