@extends('layouts.public')

@section('title', __('auth.accept_invitation_title').' | '.__('ui.app_name'))

@section('head')
@endsection

@section('bodyClass', 'auth-shell p-6 text-slate-900')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-xl items-center justify-center">
  <section class="auth-card p-8 md:p-10">
    <div class="mb-6 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1a73e8]/10 text-[#1a73e8]">
        <x-heroicon-o-user-plus class="h-7 w-7" />
      </div>
      <p class="mt-4 text-sm text-slate-500">{{ __('auth.access_invitation') }}</p>
      <h1 class="mt-1 text-3xl font-semibold tracking-tight text-slate-900">{{ __('auth.accept_invitation_heading') }}</h1>
      <p class="mt-3 text-sm text-slate-600">{{ __('auth.accept_invitation_description', ['email' => $invitation->email]) }}</p>
    </div>

    @if ($errors->any())
      <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
        <ul class="list-disc ml-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('account-invitations.accept', ['token' => request()->route('token')]) }}" class="mt-8 space-y-4">
      @csrf
      <div>
        <label class="auth-label" for="name">{{ __('ui.full_name') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-user class="auth-input-icon" />
          <input id="name" name="name" type="text" value="{{ old('name') }}" class="auth-input" placeholder="{{ __('ui.your_name') }}" />
        </div>
      </div>
      <div>
        <label class="auth-label" for="password">{{ __('ui.password') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-lock-closed class="auth-input-icon" />
          <input id="password" name="password" type="password" class="auth-input" placeholder="{{ __('ui.password_type') }}" />
        </div>
      </div>
      <div>
        <label class="auth-label" for="password_confirmation">{{ __('ui.password_confirmation') }}</label>
        <div class="auth-input-wrap">
          <x-heroicon-o-check-circle class="auth-input-icon" />
          <input id="password_confirmation" name="password_confirmation" type="password" class="auth-input" placeholder="{{ __('ui.password_confirmation_placeholder') }}" />
        </div>
      </div>
      <button class="auth-btn-primary" type="submit">
        {{ __('auth.accept_invitation_action') }}
        <x-heroicon-o-arrow-right class="h-4 w-4" />
      </button>

      <a href="{{ route('login') }}" class="auth-btn-secondary w-full">{{ __('auth.already_have_login') }}</a>
    </form>
  </section>
  </main>
@endsection
