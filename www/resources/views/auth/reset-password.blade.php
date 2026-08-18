@extends('layouts.public')

@section('title', __('auth.reset_password_title'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="shield-check" size="lg" />
        </div>
        <h1 class="auth-heading mt-4 text-3xl">{{ __('auth.reset_password_heading') }}</h1>
        <p class="auth-muted mt-2 text-sm">{{ __('auth.reset_password_description') }}</p>
      </div>

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <x-ui.input type="hidden" name="token" :value="$token" unstyled />

        <x-ui.field :label="__('ui.email')" for="email" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="mail" size="sm" /></span>
            <x-ui.input id="email" name="email" type="email" :value="$email" required class="auth-input" unstyled />
          </div>
        </x-ui.field>

        <x-ui.field :label="__('auth.new_password')" for="password" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="lock" size="sm" /></span>
            <x-ui.input id="password" name="password" type="password" required class="auth-input" unstyled placeholder="{{ __('auth.type_new_password') }}" />
          </div>
        </x-ui.field>

        <x-ui.field :label="__('ui.password_confirmation')" for="password_confirmation" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="circle-check" size="sm" /></span>
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" required class="auth-input" unstyled placeholder="{{ __('auth.repeat_new_password') }}" />
          </div>
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
          {{ __('auth.update_password') }}
          <x-ui.icon name="arrow-right" size="sm" />
        </x-ui.button>
      </form>
    </section>
  </main>
@endsection