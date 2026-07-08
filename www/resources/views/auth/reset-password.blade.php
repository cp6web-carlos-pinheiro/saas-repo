<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Redefinir senha</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
  <section class="w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-soft p-7">
    <h1 class="font-display text-3xl font-bold">Redefinir senha</h1>

    @if ($errors->any())
      <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}" />
      <div>
        <label class="block text-sm mb-2" for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ $email }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3" />
      </div>
      <div>
        <label class="block text-sm mb-2" for="password">Nova senha</label>
        <input id="password" name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3" />
      </div>
      <div>
        <label class="block text-sm mb-2" for="password_confirmation">Confirmar senha</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3" />
      </div>
      <button type="submit" class="w-full rounded-full bg-coral text-white py-3.5 font-bold text-sm">Atualizar senha</button>
    </form>
  </section>
</body>
</html>
