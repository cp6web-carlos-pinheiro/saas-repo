<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Onboarding inicial</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen p-6">
  <main class="max-w-5xl mx-auto">
    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 md:p-10">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-sm text-slate-500">Onboarding inicial</p>
          <h1 class="font-display text-3xl font-bold">Configure seu workspace em minutos</h1>
        </div>
        <div class="text-sm text-slate-600">
          Trial restante: <span class="font-bold">{{ $trial?->daysRemaining() ?? 14 }} dias</span>
        </div>
      </div>

      @if (session('status'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('status') }}</div>
      @endif

      @if (auth()->user()?->email_verified_at === null)
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 text-amber-700 px-4 py-3 text-sm">
          Confirme seu e-mail para manter o acesso sem interrupcoes. <a class="underline font-semibold" href="{{ route('verification.notice') }}">Reenviar confirmacao</a>
        </div>
      @endif

      <form method="POST" action="{{ route('onboarding.store') }}" class="mt-8 grid md:grid-cols-2 gap-4">
        @csrf
        <div>
          <label class="block text-sm mb-2" for="segment">Segmento</label>
          <input id="segment" name="segment" type="text" value="{{ old('segment', $onboarding?->segment) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3" />
        </div>
        <div>
          <label class="block text-sm mb-2" for="operation_size">Tamanho da operacao</label>
          <select id="operation_size" name="operation_size" class="w-full rounded-xl border border-slate-300 px-4 py-3">
            <option value="">Selecione</option>
            <option value="small" @selected(old('operation_size', $onboarding?->operation_size) === 'small')>Pequena</option>
            <option value="mid" @selected(old('operation_size', $onboarding?->operation_size) === 'mid')>Media</option>
            <option value="large" @selected(old('operation_size', $onboarding?->operation_size) === 'large')>Grande</option>
          </select>
        </div>
        <div>
          <label class="block text-sm mb-2" for="timezone">Timezone</label>
          <input id="timezone" name="timezone" type="text" value="{{ old('timezone', $onboarding?->timezone ?? 'America/Sao_Paulo') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3" />
        </div>

        <div class="md:col-span-2 grid sm:grid-cols-3 gap-3 pt-2">
          <label class="rounded-xl border border-slate-200 p-4 flex items-center gap-3 text-sm">
            <input type="checkbox" name="import_data" value="1" @checked(old('import_data', $onboarding?->import_data)) />
            Importar dados
          </label>
          <label class="rounded-xl border border-slate-200 p-4 flex items-center gap-3 text-sm">
            <input type="checkbox" name="connect_integrations" value="1" @checked(old('connect_integrations', $onboarding?->connect_integrations)) />
            Conectar integracoes
          </label>
          <label class="rounded-xl border border-slate-200 p-4 flex items-center gap-3 text-sm">
            <input type="checkbox" name="invite_team" value="1" @checked(old('invite_team', $onboarding?->invite_team)) />
            Convidar equipe
          </label>
        </div>

        <div class="md:col-span-2 pt-3 flex flex-wrap gap-3">
          <button class="rounded-full bg-coral text-white px-8 py-3.5 font-bold text-sm">Salvar onboarding</button>
          <a class="rounded-full border border-slate-300 px-8 py-3.5 font-semibold text-sm" href="{{ route('trial.dashboard') }}">Ir para dashboard</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
