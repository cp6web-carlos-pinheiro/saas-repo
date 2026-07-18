<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('auth.reset_password_title') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell p-6" style="font-family: 'Roboto', sans-serif;">
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
  <section class="auth-card p-8">
    <div class="mb-6 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1a73e8]/10 text-[#1a73e8]">
        <x-heroicon-o-shield-check class="h-7 w-7" />
      </div>
      <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ __('auth.reset_password_heading') }}</h1>
      <p class="mt-2 text-sm text-slate-600">{{ __('auth.reset_password_description') }}</p>
    </div>

    @if ($errors->any())
      <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}" />
      <div>
        <label class="auth-label" for="email">{{ __('ui.email') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-envelope class="auth-input-icon" />
          <input id="email" name="email" type="email" value="{{ $email }}" required class="auth-input" />
        </div>
      </div>
      <div>
        <label class="auth-label" for="password">{{ __('auth.new_password') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-lock-closed class="auth-input-icon" />
          <input id="password" name="password" type="password" required class="auth-input" placeholder="{{ __('auth.type_new_password') }}" />
        </div>
      </div>
      <div>
        <label class="auth-label" for="password_confirmation">{{ __('ui.password_confirmation') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-check-circle class="auth-input-icon" />
          <input id="password_confirmation" name="password_confirmation" type="password" required class="auth-input" placeholder="{{ __('auth.repeat_new_password') }}" />
        </div>
      </div>
      <button type="submit" class="auth-btn-primary">
        {{ __('auth.update_password') }}
        <x-heroicon-o-arrow-right class="h-4 w-4" />
      </button>
    </form>
  </section>
  </main>
</body>
</html>
