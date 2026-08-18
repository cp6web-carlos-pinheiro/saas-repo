@extends('layouts.public')

@section('title', 'Administração Global | '.__('ui.app_name'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto flex min-h-screen w-full max-w-md items-center justify-center">
    <section class="auth-card p-8">
      <div class="mb-6 text-center">
        <div class="auth-hero-icon">
          <x-ui.icon name="shield-check" size="lg" />
        </div>
        <p class="auth-muted mt-4 text-sm">Área restrita</p>
        <h1 class="auth-heading mt-1 font-display text-3xl font-bold">Administração Global</h1>
      </div>

      @if ($errors->any())
        <x-ui.alert class="mt-4" variant="error">{{ $errors->first() }}</x-ui.alert>
      @endif

      <form method="POST" class="mt-7 space-y-4">
        @csrf
        <x-ui.field label="E-mail" for="email" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="mail" size="sm" /></span>
            <x-ui.input id="email" name="email" type="email" required autofocus class="auth-input" unstyled placeholder="E-mail" />
          </div>
        </x-ui.field>

        <x-ui.field label="Senha" for="password" :required="true">
          <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true"><x-ui.icon name="lock" size="sm" /></span>
            <x-ui.input id="password" name="password" type="password" required class="auth-input" unstyled placeholder="Senha" />
          </div>
        </x-ui.field>

        <label class="auth-muted flex items-center gap-2 rounded-xl border px-4 py-3 text-sm" style="border-color: var(--ui-border); background: var(--ui-surface);">
          <x-ui.checkbox name="remember" value="1" />
          <span>Manter conectado</span>
        </label>

        <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">Entrar</x-ui.button>
      </form>
    </section>
  </main>
@endsection