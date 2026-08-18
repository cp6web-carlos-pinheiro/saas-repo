@extends('layouts.public')

@section('title', __('payment.title').' | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto max-w-3xl">
    <x-ui.panel padding="p-6 md:p-10">
      <section id="payment-form-panel">
        <p class="auth-muted text-sm">{{ __('payment.secure_payment') }}</p>
        <h1 class="auth-heading mt-1 font-display text-3xl font-bold">{{ __('payment.title') }}</h1>
        <p class="auth-muted mt-3 text-sm">{{ __('payment.plan_summary', ['plan' => $plan['label']]) }}</p>

        @if ($amount > 0)
          <p class="mt-2 text-lg font-bold" style="color: var(--ui-text);">{{ __('payment.amount', ['amount' => 'R$ '.number_format($amount / 100, 2, ',', '.')]) }}</p>
        @endif

        <div id="payment-errors" class="mt-5 hidden rounded-2xl border p-4 text-sm" role="alert" style="border-color: var(--ui-danger); background: var(--ui-danger-soft); color: var(--ui-danger);"></div>

        <form id="payment-form" class="mt-7 space-y-5" novalidate>
          @csrf
          <x-ui.field :label="__('payment.card_holder')" for="card_holder_name" :required="true">
            <x-ui.input id="card_holder_name" name="card_holder_name" type="text" autocomplete="cc-name" required />
          </x-ui.field>

          <x-ui.field :label="__('payment.card_number')" for="card_number" :required="true">
            <x-ui.input id="card_number" name="card_number" inputmode="numeric" autocomplete="cc-number" maxlength="23" required placeholder="0000 0000 0000 0000" />
          </x-ui.field>

          <div class="grid grid-cols-3 gap-4">
            <x-ui.field :label="__('payment.exp_month')" for="card_exp_month" :required="true">
              <x-ui.input id="card_exp_month" name="card_exp_month" type="text" inputmode="numeric" autocomplete="cc-exp-month" maxlength="2" required placeholder="MM" />
            </x-ui.field>
            <x-ui.field :label="__('payment.exp_year')" for="card_exp_year" :required="true">
              <x-ui.input id="card_exp_year" name="card_exp_year" type="text" inputmode="numeric" autocomplete="cc-exp-year" maxlength="4" required placeholder="AAAA" />
            </x-ui.field>
            <x-ui.field :label="__('payment.cvv')" for="card_cvv" :required="true">
              <x-ui.input id="card_cvv" name="card_cvv" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="4" required placeholder="***" />
            </x-ui.field>
          </div>

          <p class="auth-muted text-xs">{{ __('payment.security_notice') }}</p>
          <x-ui.button id="pay-button" type="submit" variant="primary" :full="true" size="lg" class="rounded-full">{{ __('payment.pay') }}</x-ui.button>
        </form>
      </section>

      <section id="processing-panel" class="hidden py-16 text-center" aria-live="polite">
        <div class="mx-auto h-12 w-12 animate-spin rounded-full border-4" style="border-color: var(--ui-border); border-top-color: var(--ui-primary);"></div>
        <h1 class="auth-heading mt-6 font-display text-3xl font-bold">{{ __('payment.processing_title') }}</h1>
        <p class="auth-muted mt-3 text-sm">{{ __('payment.processing_description') }}</p>
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
        const cardNumber = form.card_number.value.replace(/\D/g, '');
        let cardToken;

        if (@json($simulatePayment)) {
          cardToken = 'local_'.concat(crypto.randomUUID());
        } else {
          if (! @json($pagarMePublicKey)) throw new Error('{{ __('payment.configuration_error') }}');
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
          cardToken = tokenData.id;
        }

        const payload = new FormData(form);
        ['card_number', 'card_holder_name', 'card_exp_month', 'card_exp_year', 'card_cvv'].forEach((name) => payload.delete(name));
        payload.set('card_token', cardToken);
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