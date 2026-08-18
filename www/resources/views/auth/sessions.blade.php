@extends('layouts.public')

@section('title', __('auth.active_sessions_title'))

@section('bodyClass', 'auth-shell p-6')

@section('content')
  <main class="mx-auto w-full max-w-3xl">
    <section class="auth-card p-6 md:p-8">
      <h1 class="auth-heading font-display text-3xl font-bold">{{ __('auth.active_sessions_heading') }}</h1>
      <p class="auth-muted mt-2">{{ __('auth.active_sessions_description') }}</p>

      @if (session('status'))
        <x-ui.alert class="mt-4" variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      <div class="mt-6 space-y-3">
        @forelse ($sessions as $session)
          <div class="flex items-center justify-between gap-4 rounded-xl border p-4" style="border-color: var(--ui-border);">
            <div>
              <p class="font-semibold" style="color: var(--ui-text);">{{ $session->ip_address ?? __('auth.ip_not_identified') }}</p>
              <p class="auth-muted text-sm">{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</p>
            </div>
            @if ($session->id !== $currentSessionId)
              <form method="POST" action="{{ route('sessions.destroy', ['id' => $session->id]) }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="outline" class="rounded-full">{{ __('auth.end_session') }}</x-ui.button>
              </form>
            @else
              <span class="ui-status ui-status-success">{{ __('auth.current_session') }}</span>
            @endif
          </div>
        @empty
          <x-ui.empty-state :title="__('auth.no_active_sessions')" />
        @endforelse
      </div>
    </section>
  </main>
@endsection