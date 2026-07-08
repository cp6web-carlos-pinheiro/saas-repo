<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recuperar senha</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
  <section class="w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-soft p-7">
    <h1 class="font-display text-3xl font-bold">Recuperar senha</h1>
    <p class="text-slate-600 mt-2">Enviaremos um link seguro para redefinicao.</p>

    @if (session('status'))
      <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
      @csrf
      <div>
        <label class="block text-sm mb-2" for="email">Email</label>
        <input id="email" name="email" type="email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
      </div>
      <button type="submit" class="w-full rounded-full bg-coral text-white py-3.5 font-bold text-sm hover:brightness-110 transition">Enviar link de redefinicao</button>
    </form>
  </section>
</body>
</html>
