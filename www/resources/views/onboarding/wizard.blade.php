<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ __('onboarding.title') }} | {{ __('ui.app_name') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen p-6 text-slate-900">
  <main class="max-w-6xl mx-auto">
    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 md:p-10">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-sm text-slate-500">{{ __('onboarding.account_onboarding') }}</p>
          <h1 class="font-display text-3xl font-bold">{{ __('onboarding.build_account') }}</h1>
        </div>
        <div class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
          {{ __('onboarding.step_of', ['step' => $step]) }}
        </div>
      </div>

      <div class="mt-6 grid gap-3 sm:grid-cols-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
        <div class="rounded-2xl px-4 py-3 {{ $step >= 2 ? 'bg-slate-900 text-white' : 'bg-slate-100' }}">{{ __('onboarding.user') }}</div>
        <div class="rounded-2xl px-4 py-3 {{ $step >= 2 ? 'bg-slate-900 text-white' : 'bg-slate-100' }}">{{ __('onboarding.company') }}</div>
        <div class="rounded-2xl px-4 py-3 {{ $step >= 3 ? 'bg-slate-900 text-white' : 'bg-slate-100' }}">{{ __('onboarding.plan') }}</div>
        <div class="rounded-2xl px-4 py-3 {{ $step >= 4 ? 'bg-slate-900 text-white' : 'bg-slate-100' }}">{{ __('onboarding.invites') }}</div>
      </div>

      @if (session('status'))
        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
          <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if ($step === 2)
        <div class="mt-8 grid lg:grid-cols-[1.3fr_0.7fr] gap-6">
          <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-2" for="company_name">{{ __('onboarding.company_name') }}</label>
                <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $organization?->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-2" for="company_domain">{{ __('onboarding.company_domain') }}</label>
                <input id="company_domain" name="company_domain" type="text" value="{{ old('company_domain', $organization?->domain) }}" placeholder="{{ __('onboarding.company_domain_placeholder') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3" />
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-2" for="segment">{{ __('onboarding.segment') }}</label>
                <input id="segment" name="segment" type="text" value="{{ old('segment', $profile?->segment) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-2" for="operation_size">{{ __('onboarding.operation_size') }}</label>
                <select id="operation_size" name="operation_size" class="w-full rounded-xl border border-slate-300 px-4 py-3">
                  <option value="">{{ __('onboarding.select') }}</option>
                  <option value="small" @selected(old('operation_size', $profile?->operation_size) === 'small')>{{ __('onboarding.small') }}</option>
                  <option value="mid" @selected(old('operation_size', $profile?->operation_size) === 'mid')>{{ __('onboarding.mid') }}</option>
                  <option value="large" @selected(old('operation_size', $profile?->operation_size) === 'large')>{{ __('onboarding.large') }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2" for="timezone">{{ __('onboarding.timezone') }}</label>
              <input id="timezone" name="timezone" type="text" value="{{ old('timezone', $profile?->timezone ?? 'America/Sao_Paulo') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3" />
            </div>

            <div class="pt-2">
              <button class="rounded-full bg-coral text-white px-8 py-3.5 font-bold text-sm">{{ __('onboarding.save_company_continue') }}</button>
            </div>
          </form>

          <aside class="rounded-3xl bg-slate-950 text-white p-6" aria-label="{{ __('onboarding.company_step_summary') }}">
            <p class="text-xs uppercase tracking-[0.2em] text-mist">{{ __('onboarding.step_two') }}</p>
            <h2 class="mt-3 text-2xl font-bold">{{ __('onboarding.account_base') }}</h2>
            <p class="mt-3 text-sm text-slate-300">{{ __('onboarding.account_base_description') }}</p>
          </aside>
        </div>
      @elseif ($step === 3)
        <div class="mt-8 grid gap-4 lg:grid-cols-3">
          @foreach ($plans as $planCode => $plan)
            <form method="POST" action="{{ route('onboarding.store') }}" class="rounded-3xl border border-slate-200 p-6 bg-slate-50 flex flex-col">
              @csrf
              <input type="hidden" name="plan_code" value="{{ $planCode }}" />
              <div class="flex items-center justify-between gap-3">
                <h2 class="text-2xl font-bold">{{ $plan['label'] }}</h2>
                <span class="rounded-full bg-slate-900 text-white px-3 py-1 text-xs font-semibold uppercase">{{ $planCode }}</span>
              </div>
              <p class="mt-3 text-sm text-slate-600">{{ $plan['description'] }}</p>
              <div class="mt-6 rounded-2xl bg-white border border-slate-200 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-900">{{ __('onboarding.trial_days_activation', ['days' => $plan['trial_days']]) }}</p>
                <p class="mt-1">{{ __('onboarding.plan_register_description') }}</p>
              </div>
              <div class="mt-auto pt-6">
                <button class="w-full rounded-full bg-coral text-white py-3.5 font-bold text-sm">{{ __('onboarding.select_plan', ['plan' => $plan['label']]) }}</button>
              </div>
            </form>
          @endforeach
        </div>
      @elseif ($step === 4)
        <div class="mt-8 grid lg:grid-cols-[1.1fr_0.9fr] gap-6">
          <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4">
            @csrf
            <div>
              <label class="block text-sm font-medium mb-2" for="emails">{{ __('onboarding.emails_invites') }}</label>
              <textarea id="emails" name="emails" rows="8" class="w-full rounded-2xl border border-slate-300 px-4 py-3" placeholder="{{ __('onboarding.emails_invites_placeholder') }}">{{ old('emails') }}</textarea>
            </div>
            <p class="text-sm text-slate-500">{{ __('onboarding.emails_invites_help') }}</p>
            <div class="pt-2">
              <button class="rounded-full bg-coral text-white px-8 py-3.5 font-bold text-sm">{{ __('onboarding.send_invites_finish') }}</button>
            </div>
          </form>

          <aside class="rounded-3xl bg-white border border-slate-200 p-6" aria-label="{{ __('onboarding.account_summary') }}">
            <h2 class="text-2xl font-bold">{{ __('onboarding.account_summary') }}</h2>
            <dl class="mt-5 space-y-4 text-sm">
              <div>
                <dt class="text-slate-500">{{ __('onboarding.company') }}</dt>
                <dd class="font-semibold text-slate-900">{{ $organization?->name ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-slate-500">{{ __('onboarding.plan') }}</dt>
                <dd class="font-semibold text-slate-900">{{ data_get($organization?->preferences, 'selected_plan_label', $subscription?->plan_code ?? '-') }}</dd>
              </div>
              <div>
                <dt class="text-slate-500">{{ __('onboarding.sent_invites') }}</dt>
                <dd class="font-semibold text-slate-900">{{ $invitationsSent->count() }}</dd>
              </div>
            </dl>

            @if ($invitationsSent->isNotEmpty())
              <div class="mt-6 border-t border-slate-200 pt-4">
                <p class="text-sm font-semibold text-slate-900">{{ __('onboarding.latest_invites') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                  @foreach ($invitationsSent->take(5) as $invitation)
                    <li class="flex items-center justify-between gap-3">
                      <span>{{ $invitation->email }}</span>
                      <span class="text-xs uppercase tracking-wide {{ $invitation->accepted_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $invitation->accepted_at ? __('onboarding.accepted') : __('onboarding.pending') }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif
          </aside>
        </div>
      @else
        <div class="mt-8 rounded-3xl border border-slate-200 bg-slate-50 p-6">
          <h2 class="text-2xl font-bold">{{ __('onboarding.account_ready') }}</h2>
          <p class="mt-3 text-sm text-slate-600">{{ __('onboarding.account_ready_description') }}</p>
          <div class="mt-6">
            <a href="{{ route('dashboard.industrial') }}" class="rounded-full bg-coral text-white px-8 py-3.5 font-bold text-sm inline-flex">{{ __('onboarding.go_to_dashboard') }}</a>
          </div>
        </div>
      @endif
    </div>
  </main>
</body>
</html>
