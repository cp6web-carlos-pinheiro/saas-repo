@extends('layouts.public')

@section('title', __('onboarding.trial_dashboard_title'))

@section('head')
@endsection

@section('bodyClass', 'bg-slate-100 min-h-screen p-6')

@section('content')
  <main class="max-w-7xl mx-auto space-y-6">
    <x-ui.breadcrumb :items="[
      ['label' => __('ui.app_name'), 'href' => route('dashboard.industrial')],
      ['label' => __('onboarding.trial_dashboard_title')],
    ]" />

    <header class="rounded-3xl bg-night text-white p-7">
      <p class="text-sm text-mist">{{ __('onboarding.workspace', ['name' => $organization->name]) }}</p>
      <h1 class="mt-2 font-display text-4xl font-bold">{{ __('onboarding.trial_active') }}</h1>
      <p class="mt-3 text-mist/90">{{ __('onboarding.days_remaining', ['days' => $daysRemaining]) }}</p>
      <div class="mt-6 flex gap-3">
        <x-ui.button href="#" variant="brand-primary" size="lg" class="rounded-full">{{ __('onboarding.upgrade') }}</x-ui.button>
        <x-ui.button :href="route('onboarding.wizard')" variant="inverse-outline" size="lg" class="rounded-full">{{ __('onboarding.edit_onboarding') }}</x-ui.button>
      </div>
    </header>

    <section class="grid md:grid-cols-3 gap-4">
      <article class="rounded-2xl bg-white border border-slate-200 p-5">
        <p class="text-sm text-slate-500">{{ __('onboarding.onboarding_progress') }}</p>
        <p class="text-3xl font-bold mt-2">{{ $onboarding?->progress ?? 0 }}%</p>
      </article>
      <article class="rounded-2xl bg-white border border-slate-200 p-5">
        <p class="text-sm text-slate-500">{{ __('onboarding.trial_status') }}</p>
        <p class="text-3xl font-bold mt-2">{{ strtoupper($trial->status) }}</p>
      </article>
      <article class="rounded-2xl bg-white border border-slate-200 p-5">
        <p class="text-sm text-slate-500">{{ __('onboarding.expires_on') }}</p>
        <p class="text-3xl font-bold mt-2">{{ $trial->trial_end_date->format('d/m/Y') }}</p>
      </article>
    </section>

    <section class="grid lg:grid-cols-2 gap-4">
      <article class="rounded-2xl bg-white border border-slate-200 p-6">
        <h2 class="font-display text-2xl font-bold">{{ __('onboarding.quick_shortcuts') }}</h2>
        <ul class="mt-4 space-y-2 text-slate-600">
          <li>{{ __('onboarding.shortcut_users_roles') }}</li>
          <li>{{ __('onboarding.shortcut_api_integrations') }}</li>
          <li>{{ __('onboarding.shortcut_import_catalog') }}</li>
          <li>{{ __('onboarding.shortcut_monitoring') }}</li>
        </ul>
      </article>
      <article class="rounded-2xl bg-white border border-slate-200 p-6">
        <h2 class="font-display text-2xl font-bold">{{ __('onboarding.recommended_guides') }}</h2>
        <ul class="mt-4 space-y-2 text-slate-600">
          <li>{{ __('onboarding.guide_30_min') }}</li>
          <li>{{ __('onboarding.guide_reduce_rework') }}</li>
          <li>{{ __('onboarding.guide_governance') }}</li>
        </ul>
      </article>
    </section>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <x-ui.button type="submit" variant="outline" size="lg" class="rounded-full">{{ __('onboarding.end_session') }}</x-ui.button>
    </form>
  </main>
@endsection
