<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Beyond MRP</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
  <section class="w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-soft p-7">
    <h1 class="font-display text-3xl font-bold">Entrar na plataforma</h1>
    <p class="text-slate-600 mt-2">Acesse seu workspace e continue seu onboarding.</p>

    @if (session('status'))
      <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
      @csrf
      <div>
        <label class="block text-sm mb-2" for="email">Email</label>
        <input id="email" name="email" type="email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
      </div>
      <div>
        <label class="block text-sm mb-2" for="password">Senha</label>
        <input id="password" name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-sky-400" />
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="remember" value="1" />
        Lembrar-me
      </label>
      <button type="submit" class="w-full rounded-full bg-coral text-white py-3.5 font-bold text-sm hover:brightness-110 transition">Entrar</button>
    </form>

    <div class="mt-4 flex items-center justify-between text-sm">
      <a href="{{ route('password.request') }}" class="text-slate-600 hover:text-slate-900">Esqueci minha senha</a>
      <a href="{{ route('start-trial') }}" class="text-slate-600 hover:text-slate-900">Criar conta</a>
    </div>
  </section>
</body>
</html>
