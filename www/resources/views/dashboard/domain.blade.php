@extends('layouts.client-area')

@section('title', ($dashboard['title'] ?? __('ui.dashboard')).' | '.__('ui.app_name'))
@section('client-page-title', $dashboard['title'] ?? __('ui.dashboard'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-[#5f6368]">{{ __('ui.dashboard') }}</p>
            <h1 class="font-display text-3xl font-bold">{{ $dashboard['title'] ?? __('ui.dashboard') }}</h1>
            <p class="mt-2 max-w-3xl text-sm text-[#5f6368]">{{ $dashboard['description'] ?? '' }}</p>
        </div>
        <x-ui.button :href="route('dashboard.industrial')" variant="surface-muted" class="rounded-full">{{ __('ui.back_to_dashboard') }}</x-ui.button>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-lg font-semibold">Pendencias</h2>
                <span class="rounded-full border border-[#f9ab00] bg-[#fef7e0] px-3 py-1 text-sm font-semibold text-[#8a4b00]">{{ $dashboard['pending_total'] ?? 0 }}</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse (($dashboard['pending_items'] ?? []) as $item)
                    <div @class([
                        'flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5',
                        'border-[#f1f3f4]' => ($item['severity'] ?? 'normal') === 'normal',
                        'border-[#f9ab00] bg-[#fef7e0]' => ($item['severity'] ?? 'normal') === 'attention',
                        'border-[#d93025] bg-[#fce8e6]' => ($item['severity'] ?? 'normal') === 'critical',
                    ])>
                        <span class="text-sm text-[#3c4043]">{{ $item['label'] }}</span>
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide',
                                'border-[#dadce0] bg-white text-[#5f6368]' => ($item['severity'] ?? 'normal') === 'normal',
                                'border-[#f9ab00] bg-[#fef7e0] text-[#8a4b00]' => ($item['severity'] ?? 'normal') === 'attention',
                                'border-[#d93025] bg-[#fce8e6] text-[#a50e0e]' => ($item['severity'] ?? 'normal') === 'critical',
                            ])>{{ $item['severity_label'] ?? 'Normal' }}</span>
                            <span class="rounded-lg bg-[#f1f3f4] px-2 py-1 text-xs font-semibold text-[#202124]">{{ $item['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#5f6368]">Nenhuma pendencia mapeada para este dominio.</p>
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-lg font-semibold">Em andamento</h2>
                <span class="rounded-full border border-[#34a853] bg-[#e6f4ea] px-3 py-1 text-sm font-semibold text-[#0f5f2f]">{{ $dashboard['in_progress_total'] ?? 0 }}</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse (($dashboard['in_progress_items'] ?? []) as $item)
                    <div @class([
                        'flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5',
                        'border-[#f1f3f4]' => ($item['severity'] ?? 'normal') === 'normal',
                        'border-[#f9ab00] bg-[#fef7e0]' => ($item['severity'] ?? 'normal') === 'attention',
                        'border-[#d93025] bg-[#fce8e6]' => ($item['severity'] ?? 'normal') === 'critical',
                    ])>
                        <span class="text-sm text-[#3c4043]">{{ $item['label'] }}</span>
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide',
                                'border-[#dadce0] bg-white text-[#5f6368]' => ($item['severity'] ?? 'normal') === 'normal',
                                'border-[#f9ab00] bg-[#fef7e0] text-[#8a4b00]' => ($item['severity'] ?? 'normal') === 'attention',
                                'border-[#d93025] bg-[#fce8e6] text-[#a50e0e]' => ($item['severity'] ?? 'normal') === 'critical',
                            ])>{{ $item['severity_label'] ?? 'Normal' }}</span>
                            <span class="rounded-lg bg-[#f1f3f4] px-2 py-1 text-xs font-semibold text-[#202124]">{{ $item['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#5f6368]">Nenhum item em andamento para este dominio.</p>
                @endforelse
            </div>
        </x-ui.panel>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <h2 class="text-lg font-semibold">Atalhos de gerenciamento</h2>
        <p class="mt-1 text-sm text-[#5f6368]">Acesse rapidamente as telas operacionais deste dominio.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (($dashboard['shortcuts'] ?? []) as $shortcut)
                <a href="{{ $shortcut['href'] }}" class="rounded-xl border border-[#dadce0] px-4 py-3 text-sm font-semibold text-[#174ea6] no-underline transition hover:bg-[#f8fafd] hover:border-[#aecbfa]">
                    {{ $shortcut['label'] }}
                </a>
            @endforeach
        </div>
    </x-ui.panel>
</div>
@endsection
