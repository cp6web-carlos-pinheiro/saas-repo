@extends('layouts.public')

@section('title', __('ui.register_admin_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell')

@section('content')
  <main class="grid min-h-screen lg:grid-cols-2">
    <section class="ui-brand-hero relative hidden overflow-hidden p-12 lg:flex">
      <div class="absolute inset-0" style="background: radial-gradient(circle at 20% 20%, color-mix(in srgb, var(--ui-primary) 35%, transparent), transparent 35%), radial-gradient(circle at 90% 10%, color-mix(in srgb, var(--ui-primary) 25%, transparent), transparent 40%);"></div>
      <div class="relative max-w-md">
        <p class="text-xs uppercase tracking-[0.2em] text-white/80">{{ __('ui.app_name') }}</p>
        <h1 class="mt-6 text-5xl font-semibold leading-tight">{{ __('ui.register_heading') }}</h1>
        <p class="mt-6 text-lg text-white/90">{{ __('ui.register_intro') }}</p>
      </div>
    </section>

    <section class="flex items-center justify-center p-6 md:p-10">
      <div class="auth-card max-w-xl p-7 md:p-10">
        <div class="mb-6 flex items-center justify-between gap-4">
          <div>
            <h2 class="auth-heading text-3xl">{{ __('ui.register_card_title') }}</h2>
            <p class="auth-muted mt-1 text-sm">{{ __('ui.register_card_subtitle') }}</p>
          </div>
          <a href="{{ route('login') }}" class="auth-link text-sm">{{ __('ui.already_have_account') }}</a>
        </div>

        @if ($errors->any())
          <x-ui.alert class="mb-5" variant="error">
            <ul class="ml-5 list-disc">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('start-trial.store') }}" class="space-y-4" id="register-form">
          @csrf

          <x-ui.field :label="__('ui.full_name')" for="name" :required="true">
            <div class="auth-input-wrap">
              <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="user" size="sm" /></span>
              <x-ui.input id="name" name="name" type="text" :value="old('name')" required class="auth-input" unstyled placeholder="{{ __('ui.your_name') }}" />
            </div>
          </x-ui.field>

          <x-ui.field :label="__('ui.corporate_email')" for="email" :required="true">
            <div class="auth-input-wrap">
              <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="mail" size="sm" /></span>
              <x-ui.input id="email" name="email" type="email" :value="old('email')" required class="auth-input" unstyled placeholder="{{ __('ui.email_placeholder') }}" />
            </div>
          </x-ui.field>

          <x-ui.field :label="__('ui.language')" for="preferred_locale" :required="true">
            <div class="auth-input-wrap">
              <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="language" size="sm" /></span>
              <x-ui.select id="preferred_locale" name="preferred_locale" class="auth-input" required>
                @php($selectedLocale = old('preferred_locale', 'pt_BR'))
                <option value="pt_BR" @selected($selectedLocale === 'pt_BR')>{{ __('ui.portuguese') }}</option>
                <option value="en" @selected($selectedLocale === 'en')>{{ __('ui.english') }}</option>
                <option value="es" @selected($selectedLocale === 'es')>{{ __('ui.spanish') }}</option>
              </x-ui.select>
            </div>
          </x-ui.field>

          <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field :label="__('ui.password')" for="password" :required="true">
              <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="lock" size="sm" /></span>
                <x-ui.input id="password" name="password" type="password" required class="auth-input" unstyled placeholder="{{ __('ui.password_placeholder') }}" />
              </div>
            </x-ui.field>

            <x-ui.field :label="__('ui.password_confirmation')" for="password_confirmation" :required="true">
              <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="circle-check" size="sm" /></span>
                <x-ui.input id="password_confirmation" name="password_confirmation" type="password" required class="auth-input" unstyled placeholder="{{ __('ui.password_confirmation_placeholder') }}" />
              </div>
            </x-ui.field>
          </div>

          <div>
            <p class="auth-muted mb-1 text-sm">{{ __('ui.password_strength') }}</p>
            <div class="h-2 overflow-hidden rounded-full" style="background: var(--ui-surface-muted);">
              <div id="password-meter" class="h-full w-0 transition-all" style="background: var(--ui-danger);"></div>
            </div>
          </div>

          <x-ui.checkbox name="terms" value="1" required>{{ __('ui.accept_terms') }}</x-ui.checkbox>

          <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
            {{ __('ui.continue_to_company') }}
            <x-ui.icon name="arrow-right" size="sm" />
          </x-ui.button>

          <div class="relative py-1">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t" style="border-color: var(--ui-border);"></div>
            </div>
            <p class="auth-muted relative mx-auto w-fit px-3 text-center text-xs" style="background: var(--ui-surface);">{{ __('ui.or_continue_with') }}</p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <x-ui.button :href="route('social.redirect', ['provider' => 'google'])" variant="secondary" size="lg" class="rounded-full">
              <x-ui.icon name="world" size="sm" />
              {{ __('ui.google') }}
            </x-ui.button>
            <x-ui.button :href="route('social.redirect', ['provider' => 'microsoft'])" variant="secondary" size="lg" class="rounded-full">
              <x-ui.icon name="device-desktop" size="sm" />
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
      if (score < 50) {
        meter.style.background = 'var(--ui-danger)';
      } else if (score < 75) {
        meter.style.background = 'var(--ui-warning)';
      } else {
        meter.style.background = 'var(--ui-success)';
      }
    });
  </script>
@endsection
