@extends('layouts.google')

@section('title', __('ui.billing_page_title').' | '.__('ui.app_name'))

@section('bodyClass', 'bg-slate-100 text-slate-900')

@section('content')
  <main class="mx-auto max-w-6xl px-6 py-8">
    <x-ui.breadcrumb :items="[
      ['label' => __('ui.app_name'), 'href' => route('dashboard.industrial')],
      ['label' => __('ui.billing_page_title')],
    ]" />

    <x-ui.panel padding="p-6 md:p-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-sm text-slate-500">{{ __('ui.subscription_section_title') }}</p>
          <h1 class="font-display text-3xl font-bold">{{ __('ui.billing_page_title') }}</h1>
          <p class="mt-2 text-sm text-slate-600">{{ __('ui.billing_page_subtitle') }}</p>
        </div>
        <x-ui.button :href="route('dashboard.industrial')" variant="outline" class="rounded-full">{{ __('ui.back_to_dashboard') }}</x-ui.button>
      </div>

      @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">
          <ul class="ml-5 list-disc">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </x-ui.alert>
      @endif

      <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <aside class="rounded-3xl bg-slate-950 p-6 text-white">
          <p class="text-xs uppercase tracking-[0.2em] text-mist">{{ __('ui.current_subscription') }}</p>
          <h2 class="mt-3 text-2xl font-bold">{{ $currentPlan['label'] ?? __('ui.no_subscription') }}</h2>
          <dl class="mt-6 space-y-4 text-sm">
            <div>
              <dt class="text-slate-400">{{ __('ui.payment_method') }}</dt>
              <dd class="font-semibold text-white">{{ $currentPlan['payment_method'] ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-slate-400">{{ __('ui.billing_cycle') }}</dt>
              <dd class="font-semibold text-white">{{ $currentPlan['billing_cycle_label'] ?? '-' }}</dd>
            </div>
            <div>
              <dt class="text-slate-400">{{ __('ui.due_date') }}</dt>
              <dd class="font-semibold text-white">{{ $subscription?->ends_at?->format('d/m/Y') ?? __('ui.no_due_date') }}</dd>
            </div>
          </dl>

          @if ($usedFreeTrial)
            <p class="mt-6 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200">{{ __('ui.free_trial_used') }}</p>
          @endif
        </aside>

        <div>
          <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-display text-2xl font-bold text-slate-900">{{ __('ui.choose_next_plan') }}</h2>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            @foreach ($plans as $planCode => $plan)
              @php
                  $isCurrentPlan = $subscription?->plan_code === $planCode;
                  $disableFreeTrial = $planCode === 'free_trial' && $usedFreeTrial && ! $isCurrentPlan;
              @endphp

              <form method="POST" action="{{ route('billing.subscription.update') }}" class="flex h-full flex-col rounded-3xl border border-slate-200 bg-slate-50 p-6 {{ $isCurrentPlan ? 'ring-2 ring-slate-900' : '' }}">
                @csrf
                <input type="hidden" name="plan_code" value="{{ $planCode }}" />

                <div class="flex items-center justify-between gap-3">
                  <h3 class="text-xl font-bold text-slate-900">{{ $plan['label'] }}</h3>
                  @if ($isCurrentPlan)
                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase text-white">{{ __('ui.current_plan_badge') }}</span>
                  @endif
                </div>

                <p class="mt-3 text-sm text-slate-600">{{ $plan['description'] }}</p>

                <dl class="mt-5 space-y-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                  <div>
                    <dt class="text-slate-500">{{ __('ui.payment_method') }}</dt>
                    <dd class="font-semibold text-slate-900">{{ $plan['payment_method'] }}</dd>
                  </div>
                  <div>
                    <dt class="text-slate-500">{{ __('ui.billing_cycle') }}</dt>
                    <dd class="font-semibold text-slate-900">{{ $plan['billing_cycle_label'] }}</dd>
                  </div>
                  @if (isset($plan['trial_days']))
                    <div>
                      <dt class="text-slate-500">{{ __('ui.due_date') }}</dt>
                      <dd class="font-semibold text-slate-900">{{ __('onboarding.trial_days_activation', ['days' => $plan['trial_days']]) }}</dd>
                    </div>
                  @endif
                </dl>

                <div class="mt-auto pt-6">
                  <x-ui.button
                    type="submit"
                    @disabled($disableFreeTrial)
                    :variant="$disableFreeTrial ? 'surface-muted' : 'brand-primary'"
                    :full="true"
                    class="rounded-full"
                  >
                    {{ $isCurrentPlan ? __('ui.renew_or_change_plan') : __('ui.select_plan_action') }}
                  </x-ui.button>
                </div>

                @if ($disableFreeTrial)
                  <p class="mt-3 text-xs text-amber-700">{{ __('ui.free_trial_used') }}</p>
                @endif
              </form>
            @endforeach
          </div>
        </div>
      </div>
    </x-ui.panel>
  </main>
@endsection
