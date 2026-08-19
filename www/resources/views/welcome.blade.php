@extends('layouts.public')

@section('title', __('landing.title'))

@section('head')
  <meta name="description" content="{{ __('landing.description') }}" />
@endsection

@section('bodyClass', '')

@section('content')
  <main class="ui-brand-landing min-h-screen">
    <section class="mx-auto max-w-6xl px-6 py-6 text-white lg:px-10 lg:py-8">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-white/70">{{ __('landing.brand') }}</p>
          <h1 class="mt-2 font-display text-2xl font-bold">{{ __('landing.hero_title') }}</h1>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
          <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-2 py-1" aria-label="{{ __('ui.language') }}">
            <a href="{{ url('/?locale=pt_BR') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-lg transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50" title="{{ __('ui.portuguese') }}" aria-label="{{ __('ui.portuguese') }}">🇧🇷</a>
            <a href="{{ url('/?locale=es') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-lg transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50" title="{{ __('ui.spanish') }}" aria-label="{{ __('ui.spanish') }}">🇪🇸</a>
            <a href="{{ url('/?locale=en') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-lg transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50" title="{{ __('ui.english') }}" aria-label="{{ __('ui.english') }}">🇺🇸</a>
          </div>

          <x-ui.button :href="route('login')" variant="inverse-outline" class="rounded-full">{{ __('landing.login') }}</x-ui.button>
          <x-ui.button :href="route('company-signup')" variant="primary" class="rounded-full">{{ __('landing.register_company') }}</x-ui.button>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-16 pt-6 text-white lg:px-10 lg:pb-24">
      <div class="grid items-center gap-10 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
          <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold tracking-wide text-white/85">{{ __('landing.badge') }}</span>
          <h2 class="max-w-3xl font-display text-4xl font-bold leading-tight md:text-5xl xl:text-6xl">{{ __('landing.headline') }}</h2>
          <p class="max-w-2xl text-lg leading-relaxed text-white/85">{{ __('landing.subheadline') }}</p>
          <div class="flex flex-col gap-4 sm:flex-row">
            <x-ui.button :href="route('company-signup')" variant="primary" size="lg" class="justify-center rounded-full">{{ __('landing.start_signup') }}</x-ui.button>
            <x-ui.button :href="route('login')" variant="inverse-outline" size="lg" class="justify-center rounded-full">{{ __('landing.login_account') }}</x-ui.button>
          </div>

          <p class="inline-flex items-center gap-2 rounded-xl border border-emerald-300/35 bg-emerald-200/10 px-4 py-3 text-sm text-emerald-100">
            <x-ui.icon name="circle-check" size="sm" />
            <span>{{ __('landing.trial_notice') }}</span>
          </p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/10 p-6 shadow-soft backdrop-blur">
          <p class="text-xs uppercase tracking-[0.22em] text-white/70">{{ __('landing.flow_title') }}</p>
          <div class="mt-5 space-y-3 text-sm text-white/95">
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_1') }}</div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_2') }}</div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_3') }}</div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_4') }}</div>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
