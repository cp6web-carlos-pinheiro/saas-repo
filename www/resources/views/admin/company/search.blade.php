@extends('layouts.global-admin')
@section('title', __('global_company.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-heading :title="__('global_company.title')" :subtitle="__('global_company.eyebrow')" />
    <x-ui.button
      :href="route('global-admin.companies.create')"
      variant="brand-primary"
      size="lg"
      class="rounded-full"
    >
      {{ __('global_company.create') }}
    </x-ui.button>
  </div>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
    <form class="flex gap-3" method="GET">
      <label for="company-search" class="sr-only">{{ __('global_company.search') }}</label>
      <x-ui.input id="company-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('global_company.search') }}" />
      <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('global_company.filter') }}</x-ui.button>
    </form>

    @php($sortUrl = fn ($column) => route('global-admin.companies.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
            <th class="px-3 py-3">ID</th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('name') }}">{{ __('global_company.name') }} ↕</a></th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('code') }}">{{ __('global_company.code') }} ↕</a></th>
            <th class="px-3 py-3">{{ __('global_company.active_plan') }}</th>
            <th class="px-3 py-3">{{ __('global_company.users_count') }}</th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('is_active') }}">{{ __('global_company.status') }} ↕</a></th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('created_at') }}">{{ __('global_company.created_at') }} ↕</a></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($companies as $company)
            <tr
              class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
              tabindex="0"
              onclick="window.location='{{ route('global-admin.companies.show', $company) }}'"
              onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('global-admin.companies.show', $company) }}'; }"
            >
              <td class="px-3 py-4 text-[#5f6368]">{{ $company->id }}</td>
              <td class="px-3 py-4 font-semibold">{{ $company->name }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $company->code }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $company->active_plan_label ?? __('global_company.no_active_plan') }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $company->users_count }}</td>
              <td class="px-3 py-4">
                <x-ui.definition-item-status
                  :label="__('global_company.status')"
                  :value="$company->is_active ? __('global_company.active') : __('global_company.inactive')"
                  :tone="$company->is_active ? 'success' : 'neutral'"
                  inline
                />
              </td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $company->created_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-10 text-center text-[#5f6368]">{{ __('global_company.empty') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $companies->links() }}</div>
  </x-ui.panel>
@endsection
