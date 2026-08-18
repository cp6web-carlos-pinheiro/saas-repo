@extends('layouts.public')

@section('title', __('ui.login_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="building" size="lg" />
        </div>
        <h1 class="auth-heading mt-4 text-3xl">{{ __('ui.login_heading') }}</h1>
        <p class="auth-muted mt-2 text-sm">{{ __('ui.login_subtitle') }}</p>
      </div>

      @if (session('status'))
        <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-ui.field :label="__('ui.email')" for="email" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="mail" size="sm" /></span>
            <x-ui.input id="email" name="email" type="email" required class="auth-input" unstyled placeholder="{{ __('ui.email_placeholder') }}" />
          </div>
        </x-ui.field>

        <x-ui.field :label="__('ui.password')" for="password" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="lock" size="sm" /></span>
            <x-ui.input id="password" name="password" type="password" required class="auth-input" unstyled placeholder="{{ __('ui.password_type') }}" />
          </div>
        </x-ui.field>

        <x-ui.checkbox name="remember" value="1">{{ __('ui.remember_me') }}</x-ui.checkbox>

        <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
          {{ __('ui.enter') }}
          <x-ui.icon name="arrow-right" size="sm" />
        </x-ui.button>
      </form>

      <div class="mt-5 flex items-center justify-between text-sm">
        <a href="{{ route('password.request') }}" class="auth-link">{{ __('ui.forgot_password') }}</a>
        <a href="{{ route('company-signup') }}" class="auth-link">{{ __('ui.register_company') }}</a>
      </div>
    </section>
  </main>
@endsection