@extends('layouts.global-admin')

@section('title', __('global_admin.home').' | '.__('ui.app_name'))

@section('admin-content')
  @php
    $quickLinks = [
        ['label' => __('global_admin.modules.customers'), 'href' => route('global-admin.customers.index'), 'icon' => 'users'],
        ['label' => __('global_admin.modules.companies'), 'href' => route('global-admin.companies.index'), 'icon' => 'building-factory'],
        ['label' => __('global_admin.modules.plans'), 'href' => route('global-admin.plans.index'), 'icon' => 'certificate'],
        ['label' => __('global_admin.modules.tutorials'), 'href' => route('global-admin.tutorials.index'), 'icon' => 'help-circle'],
        ['label' => __('global_admin.modules.administrators'), 'href' => route('global-admin.administrators.index'), 'icon' => 'shield-check'],
        ['label' => __('global_admin.modules.documentation'), 'href' => route('global-admin.docs.index'), 'icon' => 'info-circle'],
    ];
  @endphp

  <div class="text-center">
    <span class="ui-icon-button mx-auto inline-grid h-14 w-14 place-items-center rounded-2xl bg-[var(--ui-primary-soft)] text-2xl font-bold text-[var(--ui-primary)]">B</span>
    <p class="mt-6 text-sm text-[var(--ui-text-muted)]">{{ __('global_admin.title') }}</p>
    <h1 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] md:text-4xl">{{ __('global_admin.welcome_title') }}</h1>
  </div>

  <div class="mx-auto mt-10 grid max-w-4xl gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($quickLinks as $link)
      <a href="{{ $link['href'] }}" class="flex items-center gap-3 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 text-left no-underline shadow-[var(--ui-shadow-sm)] transition hover:border-[var(--ui-primary)] hover:shadow-[var(--ui-shadow-lg)]">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--ui-primary-soft)] text-[var(--ui-primary)]">
          <x-ui.icon :name="$link['icon']" />
        </span>
        <span class="font-semibold text-[var(--ui-text)]">{{ $link['label'] }}</span>
      </a>
    @endforeach
  </div>
@endsection