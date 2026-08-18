@extends('layouts.public')

@section('title', __('auth.accept_invitation_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-xl items-center justify-center">
    <section class="auth-card p-8 md:p-10">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="user-plus" size="lg" />
        </div>
        <p class="auth-muted mt-4 text-sm">{{ __('auth.access_invitation') }}</p>
        <h1 class="auth-heading mt-1 text-3xl">{{ __('auth.accept_invitation_heading') }}</h1>
        <p class="auth-muted mt-3 text-sm">{{ __('auth.accept_invitation_description', ['email' => $invitation->email]) }}</p>
      </div>

      @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">
          <ul class="ml-5 list-disc">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </x-ui.alert>
      @endif

      <form method="POST" action="{{ route('account-invitations.accept', ['token' => request()->route('token')]) }}" class="mt-8 space-y-4">
        @csrf
        <x-ui.field :label="__('ui.full_name')" for="name">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="user" size="sm" /></span>
            <x-ui.input id="name" name="name" type="text" :value="old('name')" class="auth-input" unstyled placeholder="{{ __('ui.your_name') }}" />
          </div>
        </x-ui.field>

        <x-ui.field :label="__('ui.password')" for="password">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="lock" size="sm" /></span>
            <x-ui.input id="password" name="password" type="password" class="auth-input" unstyled placeholder="{{ __('ui.password_type') }}" />
          </div>
        </x-ui.field>

        <x-ui.field :label="__('ui.password_confirmation')" for="password_confirmation">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="circle-check" size="sm" /></span>
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" class="auth-input" unstyled placeholder="{{ __('ui.password_confirmation_placeholder') }}" />
          </div>
        </x-ui.field>

        <x-ui.button type="submit" variant="primary" :full="true" size="lg" class="rounded-full">
          {{ __('auth.accept_invitation_action') }}
          <x-ui.icon name="arrow-right" size="sm" />
        </x-ui.button>

        <x-ui.button :href="route('login')" variant="secondary" :full="true" size="lg" class="rounded-full">
          {{ __('auth.already_have_login') }}
        </x-ui.button>
      </form>
    </section>
  </main>
@endsection