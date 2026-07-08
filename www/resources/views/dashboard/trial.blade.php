<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trial Dashboard</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen p-6">
  <main class="max-w-7xl mx-auto space-y-6">
    <header class="rounded-3xl bg-night text-white p-7">
      <p class="text-sm text-mist">Workspace {{ $organization->name }}</p>
      <h1 class="mt-2 font-display text-4xl font-bold">Seu trial esta ativo</h1>
      <p class="mt-3 text-mist/90">{{ $daysRemaining }} dias restantes para validar toda a plataforma.</p>
      <div class="mt-6 flex gap-3">
        <a href="#" class="rounded-full bg-coral px-6 py-3 font-bold text-sm">Fazer upgrade</a>
        <a href="{{ route('onboarding.wizard') }}" class="rounded-full border border-white/30 px-6 py-3 font-semibold text-sm">Editar onboarding</a>
      </div>
    </header>

    <section class="grid md:grid-cols-3 gap-4">
      <article class="rounded-2xl bg-white border border-slate-200 p-5">
        <p class="text-sm text-slate-500">Progresso onboarding</p>
        <p class="text-3xl font-bold mt-2">{{ $onboarding?->progress ?? 0 }}%</p>
      </article>
      <article class="rounded-2xl bg-white border border-slate-200 p-5">
        <p class="text-sm text-slate-500">Status do trial</p>
        <p class="text-3xl font-bold mt-2">{{ strtoupper($trial->status) }}</p>
      </article>
      <article class="rounded-2xl bg-white border border-slate-200 p-5">
        <p class="text-sm text-slate-500">Expira em</p>
        <p class="text-3xl font-bold mt-2">{{ $trial->trial_end_date->format('d/m/Y') }}</p>
      </article>
    </section>

    <section class="grid lg:grid-cols-2 gap-4">
      <article class="rounded-2xl bg-white border border-slate-200 p-6">
        <h2 class="font-display text-2xl font-bold">Atalhos rapidos</h2>
        <ul class="mt-4 space-y-2 text-slate-600">
          <li>Configurar usuarios e papeis</li>
          <li>Conectar integracoes API</li>
          <li>Importar catalogo inicial</li>
          <li>Habilitar monitoramento operacional</li>
        </ul>
      </article>
      <article class="rounded-2xl bg-white border border-slate-200 p-6">
        <h2 class="font-display text-2xl font-bold">Guias recomendados</h2>
        <ul class="mt-4 space-y-2 text-slate-600">
          <li>Playbook de implantacao em 30 minutos</li>
          <li>Como reduzir retrabalho com workflows</li>
          <li>Checklist de governanca e auditoria</li>
        </ul>
      </article>
    </section>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold">Encerrar sessao</button>
    </form>
  </main>
</body>
</html>
