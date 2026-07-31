@extends('layouts.google')

@section('title', __('payment.title').' | '.__('ui.app_name'))

@section('bodyClass', 'bg-slate-100 min-h-screen p-6 text-slate-900')

@section('content')
  <main class="mx-auto max-w-3xl">
    <x-ui.panel padding="p-6 md:p-10">
      <section id="payment-form-panel">
        <p class="text-sm text-slate-500">{{ __('payment.secure_payment') }}</p>
        <h1 class="mt-1 font-display text-3xl font-bold">{{ __('payment.title') }}</h1>
        <p class="mt-3 text-sm text-slate-600">{{ __('payment.plan_summary', ['plan' => $plan['label']]) }}</p>

        @if ($amount > 0)
          <p class="mt-2 text-lg font-bold text-slate-900">{{ __('payment.amount', ['amount' => 'R$ '.number_format($amount / 100, 2, ',', '.')]) }}</p>
        @endif

        <div id="payment-errors" class="mt-5 hidden rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"></div>

        <form id="payment-form" class="mt-7 space-y-5" novalidate>
          @csrf
          <div>
            <label class="mb-2 block text-sm font-medium" for="card_holder_name">{{ __('payment.card_holder') }}</label>
            <input id="card_holder_name" name="card_holder_name" type="text" autocomplete="cc-name" required class="w-full rounded-xl border border-slate-300 px-4 py-3" />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium" for="card_number">{{ __('payment.card_number') }}</label>
            <input id="card_number" name="card_number" inputmode="numeric" autocomplete="cc-number" maxlength="23" required class="w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="0000 0000 0000 0000" />
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="mb-2 block text-sm font-medium" for="card_exp_month">{{ __('payment.exp_month') }}</label>
              <input id="card_exp_month" name="card_exp_month" type="text" inputmode="numeric" autocomplete="cc-exp-month" maxlength="2" required class="w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="MM" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium" for="card_exp_year">{{ __('payment.exp_year') }}</label>
              <input id="card_exp_year" name="card_exp_year" type="text" inputmode="numeric" autocomplete="cc-exp-year" maxlength="4" required class="w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="AAAA" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium" for="card_cvv">{{ __('payment.cvv') }}</label>
              <input id="card_cvv" name="card_cvv" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="4" required class="w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="•••" />
            </div>
          </div>
          <p class="text-xs text-slate-500">{{ __('payment.security_notice') }}</p>
          <button id="pay-button" type="submit" class="w-full rounded-full bg-coral px-8 py-3.5 text-sm font-bold text-white">{{ __('payment.pay') }}</button>
        </form>
      </section>

      <section id="processing-panel" class="hidden py-16 text-center" aria-live="polite">
        <div class="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-coral"></div>
        <h1 class="mt-6 font-display text-3xl font-bold">{{ __('payment.processing_title') }}</h1>
        <p class="mt-3 text-sm text-slate-600">{{ __('payment.processing_description') }}</p>
      </section>
    </x-ui.panel>
  </main>

  <script>
    document.getElementById('payment-form').addEventListener('submit', async function (event) {
      event.preventDefault();
      const form = event.currentTarget;
      const errors = document.getElementById('payment-errors');
      errors.classList.add('hidden');
      document.getElementById('payment-form-panel').classList.add('hidden');
      document.getElementById('processing-panel').classList.remove('hidden');

      try {
        if (! @json($pagarMePublicKey)) throw new Error('{{ __('payment.configuration_error') }}');
        const cardNumber = form.card_number.value.replace(/\D/g, '');
        const tokenResponse = await fetch('{{ $pagarMeTokenUrl }}?appId={{ urlencode($pagarMePublicKey) }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            type: 'card',
            card: {
              number: cardNumber,
              holder_name: form.card_holder_name.value,
              exp_month: Number(form.card_exp_month.value),
              exp_year: Number(form.card_exp_year.value),
              cvv: form.card_cvv.value
            }
          })
        });
        const tokenData = await tokenResponse.json();
        if (!tokenResponse.ok || !tokenData.id) throw new Error(tokenData.message || '{{ __('payment.card_tokenization_error') }}');

        const payload = new FormData(form);
        ['card_number', 'card_holder_name', 'card_exp_month', 'card_exp_year', 'card_cvv'].forEach((name) => payload.delete(name));
        payload.set('card_token', tokenData.id);
        payload.set('last_four', cardNumber.slice(-4));
        const response = await fetch('{{ route('onboarding.payment.process') }}', {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: payload,
          credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.redirect) window.location.assign(data.redirect);
        else throw new Error(data.message || '{{ __('payment.unexpected_error') }}');
      } catch (error) {
        document.getElementById('processing-panel').classList.add('hidden');
        document.getElementById('payment-form-panel').classList.remove('hidden');
        errors.textContent = error.message || '{{ __('payment.unexpected_error') }}';
        errors.classList.remove('hidden');
      }
    });
  </script>
@endsection
