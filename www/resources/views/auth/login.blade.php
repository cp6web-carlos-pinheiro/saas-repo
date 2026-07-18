@extends('layouts.google')

@section('title', __('ui.login_title').' | '.__('ui.app_name'))

@section('head')
@endsection

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
  <section class="auth-card p-8">
    <div class="mb-6 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1a73e8]/10 text-[#1a73e8]">
        <x-heroicon-o-building-office-2 class="h-7 w-7" />
      </div>
      <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ __('ui.login_heading') }}</h1>
      <p class="mt-2 text-sm text-slate-600">{{ __('ui.login_subtitle') }}</p>
    </div>

    @if (session('status'))
      <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
      <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
      @csrf
      <div>
        <label class="auth-label" for="email">{{ __('ui.email') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-envelope class="auth-input-icon" />
          <input id="email" name="email" type="email" required class="auth-input" placeholder="{{ __('ui.email_placeholder') }}" />
        </div>
      </div>
      <div>
        <label class="auth-label" for="password">{{ __('ui.password') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-lock-closed class="auth-input-icon" />
          <input id="password" name="password" type="password" required class="auth-input" placeholder="{{ __('ui.password_type') }}" />
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#1a73e8] focus:ring-[#1a73e8]/35" />
        {{ __('ui.remember_me') }}
      </label>
      <button type="submit" class="auth-btn-primary">
        {{ __('ui.enter') }}
        <x-heroicon-o-arrow-right class="h-4 w-4" />
      </button>
    </form>

    <div class="mt-5 flex items-center justify-between text-sm">
      <a href="{{ route('password.request') }}" class="font-medium text-[#1a73e8] hover:underline">{{ __('ui.forgot_password') }}</a>
      <a href="{{ route('company-signup') }}" class="font-medium text-[#1a73e8] hover:underline">{{ __('ui.register_company') }}</a>
    </div>
  </section>
  </main>
@endsection
