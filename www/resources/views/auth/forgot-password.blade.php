@extends('layouts.public')

@section('title', __('auth.forgot_password_title'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="key" size="lg" />
        </div>
        <h1 class="auth-heading mt-4 text-3xl">{{ __('auth.forgot_password_heading') }}</h1>
        <p class="auth-muted mt-2 text-sm">{{ __('auth.forgot_password_description') }}</p>
      </div>

      @if (session('status'))
        <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <x-ui.field :label="__('ui.email')" for="email" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="mail" size="sm" /></span>
            <x-ui.input id="email" name="email" type="email" required class="auth-input" unstyled placeholder="{{ __('ui.email_placeholder') }}" />
          </div>
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
          {{ __('auth.send_reset_link') }}
          <x-ui.icon name="send" size="sm" />
        </x-ui.button>

        <x-ui.button :href="route('login')" variant="secondary" :full="true" size="lg" class="rounded-full">{{ __('auth.back_to_login') }}</x-ui.button>
      </form>
    </section>
  </main>
@endsection