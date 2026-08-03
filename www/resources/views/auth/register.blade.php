@extends('layouts.public')

@section('title', __('ui.register_admin_title').' | '.__('ui.app_name'))

@section('head')
@endsection

@section('bodyClass', 'auth-shell text-slate-900')

@section('content')
  <main class="min-h-screen grid lg:grid-cols-2">
    <section class="hidden lg:flex relative overflow-hidden bg-[#0f172a] text-white p-12">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(66,133,244,.35),transparent_35%),radial-gradient(circle_at_90%_10%,rgba(26,115,232,.25),transparent_40%)]"></div>
      <div class="relative max-w-md">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-200">{{ __('ui.app_name') }}</p>
        <h1 class="mt-6 text-5xl leading-tight font-semibold">{{ __('ui.register_heading') }}</h1>
        <p class="mt-6 text-slate-200/90 text-lg">{{ __('ui.register_intro') }}</p>
      </div>
    </section>

    <section class="p-6 md:p-10 flex items-center justify-center">
      <div class="auth-card max-w-xl p-7 md:p-10">
        <div class="flex items-center justify-between gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-semibold tracking-tight">{{ __('ui.register_card_title') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('ui.register_card_subtitle') }}</p>
          </div>
          <a href="{{ route('login') }}" class="text-sm font-medium text-[#1a73e8] hover:underline">{{ __('ui.already_have_account') }}</a>
        </div>

        @if ($errors->any())
          <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
            <ul class="list-disc ml-5">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('start-trial.store') }}" class="space-y-4" id="register-form">
          @csrf
          <div>
            <label class="auth-label" for="name">{{ __('ui.full_name') }}</label>
            <div class="auth-input-wrap">
              <x-heroicon-o-user class="auth-input-icon" />
              <input id="name" name="name" type="text" value="{{ old('name') }}" required class="auth-input" placeholder="{{ __('ui.your_name') }}" />
            </div>
          </div>

          <div>
            <label class="auth-label" for="email">{{ __('ui.corporate_email') }}</label>
            <div class="auth-input-wrap">
              <x-heroicon-o-envelope class="auth-input-icon" />
              <input id="email" name="email" type="email" value="{{ old('email') }}" required class="auth-input" placeholder="{{ __('ui.email_placeholder') }}" />
            </div>
          </div>

          <div>
            <label class="auth-label" for="preferred_locale">{{ __('ui.language') }}</label>
            <div class="auth-input-wrap">
              <x-heroicon-o-language class="auth-input-icon" />
              <x-ui.select id="preferred_locale" name="preferred_locale" class="auth-input" required>
                @php($selectedLocale = old('preferred_locale', 'pt_BR'))
                <option value="pt_BR" @selected($selectedLocale === 'pt_BR')>{{ __('ui.portuguese') }}</option>
                <option value="en" @selected($selectedLocale === 'en')>{{ __('ui.english') }}</option>
                <option value="es" @selected($selectedLocale === 'es')>{{ __('ui.spanish') }}</option>
              </x-ui.select>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="auth-label" for="password">{{ __('ui.password') }}</label>
              <div class="auth-input-wrap">
                <x-heroicon-o-lock-closed class="auth-input-icon" />
                <input id="password" name="password" type="password" required class="auth-input" placeholder="{{ __('ui.password_placeholder') }}" />
              </div>
            </div>
            <div>
              <label class="auth-label" for="password_confirmation">{{ __('ui.password_confirmation') }}</label>
              <div class="auth-input-wrap">
                <x-heroicon-o-check-circle class="auth-input-icon" />
                <input id="password_confirmation" name="password_confirmation" type="password" required class="auth-input" placeholder="{{ __('ui.password_confirmation_placeholder') }}" />
              </div>
            </div>
          </div>

          <div>
            <p class="text-sm text-slate-600 mb-1">{{ __('ui.password_strength') }}</p>
            <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
              <div id="password-meter" class="h-full w-0 bg-rose-400 transition-all"></div>
            </div>
          </div>

          <label class="flex items-start gap-3 text-sm text-slate-600">
            <input type="checkbox" name="terms" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" required />
            <span>{{ __('ui.accept_terms') }}</span>
          </label>

          <x-ui.button type="submit" variant="brand-primary" :full="true" size="lg" class="rounded-full">
            {{ __('ui.continue_to_company') }}
            <x-heroicon-o-arrow-right class="h-4 w-4" />
          </x-ui.button>

          <div class="relative py-1">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <p class="relative text-center text-xs text-slate-500 bg-white w-fit mx-auto px-3">{{ __('ui.or_continue_with') }}</p>
          </div>

          <div class="grid sm:grid-cols-2 gap-3">
            <x-ui.button :href="route('social.redirect', ['provider' => 'google'])" variant="secondary" size="lg" class="rounded-full">
              <x-heroicon-o-globe-alt class="h-4 w-4" />
              {{ __('ui.google') }}
            </x-ui.button>
            <x-ui.button :href="route('social.redirect', ['provider' => 'microsoft'])" variant="secondary" size="lg" class="rounded-full">
              <x-heroicon-o-window class="h-4 w-4" />
              {{ __('ui.microsoft') }}
            </x-ui.button>
          </div>
        </form>
      </div>
    </section>
  </main>

@endsection

@section('scripts')
  <script>
    const passwordInput = document.getElementById('password');
    const meter = document.getElementById('password-meter');

    passwordInput?.addEventListener('input', (e) => {
      const value = e.target.value || '';
      let score = 0;
      if (value.length >= 10) score += 25;
      if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score += 25;
      if (/\d/.test(value)) score += 25;
      if (/[^A-Za-z0-9]/.test(value)) score += 25;

      meter.style.width = `${score}%`;
      meter.className = 'h-full transition-all ' + (score < 50 ? 'bg-rose-400' : score < 75 ? 'bg-amber-400' : 'bg-emerald-500');
    });
  </script>
@endsection
