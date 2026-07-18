<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('landing.title') }}</title>
  <meta name="description" content="{{ __('landing.description') }}" />
  <meta name="theme-color" content="#0A1224" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    body { font-family: "Manrope", sans-serif; background: #f3f7fc; color: #0f172a; }
    h1, h2, h3 { font-family: "Space Grotesk", sans-serif; letter-spacing: -0.02em; }
    .focus-ring:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(0, 179, 255, 0.25); }
  </style>
</head>
<body class="antialiased">
  <main class="min-h-screen bg-linear-to-b from-[#081021] via-[#0c1731] to-slate-100">
    <section class="mx-auto max-w-6xl px-6 py-6 lg:px-10 lg:py-8 text-white">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-slate-300">{{ __('landing.brand') }}</p>
          <h1 class="mt-2 text-2xl font-bold">{{ __('landing.hero_title') }}</h1>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-2 py-1" aria-label="{{ __('ui.language') }}">
            <a href="{{ url('/?locale=pt_BR') }}" class="focus-ring inline-flex h-8 w-8 items-center justify-center rounded-full text-lg hover:bg-white/10 transition" title="{{ __('ui.portuguese') }}" aria-label="{{ __('ui.portuguese') }}">🇧🇷</a>
            <a href="{{ url('/?locale=es') }}" class="focus-ring inline-flex h-8 w-8 items-center justify-center rounded-full text-lg hover:bg-white/10 transition" title="{{ __('ui.spanish') }}" aria-label="{{ __('ui.spanish') }}">🇪🇸</a>
            <a href="{{ url('/?locale=en') }}" class="focus-ring inline-flex h-8 w-8 items-center justify-center rounded-full text-lg hover:bg-white/10 transition" title="{{ __('ui.english') }}" aria-label="{{ __('ui.english') }}">🇺🇸</a>
          </div>

          <a href="{{ route('login') }}" class="focus-ring inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-sm font-semibold hover:bg-white/10 transition">{{ __('landing.login') }}</a>
          <a href="{{ route('company-signup') }}" class="focus-ring inline-flex items-center rounded-full bg-coral px-4 py-2 text-sm font-bold hover:brightness-110 transition shadow-soft">{{ __('landing.register_company') }}</a>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-16 pt-6 lg:px-10 lg:pb-24 text-white">
      <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] items-center">
        <div class="space-y-6">
          <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold tracking-wide text-slate-200">{{ __('landing.badge') }}</span>
          <h2 class="text-4xl md:text-5xl xl:text-6xl font-bold leading-tight max-w-3xl">{{ __('landing.headline') }}</h2>
          <p class="max-w-2xl text-lg text-slate-200/90 leading-relaxed">{{ __('landing.subheadline') }}</p>
          <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('company-signup') }}" class="focus-ring inline-flex justify-center rounded-full bg-coral px-7 py-4 text-sm font-bold tracking-wide text-white hover:brightness-110 transition shadow-glow">{{ __('landing.start_signup') }}</a>
            <a href="{{ route('login') }}" class="focus-ring inline-flex justify-center rounded-full border border-white/20 px-7 py-4 text-sm font-semibold text-white hover:bg-white/10 transition">{{ __('landing.login_account') }}</a>
          </div>

          <p class="inline-flex items-center gap-2 rounded-xl border border-emerald-300/35 bg-emerald-200/10 px-4 py-3 text-sm text-emerald-100">
            <span aria-hidden="true">✓</span>
            <span>{{ __('landing.trial_notice') }}</span>
          </p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/10 backdrop-blur p-6 shadow-soft">
          <p class="text-xs uppercase tracking-[0.22em] text-slate-300">{{ __('landing.flow_title') }}</p>
          <div class="mt-5 space-y-3 text-sm text-slate-100">
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_1') }}</div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_2') }}</div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_3') }}</div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ __('landing.flow_step_4') }}</div>
          </div>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
