@extends('layouts.client-area')

@section('title', ($dashboard['title'] ?? __('ui.dashboard')).' | '.__('ui.app_name'))
@section('client-page-title', $dashboard['title'] ?? __('ui.dashboard'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">{{ __('ui.dashboard') }}</p>
            <h1 class="font-display text-3xl font-bold">{{ $dashboard['title'] ?? __('ui.dashboard') }}</h1>
            <p class="mt-2 max-w-3xl text-sm text-[var(--ui-text-muted)]">{{ $dashboard['description'] ?? '' }}</p>
        </div>
        <x-ui.button :href="route('dashboard.industrial')" variant="secondary" class="rounded-full">{{ __('ui.back_to_dashboard') }}</x-ui.button>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-lg font-semibold">{{ __('domain_dashboard.pending') }}</h2>
                <span class="rounded-full border border-[var(--ui-warning)] bg-[var(--ui-warning-soft)] px-3 py-1 text-sm font-semibold text-[var(--ui-warning-text)]">{{ $dashboard['pending_total'] ?? 0 }}</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse (($dashboard['pending_items'] ?? []) as $item)
                    <div @class([
                        'flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5',
                        'border-[var(--ui-border)]' => ($item['severity'] ?? 'normal') === 'normal',
                        'border-[var(--ui-warning)] bg-[var(--ui-warning-soft)]' => ($item['severity'] ?? 'normal') === 'attention',
                        'border-[var(--ui-danger)] bg-[var(--ui-danger-soft)]' => ($item['severity'] ?? 'normal') === 'critical',
                    ])>
                        <span class="text-sm text-[var(--ui-text-muted)]">{{ $item['label'] }}</span>
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide',
                                'border-[var(--ui-border)] bg-[var(--ui-surface)] text-[var(--ui-text-muted)]' => ($item['severity'] ?? 'normal') === 'normal',
                                'border-[var(--ui-warning)] bg-[var(--ui-warning-soft)] text-[var(--ui-warning-text)]' => ($item['severity'] ?? 'normal') === 'attention',
                                'border-[var(--ui-danger)] bg-[var(--ui-danger-soft)] text-[var(--ui-danger)]' => ($item['severity'] ?? 'normal') === 'critical',
                            ])>{{ $item['severity_label'] ?? __('domain_dashboard.severity.normal') }}</span>
                            <span class="rounded-lg bg-[var(--ui-surface-hover)] px-2 py-1 text-xs font-semibold text-[var(--ui-text)]">{{ $item['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[var(--ui-text-muted)]">{{ __('domain_dashboard.no_pending') }}</p>
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-lg font-semibold">{{ __('domain_dashboard.in_progress') }}</h2>
                <span class="rounded-full border border-[var(--ui-success)] bg-[var(--ui-success-soft)] px-3 py-1 text-sm font-semibold text-[var(--ui-success)]">{{ $dashboard['in_progress_total'] ?? 0 }}</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse (($dashboard['in_progress_items'] ?? []) as $item)
                    <div @class([
                        'flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5',
                        'border-[var(--ui-border)]' => ($item['severity'] ?? 'normal') === 'normal',
                        'border-[var(--ui-warning)] bg-[var(--ui-warning-soft)]' => ($item['severity'] ?? 'normal') === 'attention',
                        'border-[var(--ui-danger)] bg-[var(--ui-danger-soft)]' => ($item['severity'] ?? 'normal') === 'critical',
                    ])>
                        <span class="text-sm text-[var(--ui-text-muted)]">{{ $item['label'] }}</span>
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide',
                                'border-[var(--ui-border)] bg-[var(--ui-surface)] text-[var(--ui-text-muted)]' => ($item['severity'] ?? 'normal') === 'normal',
                                'border-[var(--ui-warning)] bg-[var(--ui-warning-soft)] text-[var(--ui-warning-text)]' => ($item['severity'] ?? 'normal') === 'attention',
                                'border-[var(--ui-danger)] bg-[var(--ui-danger-soft)] text-[var(--ui-danger)]' => ($item['severity'] ?? 'normal') === 'critical',
                            ])>{{ $item['severity_label'] ?? __('domain_dashboard.severity.normal') }}</span>
                            <span class="rounded-lg bg-[var(--ui-surface-hover)] px-2 py-1 text-xs font-semibold text-[var(--ui-text)]">{{ $item['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[var(--ui-text-muted)]">{{ __('domain_dashboard.no_in_progress') }}</p>
                @endforelse
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-5 md:p-6">
        <h2 class="text-lg font-semibold">{{ __('domain_dashboard.shortcuts') }}</h2>
        <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ __('domain_dashboard.shortcuts_hint') }}</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (($dashboard['shortcuts'] ?? []) as $shortcut)
                <a href="{{ $shortcut['href'] }}" class="rounded-xl border border-[var(--ui-border)] px-4 py-3 text-sm font-semibold text-[var(--ui-primary-text)] no-underline transition hover:bg-[var(--ui-surface-muted)] hover:border-[var(--ui-primary)]">
                    {{ $shortcut['label'] }}
                </a>
            @endforeach
        </div>
    </x-ui.panel>
</div>
@endsection
