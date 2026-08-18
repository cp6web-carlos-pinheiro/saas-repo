@extends('layouts.public')

@section('title', __('auth.verify_email_title'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="mail-opened" size="lg" />
        </div>
        <h1 class="auth-heading mt-4 text-3xl">{{ __('auth.verify_email_heading') }}</h1>
        <p class="auth-muted mt-2 text-sm">{{ __('auth.verify_email_description') }}</p>
      </div>

      @if (session('status'))
        <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" action="{{ route('verification.resend') }}" class="mt-6 space-y-3">
        @csrf
        <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
          {{ __('auth.resend_verification_email') }}
          <x-ui.icon name="send" size="sm" />
        </x-ui.button>
        <x-ui.button :href="route('login')" variant="secondary" :full="true" size="lg" class="rounded-full">{{ __('auth.back_to_login') }}</x-ui.button>
      </form>
    </section>
  </main>
@endsection