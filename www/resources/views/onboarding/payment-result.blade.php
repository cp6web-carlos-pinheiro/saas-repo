@extends('layouts.public')

@section('title', __('payment.result_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto max-w-2xl">
    <x-ui.panel padding="p-8 md:p-12">
      @if ($result['success'])
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full" style="background: var(--ui-success-soft); color: var(--ui-success);">
          <x-ui.icon name="circle-check" size="lg" />
        </div>
        <div class="mt-6 text-center">
          <p class="text-sm font-semibold" style="color: var(--ui-success);">{{ __('payment.approved_label') }}</p>
          <h1 class="auth-heading mt-2 font-display text-3xl font-bold">{{ __('payment.approved_title') }}</h1>
          <p class="auth-muted mt-3 text-sm">{{ __('payment.approved_description', ['plan' => $result['plan_label'], 'last_four' => $result['last_four']]) }}</p>
          <x-ui.button :href="$result['continue_url']" variant="primary" size="lg" class="mt-8 rounded-full">{{ __('payment.continue') }}</x-ui.button>
        </div>
      @else
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full" style="background: var(--ui-danger-soft); color: var(--ui-danger);">
          <x-ui.icon name="alert-triangle" size="lg" />
        </div>
        <div class="mt-6 text-center">
          <p class="text-sm font-semibold" style="color: var(--ui-danger);">{{ __('payment.declined_label') }}</p>
          <h1 class="auth-heading mt-2 font-display text-3xl font-bold">{{ __('payment.declined_title') }}</h1>
          <p class="auth-muted mt-3 text-sm">{{ __('payment.declined_description') }}</p>
          <p class="mt-4 rounded-2xl p-4 text-sm" style="background: var(--ui-danger-soft); color: var(--ui-danger);">{{ $result['reason'] }}</p>
          <p class="auth-muted mt-4 text-xs">{{ __('payment.redirecting_to_review') }}</p>
          <x-ui.button :href="route('onboarding.payment.create', ['planCode' => $result['plan_code']])" variant="primary" size="lg" class="mt-8 rounded-full">{{ __('payment.review_card') }}</x-ui.button>
          <script>window.setTimeout(() => window.location.assign(@json(route('onboarding.payment.create', ['planCode' => $result['plan_code']]))), 8000);</script>
        </div>
      @endif
    </x-ui.panel>
  </main>
@endsection