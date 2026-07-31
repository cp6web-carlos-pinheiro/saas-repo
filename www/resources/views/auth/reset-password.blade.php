@extends('layouts.google')

@section('title', __('auth.reset_password_title'))

@section('head')
@endsection

@section('bodyClass', 'auth-shell p-6')

@section('content')
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
      <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
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
      <x-ui.button type="submit" variant="brand-primary" :full="true" size="lg" class="rounded-full">
        {{ __('auth.update_password') }}
        <x-heroicon-o-arrow-right class="h-4 w-4" />
      </x-ui.button>
    </form>
  </section>
  </main>
@endsection
