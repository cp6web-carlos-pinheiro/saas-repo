@props([
    'id',
    'title',
    'description' => null,
    'size' => 'md',
    'open' => false,
    'closeLabel' => 'Fechar modal',
])

@php
    $panelClasses = [
        'sm' => 'w-full max-w-sm rounded-2xl',
        'md' => 'w-full max-w-lg rounded-2xl',
        'lg' => 'w-full max-w-2xl rounded-2xl',
        'xl' => 'w-full max-w-5xl rounded-2xl',
        'full' => 'h-[calc(100dvh-2rem)] w-full max-w-none rounded-2xl',
        'sheet' => 'ml-auto h-dvh w-full max-w-md rounded-none',
    ];
    $resolvedPanelClass = $panelClasses[$size] ?? $panelClasses['md'];
    $isSheet = $size === 'sheet';
@endphp

<div
    id="{{ $id }}"
    data-ui-modal
    data-ui-modal-size="{{ $size }}"
    aria-hidden="{{ $open ? 'false' : 'true' }}"
    {{ $attributes->class(['ui-modal-root', 'hidden' => ! $open, 'items-center justify-center p-4' => ! $isSheet, 'items-stretch justify-end p-0' => $isSheet]) }}
>
    <button type="button" class="ui-modal-backdrop" data-ui-modal-close tabindex="-1" aria-label="{{ $closeLabel }}"></button>
    <section role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" @if($description) aria-describedby="{{ $id }}-description" @endif tabindex="-1" class="ui-modal-panel {{ $resolvedPanelClass }}">
        <header class="flex items-start justify-between gap-4 border-b border-[var(--ui-border)] px-6 py-5">
            <div>
                <h2 id="{{ $id }}-title" class="font-display text-xl font-bold text-[var(--ui-text)]">{{ $title }}</h2>
                @if ($description)<p id="{{ $id }}-description" class="mt-1 text-sm leading-6 text-[var(--ui-text-muted)]">{{ $description }}</p>@endif
            </div>
            <button type="button" class="ui-icon-button -mr-2 -mt-1" data-ui-modal-close aria-label="{{ $closeLabel }}"><x-ui.icon name="x" /></button>
        </header>
        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5 text-sm leading-7 text-[var(--ui-text-muted)]">{{ $slot }}</div>
        @isset($footer)
            <footer class="flex flex-wrap justify-end gap-3 border-t border-[var(--ui-border)] px-6 py-4">{{ $footer }}</footer>
        @endisset
    </section>
</div>
