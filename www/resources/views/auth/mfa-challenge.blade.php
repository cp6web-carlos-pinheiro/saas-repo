@extends('layouts.public')

@section('title', __('messages.mfa_challenge_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8" aria-labelledby="mfa-heading">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="shield-check" size="lg" />
        </div>
        <h1 id="mfa-heading" class="auth-heading mt-4 text-3xl">{{ __('messages.mfa_challenge_title') }}</h1>
        <p class="auth-muted mt-2 text-sm">{{ __('messages.mfa_challenge_subtitle', ['email' => $email]) }}</p>
      </div>

      @if (session('status'))
        <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" action="{{ route('mfa.challenge.store') }}" class="mt-6 space-y-4" novalidate>
        @csrf
        <x-ui.field :label="__('messages.mfa_code_label')" for="code" :required="true">
          <x-ui.input
            id="code"
            name="code"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="8"
            required
            autocomplete="one-time-code"
            placeholder="000000"
          />
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
          {{ __('messages.mfa_verify_button') }}
        </x-ui.button>
      </form>

      <form method="POST" action="{{ route('mfa.challenge.resend') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="auth-link">{{ __('messages.mfa_resend_button') }}</button>
      </form>
    </section>
  </main>
@endsection