@extends('layouts.global-admin')
@section('title', __('global_plan.title').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
  <div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-heading :title="__('global_plan.title')" :subtitle="__('global_plan.eyebrow')" />
    <x-ui.button :href="route('global-admin.plans.create')" variant="brand-primary" size="lg" class="rounded-full">
      {{ __('global_plan.create') }}
    </x-ui.button>
  </div>

  @if (session('status'))
    <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
  @endif

  <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
    <form class="flex gap-3" method="GET">
      <label for="plan-search" class="sr-only">{{ __('global_plan.search') }}</label>
      <input id="plan-search" name="search" value="{{ $search }}" class="min-w-0 flex-1 rounded-xl border border-[#dadce0] px-4 py-3" placeholder="{{ __('global_plan.search') }}">
      <x-ui.button type="submit" variant="surface-muted" class="rounded-xl">{{ __('global_plan.filter') }}</x-ui.button>
    </form>

    @php($sortUrl = fn ($column) => route('global-admin.plans.index', ['search' => $search, 'sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc']))

    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
            <th class="px-3 py-3">ID</th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('label') }}">{{ __('global_plan.label') }} ↕</a></th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('amount_cents') }}">{{ __('global_plan.amount_short') }} ↕</a></th>
            <th class="px-3 py-3">{{ __('global_plan.duration') }}</th>
            <th class="px-3 py-3">{{ __('global_plan.payment_method') }}</th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('sort_order') }}">{{ __('global_plan.sort_order') }} ↕</a></th>
            <th class="px-3 py-3">{{ __('global_plan.subscriptions_count') }}</th>
            <th class="px-3 py-3"><a href="{{ $sortUrl('is_active') }}">{{ __('global_plan.status') }} ↕</a></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($plans as $plan)
            <tr
              class="cursor-pointer border-b border-[#f1f3f4] transition hover:bg-[#f8fafd]"
              tabindex="0"
              onclick="window.location='{{ route('global-admin.plans.show', $plan) }}'"
              onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('global-admin.plans.show', $plan) }}'; }"
            >
              <td class="px-3 py-4 text-[#5f6368]">{{ $plan->id }}</td>
              <td class="px-3 py-4">{{ $plan->label }}</td>
              <td class="px-3 py-4 font-semibold text-slate-900">R$ {{ number_format($plan->amount_cents / 100, 2, ',', '.') }}</td>
              <td class="px-3 py-4 text-[#5f6368]">
                @if ($plan->trial_days)
                  {{ $plan->trial_days }} {{ $plan->trial_days === 1 ? __('global_plan.day_singular') : __('global_plan.day_plural') }}
                @elseif ($plan->interval_months)
                  {{ $plan->interval_months }} {{ $plan->interval_months === 1 ? __('global_plan.month_singular') : __('global_plan.month_plural') }}
                @else
                  —
                @endif
              </td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $plan->payment_method ?: '—' }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $plan->sort_order }}</td>
              <td class="px-3 py-4 text-[#5f6368]">{{ $plan->subscriptions_count }}</td>
              <td class="px-3 py-4">
                <span class="rounded-full px-2 py-1 text-xs {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                  {{ $plan->is_active ? __('global_plan.active') : __('global_plan.inactive') }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-3 py-10 text-center text-[#5f6368]">{{ __('global_plan.empty') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $plans->links() }}</div>
  </x-ui.panel>
@endsection
