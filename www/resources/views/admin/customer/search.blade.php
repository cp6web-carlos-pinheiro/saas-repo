@extends('layouts.global-admin')
@section('title', __('global_customer.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-heading :title="__('global_customer.title')" :subtitle="__('global_customer.eyebrow')" />
    <x-ui.button
      :href="route('global-admin.customers.create')"
      variant="brand-primary"
      size="lg"
      class="rounded-full"
    >
      {{ __('global_customer.create') }}
    </x-ui.button>
  </div>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
    <form class="flex gap-3" method="GET">
      <label for="customer-search" class="sr-only">{{ __('global_customer.search') }}</label>
      <x-ui.input id="customer-search" name="search" :value="$search" class="min-w-0 flex-1" placeholder="{{ __('global_customer.search') }}" />
      <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('global_customer.filter') }}</x-ui.button>
    </form>

    @php($sortUrl = fn ($column) => route('global-admin.customers.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
            <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="name" :label="__('global_customer.name')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="email" :label="__('global_customer.email')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="company" :label="__('global_customer.company')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="is_active" :label="__('global_customer.status')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="created_at" :label="__('global_customer.created_at')" :sort="$sort" :direction="$direction" />
          </tr>
        </thead>
        <tbody>
          @forelse ($customers as $customer)
            <tr
              class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
              tabindex="0"
              onclick="window.location='{{ route('global-admin.customers.show', $customer) }}'"
              onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('global-admin.customers.show', $customer) }}'; }"
            >
              <td class="px-3 py-4 text-[#5f6368]">{{ $customer->id }}</td>
              <td class="px-3 py-4 font-semibold">{{ $customer->name }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $customer->email }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $customer->currentCompany?->name ?? __('global_customer.company_unlinked') }}</td>
              <td class="px-3 py-4">
                <x-ui.definition-item-status
                  :label="__('global_customer.status')"
                  :value="$customer->is_active ? __('global_customer.active') : __('global_customer.inactive')"
                  :tone="$customer->is_active ? 'success' : 'neutral'"
                  inline
                />
              </td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $customer->created_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-10 text-center text-[#5f6368]">{{ __('global_customer.empty') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $customers->links() }}</div>
  </x-ui.panel>
@endsection
