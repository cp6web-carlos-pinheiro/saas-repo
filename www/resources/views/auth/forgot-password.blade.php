@extends('layouts.google')

@section('title', __('auth.forgot_password_title'))

@section('head')
@endsection

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
  <section class="auth-card p-8">
    <div class="mb-6 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1a73e8]/10 text-[#1a73e8]">
        <x-heroicon-o-key class="h-7 w-7" />
      </div>
      <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ __('auth.forgot_password_heading') }}</h1>
      <p class="mt-2 text-sm text-slate-600">{{ __('auth.forgot_password_description') }}</p>
    </div>

    @if (session('status'))
      <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
      @csrf
      <div>
        <label class="auth-label" for="email">{{ __('ui.email') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-envelope class="auth-input-icon" />
          <input id="email" name="email" type="email" required class="auth-input" placeholder="{{ __('ui.email_placeholder') }}" />
        </div>
      </div>

      <x-ui.button type="submit" variant="brand-primary" :full="true" size="lg" class="rounded-full">
        {{ __('auth.send_reset_link') }}
        <x-heroicon-o-paper-airplane class="h-4 w-4" />
      </x-ui.button>

      <x-ui.button :href="route('login')" variant="secondary" :full="true" size="lg" class="rounded-full">{{ __('auth.back_to_login') }}</x-ui.button>
    </form>
  </section>
  </main>
@endsection
