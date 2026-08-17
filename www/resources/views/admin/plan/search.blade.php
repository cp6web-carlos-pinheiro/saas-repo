@extends('layouts.global-admin')
@section('title', __('global_plan.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <x-ui.page-heading :title="__('global_plan.title')" :subtitle="__('global_plan.eyebrow')">
    <x-slot:actions>
      <x-ui.button :href="route('global-admin.plans.create')" variant="primary" class="rounded-full">
        <x-ui.icon name="plus" size="sm" /> {{ __('global_plan.create') }}
      </x-ui.button>
    </x-slot:actions>
  </x-ui.page-heading>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <form method="GET">
    <x-ui.filter-bar class="mt-6" search-name="search" :search-value="$search" :search-placeholder="__('global_plan.search')" :search-label="__('global_plan.search')">
      <x-slot:actions>
        <x-ui.button type="submit" variant="secondary" class="rounded-xl">{{ __('global_plan.filter') }}</x-ui.button>
      </x-slot:actions>
    </x-ui.filter-bar>
  </form>

  @php($sortUrl = fn ($column) => route('global-admin.plans.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

  <x-ui.panel class="mt-6" padding="p-5 md:p-6">
    @if ($plans->isEmpty())
      <x-ui.empty-state icon="certificate" :title="__('global_plan.empty')">
        <x-slot:actions>
          <x-ui.button :href="route('global-admin.plans.create')" variant="primary" size="sm">
            <x-ui.icon name="plus" size="sm" /> {{ __('global_plan.create') }}
          </x-ui.button>
        </x-slot:actions>
      </x-ui.empty-state>
    @else
      <x-ui.table :caption="__('global_plan.title')">
        <thead>
          <tr>
            <x-ui.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="label" :label="__('global_plan.label')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="amount_cents" :label="__('global_plan.amount_short')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="duration" :label="__('global_plan.duration')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="sort_order" :label="__('global_plan.sort_order')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="subscriptions_count" :label="__('global_plan.subscriptions_count')" :sort="$sort" :direction="$direction" />
            <x-ui.sortable-header column="is_active" :label="__('global_plan.status')" :sort="$sort" :direction="$direction" />
            <x-ui.table.head align="right"><span class="sr-only">{{ __('ui.actions') }}</span></x-ui.table.head>
          </tr>
        </thead>
        <tbody>
          @foreach ($plans as $plan)
            <x-ui.table.row>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $plan->id }}</x-ui.table.cell>
              <x-ui.table.cell>
                <a href="{{ route('global-admin.plans.show', $plan) }}" class="font-semibold text-[var(--ui-text)] hover:text-[var(--ui-primary)]">
                  {{ $plan->label }}
                </a>
              </x-ui.table.cell>
              <x-ui.table.cell class="font-semibold text-[var(--ui-text)]">R$ {{ number_format($plan->amount_cents / 100, 2, ',', '.') }}</x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">
                @if ($plan->trial_days)
                  {{ $plan->trial_days }} {{ $plan->trial_days === 1 ? __('global_plan.day_singular') : __('global_plan.day_plural') }}
                @elseif ($plan->interval_months)
                  {{ $plan->interval_months }} {{ $plan->interval_months === 1 ? __('global_plan.month_singular') : __('global_plan.month_plural') }}
                @else
                  —
                @endif
              </x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $plan->sort_order }}</x-ui.table.cell>
              <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $plan->subscriptions_count }}</x-ui.table.cell>
              <x-ui.table.cell>
                <x-ui.definition-item-status
                  :label="__('global_plan.status')"
                  :value="$plan->is_active ? __('global_plan.active') : __('global_plan.inactive')"
                  :tone="$plan->is_active ? 'success' : 'neutral'"
                  inline
                />
              </x-ui.table.cell>
              <x-ui.table.cell align="right">
                <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $plan->label])">
                  <x-ui.icon-button :href="route('global-admin.plans.show', $plan)" icon="search" :label="__('ui.view')" />
                </x-ui.row-actions>
              </x-ui.table.cell>
            </x-ui.table.row>
          @endforeach
        </tbody>
      </x-ui.table>

      <div class="mt-6">{{ $plans->links() }}</div>
    @endif
  </x-ui.panel>
@endsection