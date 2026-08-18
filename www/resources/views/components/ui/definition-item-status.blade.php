@props([
    'label',
    'value' => '—',
    'tone' => 'neutral',
    'inline' => false,
])

@php
    // Mesmos tons semânticos usados por x-ui.badge (ui-badge-*), definidos com tokens
    // --ui-* em resources/css/app.css — mantém o pill de status coerente entre light e
    // dark mode em vez da paleta fixa emerald/rose/amber/sky/slate.
    $toneClasses = ['success', 'danger', 'warning', 'info', 'neutral'];
    $badgeClass = 'ui-badge-'.(in_array($tone, $toneClasses, true) ? $tone : 'neutral');
@endphp

@if ($inline)
    <span {{ $attributes->class(['ui-badge ui-badge-sm', $badgeClass]) }}>
        {{ $value }}
    </span>
@else
    <x-ui.definition-item :label="$label" {{ $attributes }}>
        <span class="ui-badge ui-badge-sm {{ $badgeClass }}">
            {{ $value }}
        </span>
    </x-ui.definition-item>
@endif