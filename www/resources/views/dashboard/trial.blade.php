<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('onboarding.trial_dashboard_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen p-6">
  <main class="max-w-7xl mx-auto space-y-6">
    <header class="rounded-3xl bg-night text-white p-7">
      <p class="text-sm text-mist">{{ __('onboarding.workspace', ['name' => $organization->name]) }}</p>
      <h1 class="mt-2 font-display text-4xl font-bold">{{ __('onboarding.trial_active') }}</h1>
      <p class="mt-3 text-mist/90">{{ __('onboarding.days_remaining', ['days' => $daysRemaining]) }}</p>
      <div class="mt-6 flex gap-3">
        <a href="#" class="rounded-full bg-coral px-6 py-3 font-bold text-sm">{{ __('onboarding.upgrade') }}</a>
        <a href="{{ route('onboarding.wizard') }}" class="rounded-full border border-white/30 px-6 py-3 font-semibold text-sm">{{ __('onboarding.edit_onboarding') }}</a>
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
      <button class="rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold">{{ __('onboarding.end_session') }}</button>
    </form>
  </main>
</body>
</html>
