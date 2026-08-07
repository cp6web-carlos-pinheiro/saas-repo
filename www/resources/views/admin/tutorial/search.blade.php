@extends('layouts.global-admin')
@section('title', __('global_tutorial.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-heading :title="__('global_tutorial.title')" :subtitle="__('global_tutorial.eyebrow')" />
    <x-ui.button :href="route('global-admin.tutorials.create')" variant="brand-primary" size="lg" class="rounded-full">
      {{ __('global_tutorial.create') }}
    </x-ui.button>
  </div>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
    <form class="flex gap-3" method="GET">
      <label for="tutorial-search" class="sr-only">{{ __('global_tutorial.search') }}</label>
      <x-ui.input id="tutorial-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('global_tutorial.search') }}" />
      <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('global_tutorial.filter') }}</x-ui.button>
    </form>

    @php($sortUrl = fn ($column) => route('global-admin.tutorials.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
            <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="route_name" :label="__('global_tutorial.route_name')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="updated_at" :label="__('global_tutorial.updated_at')" :sort="$sort" :direction="$direction" />
          </tr>
        </thead>
        <tbody>
          @forelse ($tutorials as $tutorial)
            <tr
              class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
              tabindex="0"
              onclick="window.location='{{ route('global-admin.tutorials.show', $tutorial) }}'"
              onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('global-admin.tutorials.show', $tutorial) }}'; }"
            >
              <td class="px-3 py-4 text-[#5f6368]">{{ $tutorial->id }}</td>
              <td class="px-3 py-4 font-mono text-xs md:text-sm">{{ $tutorial->route_name }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ optional($tutorial->updated_at)->format('d/m/Y H:i') ?: '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-10 text-center text-[#5f6368]">{{ __('global_tutorial.empty') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $tutorials->links() }}</div>
  </x-ui.panel>
@endsection
