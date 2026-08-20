@extends('layouts.public')

@section('title', __('global_admin.title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="shield-check" size="lg" />
        </div>
        <p class="auth-muted mt-4 text-sm">{{ __('global_admin.eyebrow') }}</p>
        <h1 class="auth-heading mt-1 font-display text-3xl font-bold">{{ __('global_admin.title') }}</h1>
      </div>

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" class="mt-7 space-y-4">
        @csrf
        <x-ui.field :label="__('global_admin.email')" for="email" :error="$errors->first('email')" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="mail" size="sm" /></span>
            <x-ui.input id="email" name="email" type="email" required autofocus class="auth-input" unstyled :placeholder="__('global_admin.email')" :aria-describedby="$errors->has('email') ? 'email-error' : null" />
          </div>
        </x-ui.field>

        <x-ui.field :label="__('global_admin.password')" for="password" :error="$errors->first('password')" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="lock" size="sm" /></span>
            <x-ui.input id="password" name="password" type="password" required class="auth-input" unstyled :placeholder="__('global_admin.password')" :aria-describedby="$errors->has('password') ? 'password-error' : null" />
          </div>
        </x-ui.field>

        <label class="auth-muted flex items-center gap-2 rounded-xl border px-4 py-3 text-sm" style="border-color: var(--ui-border); background: var(--ui-surface);">
          <x-ui.checkbox name="remember" value="1" />
          <span>{{ __('global_admin.remember') }}</span>
        </label>

        <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">{{ __('global_admin.sign_in') }}</x-ui.button>
      </form>
    </section>
  </main>
@endsection
