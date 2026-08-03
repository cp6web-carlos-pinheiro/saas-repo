@extends('layouts.global-admin')
@section('title', __('global_admin.modules.administrators').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-heading :title="__('global_admin.modules.administrators')" :subtitle="__('global_admin.eyebrow')" />
    <x-ui.button
      :href="route('global-admin.administrators.create')"
      variant="brand-primary"
      size="lg"
      class="rounded-full"
    >
      {{ __('global_admin.create') }}
    </x-ui.button>
  </div>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
    <form class="flex gap-3" method="GET">
      <input name="search" value="{{ $search }}" class="min-w-0 flex-1 rounded-xl border border-[#dadce0] px-4 py-3" placeholder="{{ __('global_admin.search') }}">
      <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('global_admin.filter') }}</x-ui.button>
    </form>

    @php($sortUrl = fn ($column) => route('global-admin.administrators.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
            <th class="px-3 py-3"><a href="{{ $sortUrl('name') }}">{{ __('global_admin.name') }} ↕</a></th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('email') }}">{{ __('global_admin.email') }} ↕</a></th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('is_active') }}">{{ __('global_admin.status') }} ↕</a></th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('created_at') }}">{{ __('global_admin.created_at') }} ↕</a></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($administrators as $administrator)
            <tr class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]" onclick="window.location='{{ route('global-admin.administrators.show', $administrator) }}'">
              <td class="px-3 py-4 font-semibold">{{ $administrator->name }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $administrator->email }}</td>
              <td class="px-3 py-4">
                <x-ui.definition-item-status
                  :label="__('global_admin.status')"
                  :value="$administrator->is_active ? __('global_admin.active') : __('global_admin.inactive')"
                  :tone="$administrator->is_active ? 'success' : 'neutral'"
                  inline
                />
              </td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $administrator->created_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-10 text-center text-[#5f6368]">{{ __('global_admin.empty') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $administrators->links() }}</div>
  </x-ui.panel>
@endsection
