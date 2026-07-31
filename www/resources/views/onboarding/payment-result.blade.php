@extends('layouts.google')

@section('title', __('payment.result_title').' | '.__('ui.app_name'))

@section('bodyClass', 'bg-slate-100 min-h-screen p-6 text-slate-900')

@section('content')
  <main class="mx-auto max-w-2xl">
    <x-ui.panel padding="p-8 md:p-12">
      @if ($result['success'])
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-700">✓</div>
        <div class="mt-6 text-center">
          <p class="text-sm font-semibold text-emerald-700">{{ __('payment.approved_label') }}</p>
          <h1 class="mt-2 font-display text-3xl font-bold">{{ __('payment.approved_title') }}</h1>
          <p class="mt-3 text-sm text-slate-600">{{ __('payment.approved_description', ['plan' => $result['plan_label'], 'last_four' => $result['last_four']]) }}</p>
          <a href="{{ $result['continue_url'] }}" class="mt-8 inline-flex rounded-full bg-coral px-8 py-3.5 text-sm font-bold text-white">{{ __('payment.continue') }}</a>
        </div>
      @else
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-3xl text-red-700">!</div>
        <div class="mt-6 text-center">
          <p class="text-sm font-semibold text-red-700">{{ __('payment.declined_label') }}</p>
          <h1 class="mt-2 font-display text-3xl font-bold">{{ __('payment.declined_title') }}</h1>
          <p class="mt-3 text-sm text-slate-600">{{ __('payment.declined_description') }}</p>
          <p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm text-red-800">{{ $result['reason'] }}</p>
          <p class="mt-4 text-xs text-slate-500">{{ __('payment.redirecting_to_review') }}</p>
          <a href="{{ route('onboarding.payment.create', ['planCode' => $result['plan_code']]) }}" class="mt-8 inline-flex rounded-full bg-coral px-8 py-3.5 text-sm font-bold text-white">{{ __('payment.review_card') }}</a>
          <script>window.setTimeout(() => window.location.assign(@json(route('onboarding.payment.create', ['planCode' => $result['plan_code']]))), 8000);</script>
        </div>
      @endif
    </x-ui.panel>
  </main>
@endsection
