@extends('layouts.public')

@section('title', __('ui.billing_page_title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell')

@section('content')
  <main class="w-full max-w-none px-6 py-8 xl:px-10 2xl:px-12">
    <x-ui.panel padding="p-6 md:p-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="auth-muted text-sm">{{ __('ui.subscription_section_title') }}</p>
          <h1 class="auth-heading font-display text-3xl font-bold">{{ __('ui.billing_page_title') }}</h1>
          <p class="auth-muted mt-2 text-sm">{{ __('ui.billing_page_subtitle') }}</p>
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

      <div class="mt-8">
        <div class="mb-4 flex items-center justify-between gap-3">
          <h2 class="auth-heading font-display text-2xl font-bold">{{ __('ui.choose_next_plan') }}</h2>
        </div>

        <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(260px,1fr))]">
          @foreach ($plans as $planCode => $plan)
            @php
                $isCurrentPlan = $subscription?->plan_code === $planCode;
                $hasPlanAmount = isset($plan['amount_cents']) && (int) $plan['amount_cents'] > 0;
                $disableFreeTrial = $planCode === 'free_trial' && $usedFreeTrial && ! $isCurrentPlan;
                $disableNoAmountRenewal = $isCurrentPlan && ! $hasPlanAmount;
                $disablePlanAction = $disableFreeTrial || $disableNoAmountRenewal;
            @endphp

            <form
              method="POST"
              action="{{ route('billing.subscription.update') }}"
              class="flex h-full flex-col rounded-3xl border p-6"
              style="border-color: var(--ui-border); background: var(--ui-surface-muted); {{ $isCurrentPlan ? 'box-shadow: 0 0 0 2px var(--ui-text);' : '' }}"
            >
              @csrf
              <x-ui.input type="hidden" name="plan_code" :value="$planCode" unstyled />

              <div class="flex items-center justify-between gap-3">
                <h3 class="text-xl font-bold" style="color: var(--ui-text);">{{ $plan['label'] }}</h3>
                @if ($isCurrentPlan)
                  <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase" style="background: var(--ui-text); color: var(--ui-surface);">{{ __('ui.current_plan_badge') }}</span>
                @endif
              </div>

              <p class="auth-muted mt-3 text-sm">{{ $plan['description'] }}</p>

              <dl class="mt-5 space-y-3 rounded-2xl border p-4 text-sm" style="border-color: var(--ui-border); background: var(--ui-surface); color: var(--ui-text-muted);">
                <div>
                  <dt class="auth-muted">{{ __('global_plan.amount_short') }}</dt>
                  <dd class="font-semibold" style="color: var(--ui-text);">R$ {{ number_format(($plan['amount_cents'] ?? 0) / 100, 2, ',', '.') }}</dd>
                </div>
                <div>
                  <dt class="auth-muted">{{ __('ui.payment_method') }}</dt>
                  <dd class="font-semibold" style="color: var(--ui-text);">{{ $plan['payment_method'] }}</dd>
                </div>
                <div>
                  <dt class="auth-muted">{{ __('ui.billing_cycle') }}</dt>
                  <dd class="font-semibold" style="color: var(--ui-text);">{{ $plan['billing_cycle_label'] }}</dd>
                </div>
                @if (isset($plan['trial_days']))
                  <div>
                    <dt class="auth-muted">{{ __('ui.due_date') }}</dt>
                    <dd class="font-semibold" style="color: var(--ui-text);">{{ __('onboarding.trial_days_activation', ['days' => $plan['trial_days']]) }}</dd>
                  </div>
                @endif
              </dl>

              @if ($disableNoAmountRenewal)
                <p class="mt-3 text-xs" style="color: var(--ui-warning-text);">{{ __('ui.plan_without_amount_cannot_be_renewed') }}</p>
              @else
                <div class="mt-auto pt-6">
                  <x-ui.button
                    type="submit"
                    :disabled="$disablePlanAction"
                    :variant="$disablePlanAction ? 'secondary' : 'primary'"
                    :full="true"
                    class="rounded-full"
                  >
                    {{ $isCurrentPlan ? __('ui.renew_plan') : __('ui.select_plan_action') }}
                  </x-ui.button>
                </div>
              @endif

              @if ($disableFreeTrial)
                <p class="mt-3 text-xs" style="color: var(--ui-warning-text);">{{ __('ui.free_trial_used') }}</p>
              @endif
            </form>
          @endforeach
        </div>
      </div>
    </x-ui.panel>
  </main>
@endsection