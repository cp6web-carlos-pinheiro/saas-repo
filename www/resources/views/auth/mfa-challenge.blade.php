@extends('layouts.public')

@section('title', __('messages.mfa_challenge_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8" aria-labelledby="mfa-heading">
      <div class="mb-6 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1a73e8]/10 text-[#1a73e8]">
          <x-heroicon-o-shield-check class="h-7 w-7" />
        </div>
        <h1 id="mfa-heading" class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ __('messages.mfa_challenge_title') }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ __('messages.mfa_challenge_subtitle', ['email' => $email]) }}</p>
      </div>

      @if (session('status'))
        <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" action="{{ route('mfa.challenge.store') }}" class="mt-6 space-y-4" novalidate>
        @csrf
        <div>
          <label class="auth-label" for="code">{{ __('messages.mfa_code_label') }}</label>
          <x-ui.input
            id="code"
            name="code"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="8"
            required
            autocomplete="one-time-code"
            class="auth-input"
            unstyled
            placeholder="000000"
          />
        </div>

        <x-ui.button type="submit" variant="brand-primary" :full="true" size="lg" class="rounded-full">
          {{ __('messages.mfa_verify_button') }}
        </x-ui.button>
      </form>

      <form method="POST" action="{{ route('mfa.challenge.resend') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="font-medium text-[#1a73e8] hover:underline">{{ __('messages.mfa_resend_button') }}</button>
      </form>
    </section>
  </main>
@endsection
