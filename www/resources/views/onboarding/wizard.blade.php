@extends('layouts.public')

@section('title', __('onboarding.title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto max-w-6xl">
    <x-ui.panel padding="p-6 md:p-10">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="auth-muted text-sm">{{ __('onboarding.account_onboarding') }}</p>
          <h1 class="auth-heading font-display text-3xl font-bold">{{ __('onboarding.build_account') }}</h1>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
          <div class="rounded-full border px-4 py-2 text-sm auth-muted" style="border-color: var(--ui-border); background: var(--ui-surface-muted);">
            {{ __('onboarding.step_of', ['step' => $step]) }}
          </div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <input type="hidden" name="redirect_to" value="home">
            <x-ui.button type="submit" variant="ghost" size="sm" class="rounded-full">
              {{ __('onboarding.cancel_go_home') }}
            </x-ui.button>
          </form>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <input type="hidden" name="redirect_to" value="login">
            <x-ui.button type="submit" variant="outline" size="sm" class="rounded-full">
              {{ __('onboarding.exit_go_login') }}
            </x-ui.button>
          </form>
        </div>
      </div>

      <div class="mt-6 grid gap-3 text-xs font-semibold uppercase tracking-[0.18em] sm:grid-cols-4" style="color: var(--ui-text-subtle);">
        <div class="rounded-2xl px-4 py-3" style="{{ $step >= 2 ? 'background: var(--ui-text); color: var(--ui-surface);' : 'background: var(--ui-surface-muted);' }}">{{ __('onboarding.user') }}</div>
        <div class="rounded-2xl px-4 py-3" style="{{ $step >= 2 ? 'background: var(--ui-text); color: var(--ui-surface);' : 'background: var(--ui-surface-muted);' }}">{{ __('onboarding.company') }}</div>
        <div class="rounded-2xl px-4 py-3" style="{{ $step >= 3 ? 'background: var(--ui-text); color: var(--ui-surface);' : 'background: var(--ui-surface-muted);' }}">{{ __('onboarding.plan') }}</div>
        <div class="rounded-2xl px-4 py-3" style="{{ $step >= 4 ? 'background: var(--ui-text); color: var(--ui-surface);' : 'background: var(--ui-surface-muted);' }}">{{ __('onboarding.invites') }}</div>
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

      @if ($step === 2)
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
          <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
              <x-ui.field :label="__('onboarding.company_name')" for="company_name" :required="true">
                <x-ui.input id="company_name" name="company_name" type="text" :value="old('company_name', $organization?->name)" required />
              </x-ui.field>
              <x-ui.field :label="__('onboarding.company_domain')" for="company_domain">
                <x-ui.input id="company_domain" name="company_domain" type="text" :value="old('company_domain', $organization?->domain)" placeholder="{{ __('onboarding.company_domain_placeholder') }}" />
              </x-ui.field>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <x-ui.field :label="__('onboarding.segment')" for="segment">
                <x-ui.input id="segment" name="segment" type="text" :value="old('segment', $profile?->segment)" />
              </x-ui.field>
              <x-ui.field :label="__('onboarding.operation_size')" for="operation_size">
                <x-ui.select id="operation_size" name="operation_size">
                  <option value="">{{ __('onboarding.select') }}</option>
                  <option value="small" @selected(old('operation_size', $profile?->operation_size) === 'small')>{{ __('onboarding.small') }}</option>
                  <option value="mid" @selected(old('operation_size', $profile?->operation_size) === 'mid')>{{ __('onboarding.mid') }}</option>
                  <option value="large" @selected(old('operation_size', $profile?->operation_size) === 'large')>{{ __('onboarding.large') }}</option>
                </x-ui.select>
              </x-ui.field>
            </div>

            <x-ui.field :label="__('onboarding.timezone')" for="timezone" :required="true">
              @php
                $selectedTimezone = old('timezone', $profile?->timezone ?? 'UTC');
                $shouldDetectTimezone = old('timezone') === null && $profile?->timezone === null;
              @endphp
              <x-ui.select
                id="timezone"
                name="timezone"
                data-search="on"
                :data-detect-timezone="$shouldDetectTimezone ? 'true' : 'false'"
                required
              >
                @foreach (DateTimeZone::listIdentifiers() as $timezone)
                  <option value="{{ $timezone }}" @selected($selectedTimezone === $timezone)>{{ $timezone }}</option>
                @endforeach
              </x-ui.select>
            </x-ui.field>

            <div class="pt-2">
              <x-ui.button type="submit" variant="primary" size="lg" class="rounded-full">{{ __('onboarding.save_company_continue') }}</x-ui.button>
            </div>
          </form>

          <aside class="ui-brand-hero rounded-3xl p-6" aria-label="{{ __('onboarding.company_step_summary') }}">
            <p class="text-xs uppercase tracking-[0.2em] text-white/70">{{ __('onboarding.step_two') }}</p>
            <h2 class="mt-3 text-2xl font-bold">{{ __('onboarding.account_base') }}</h2>
            <p class="mt-3 text-sm text-white/80">{{ __('onboarding.account_base_description') }}</p>
          </aside>
        </div>
      @elseif ($step === 3)
        <div class="mt-8 grid gap-4 lg:grid-cols-3">
          @foreach ($plans as $planCode => $plan)
            <form method="POST" action="{{ route('onboarding.store') }}" class="flex flex-col rounded-3xl border p-6" style="border-color: var(--ui-border); background: var(--ui-surface-muted);">
              @csrf
              <x-ui.input type="hidden" name="plan_code" :value="$planCode" unstyled />
              <div class="flex items-center justify-between gap-3">
                <h2 class="text-2xl font-bold" style="color: var(--ui-text);">{{ $plan['label'] }}</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase" style="background: var(--ui-text); color: var(--ui-surface);">{{ $planCode }}</span>
              </div>
              <p class="auth-muted mt-3 text-sm">{{ $plan['description'] }}</p>
              <div class="mt-6 rounded-2xl border p-4 text-sm" style="border-color: var(--ui-border); background: var(--ui-surface); color: var(--ui-text-muted);">
                <p class="text-lg font-bold" style="color: var(--ui-text);">R$ {{ number_format(($plan['amount_cents'] ?? 0) / 100, 2, ',', '.') }}</p>
                <p class="font-semibold" style="color: var(--ui-text);">{{ $plan['billing_cycle_label'] }}</p>
                <p class="mt-1">{{ __('ui.payment_method') }}: {{ $plan['payment_method'] }}</p>
                @if (isset($plan['trial_days']))
                  <p class="auth-muted mt-3 text-xs">{{ __('onboarding.trial_days_activation', ['days' => $plan['trial_days']]) }}</p>
                @endif
              </div>
              <div class="mt-auto pt-6">
                <x-ui.button type="submit" variant="primary" size="lg" :full="true" class="rounded-full">{{ __('onboarding.select_plan', ['plan' => $plan['label']]) }}</x-ui.button>
              </div>
            </form>
          @endforeach
        </div>
      @elseif ($step === 4)
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
          <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4">
            @csrf
            <x-ui.field :label="__('onboarding.emails_invites')" for="emails">
              <x-ui.textarea id="emails" name="emails" rows="8" class="rounded-2xl" placeholder="{{ __('onboarding.emails_invites_placeholder') }}">{{ old('emails') }}</x-ui.textarea>
            </x-ui.field>
            <p class="auth-muted text-sm">{{ __('onboarding.emails_invites_help') }}</p>
            <div class="pt-2">
              <x-ui.button type="submit" variant="primary" size="lg" class="rounded-full">{{ __('onboarding.send_invites_finish') }}</x-ui.button>
            </div>
          </form>

          <aside class="rounded-3xl border p-6" style="border-color: var(--ui-border); background: var(--ui-surface);" aria-label="{{ __('onboarding.account_summary') }}">
            <h2 class="auth-heading text-2xl font-bold">{{ __('onboarding.account_summary') }}</h2>
            <dl class="mt-5 space-y-4 text-sm">
              <div>
                <dt class="auth-muted">{{ __('onboarding.company') }}</dt>
                <dd class="font-semibold" style="color: var(--ui-text);">{{ $organization?->name ?? '-' }}</dd>
              </div>
              <div>
                <dt class="auth-muted">{{ __('onboarding.plan') }}</dt>
                <dd class="font-semibold" style="color: var(--ui-text);">{{ data_get($plans, ($subscription?->plan_code ?? '').'.label', $subscription?->plan_code ?? '-') }}</dd>
              </div>
              <div>
                <dt class="auth-muted">{{ __('onboarding.sent_invites') }}</dt>
                <dd class="font-semibold" style="color: var(--ui-text);">{{ $invitationsSent->count() }}</dd>
              </div>
            </dl>

            @if ($invitationsSent->isNotEmpty())
              <div class="mt-6 border-t pt-4" style="border-color: var(--ui-border);">
                <p class="text-sm font-semibold" style="color: var(--ui-text);">{{ __('onboarding.latest_invites') }}</p>
                <ul class="mt-3 space-y-2 text-sm auth-muted">
                  @foreach ($invitationsSent->take(5) as $invitation)
                    <li class="flex items-center justify-between gap-3">
                      <span>{{ $invitation->email }}</span>
                      <span class="text-xs uppercase tracking-wide" style="color: {{ $invitation->accepted_at ? 'var(--ui-success)' : 'var(--ui-warning-text)' }};">
                        {{ $invitation->accepted_at ? __('onboarding.accepted') : __('onboarding.pending') }}
                      </span>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif
          </aside>
        </div>
      @else
        <div class="mt-8 rounded-3xl border p-6" style="border-color: var(--ui-border); background: var(--ui-surface-muted);">
          <h2 class="auth-heading text-2xl font-bold">{{ __('onboarding.account_ready') }}</h2>
          <p class="auth-muted mt-3 text-sm">{{ __('onboarding.account_ready_description') }}</p>
          <div class="mt-6">
            <x-ui.button :href="route('dashboard.industrial')" variant="primary" size="lg" class="rounded-full">{{ __('onboarding.go_to_dashboard') }}</x-ui.button>
          </div>
        </div>
      @endif
    </x-ui.panel>
  </main>
@endsection

@section('scripts')
  @if ($step === 2)
    <script>
      (() => {
        const timezoneSelect = document.getElementById('timezone');

        if (!timezoneSelect || timezoneSelect.dataset.detectTimezone !== 'true') {
          return;
        }

        const detectedTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        const hasDetectedTimezone = Array.from(timezoneSelect.options)
          .some((option) => option.value === detectedTimezone);

        if (detectedTimezone && hasDetectedTimezone) {
          timezoneSelect.value = detectedTimezone;
          timezoneSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
      })();
    </script>
  @endif
@endsection
