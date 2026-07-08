<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sessoes ativas</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen p-6">
  <main class="max-w-3xl mx-auto rounded-3xl bg-white border border-slate-200 shadow-soft p-6">
    <h1 class="font-display text-3xl font-bold">Sessoes ativas</h1>
    <p class="text-slate-600 mt-2">Gerencie dispositivos conectados com sua conta.</p>

    @if (session('status'))
      <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    <div class="mt-6 space-y-3">
      @forelse ($sessions as $session)
        <div class="rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-4">
          <div>
            <p class="font-semibold">{{ $session->ip_address ?? 'IP nao identificado' }}</p>
            <p class="text-sm text-slate-500">{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</p>
          </div>
          @if ($session->id !== $currentSessionId)
            <form method="POST" action="{{ route('sessions.destroy', ['id' => $session->id]) }}">
              @csrf
              @method('DELETE')
              <button class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold">Encerrar</button>
            </form>
          @else
            <span class="text-xs px-3 py-2 rounded-full bg-emerald-100 text-emerald-700">Sessao atual</span>
          @endif
        </div>
      @empty
        <p class="text-slate-500">Nenhuma sessao ativa encontrada.</p>
      @endforelse
    </div>
  </main>
</body>
</html>
