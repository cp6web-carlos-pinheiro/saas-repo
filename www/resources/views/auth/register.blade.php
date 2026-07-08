<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastro | Trial de 14 dias</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 text-slate-900">
  <main class="min-h-screen grid lg:grid-cols-2">
    <section class="hidden lg:flex relative overflow-hidden bg-night text-white p-12">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(0,179,255,.35),transparent_35%),radial-gradient(circle_at_90%_10%,rgba(25,216,157,.25),transparent_40%)]"></div>
      <div class="relative max-w-md">
        <p class="text-xs uppercase tracking-[0.2em] text-mist">Beyond MRP Enterprise</p>
        <h1 class="mt-6 font-display text-5xl leading-tight font-bold">Comece seu TESTE GRATIS POR 14 DIAS.</h1>
        <p class="mt-6 text-mist/90 text-lg">Sem cartao de credito, onboarding assistido e setup rapido para sua operacao.</p>
      </div>
    </section>

    <section class="p-6 md:p-10 flex items-center justify-center">
      <div class="w-full max-w-xl rounded-3xl bg-white border border-slate-200 shadow-soft p-7 md:p-10">
        <div class="flex items-center justify-between gap-4 mb-6">
          <h2 class="font-display text-3xl font-bold">Criar conta</h2>
          <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900">Ja tenho conta</a>
        </div>

        @if ($errors->any())
          <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
            <ul class="list-disc ml-5">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('start-trial.store') }}" class="space-y-4" id="register-form">
          @csrf
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2" for="name">Nome completo</label>
              <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-2" for="company">Empresa</label>
              <input id="company" name="company" type="text" value="{{ old('company') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" for="email">Email corporativo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2" for="password">Senha</label>
              <input id="password" name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-2" for="password_confirmation">Confirmar senha</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
            </div>
          </div>

          <div>
            <p class="text-sm text-slate-600 mb-1">Forca da senha</p>
            <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
              <div id="password-meter" class="h-full w-0 bg-rose-400 transition-all"></div>
            </div>
          </div>

          <label class="flex items-start gap-3 text-sm text-slate-600">
            <input type="checkbox" name="terms" value="1" class="mt-1" required />
            <span>Li e aceito os Termos de Uso e a Politica de Privacidade.</span>
          </label>

          <button type="submit" class="w-full rounded-full bg-coral text-white py-3.5 font-bold text-sm tracking-wide hover:brightness-110 transition">TESTE GRATIS POR 14 DIAS</button>

          <div class="relative py-1">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <p class="relative text-center text-xs text-slate-500 bg-white w-fit mx-auto px-3">ou continue com</p>
          </div>

          <div class="grid sm:grid-cols-2 gap-3">
            <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold hover:bg-slate-50">Google</a>
            <a href="{{ route('social.redirect', ['provider' => 'microsoft']) }}" class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold hover:bg-slate-50">Microsoft</a>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script>
    const passwordInput = document.getElementById('password');
    const meter = document.getElementById('password-meter');

    passwordInput?.addEventListener('input', (e) => {
      const value = e.target.value || '';
      let score = 0;
      if (value.length >= 10) score += 25;
      if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score += 25;
      if (/\d/.test(value)) score += 25;
      if (/[^A-Za-z0-9]/.test(value)) score += 25;

      meter.style.width = `${score}%`;
      meter.className = 'h-full transition-all ' + (score < 50 ? 'bg-rose-400' : score < 75 ? 'bg-amber-400' : 'bg-emerald-500');
    });
  </script>
</body>
</html>
