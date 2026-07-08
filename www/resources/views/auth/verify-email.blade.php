<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Confirmar email</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
  <section class="w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-soft p-7">
    <h1 class="font-display text-3xl font-bold">Confirme seu email</h1>
    <p class="text-slate-600 mt-2">Enviamos um link para ativar o trial e liberar recursos completos.</p>

    @if (session('status'))
      <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('verification.resend') }}" class="mt-6">
      @csrf
      <button type="submit" class="w-full rounded-full bg-coral text-white py-3.5 font-bold text-sm">Reenviar email de confirmacao</button>
    </form>
  </section>
</body>
</html>
