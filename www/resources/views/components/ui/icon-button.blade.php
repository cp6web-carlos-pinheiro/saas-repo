@props([
    'icon',
    'label',
    'variant' => 'ghost',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $sizeClasses = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-11 w-11',
    ];
    $resolvedSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $iconSize = $size === 'sm' ? 'sm' : 'md';
    $isPlain = $variant === 'ghost';
@endphp

{{--
    Formaliza o padrão .ui-icon-button (já usado no shell) como componente reutilizável para
    ações compactas em toolbars, linhas de tabela e headers. `label` é obrigatório e vira
    aria-label + title, já que o botão não tem texto visível.
--}}
@if ($href)
    <a
        href="{{ $href }}"
        aria-label="{{ $label }}"
        title="{{ $label }}"
        {{ $attributes->class([
            'ui-icon-button inline-flex items-center justify-center rounded-xl transition',
            $resolvedSize,
            'ui-button-'.$variant => ! $isPlain,
        ]) }}
    ><x-ui.icon :name="$icon" :size="$iconSize" /></a>
@else
    <button
        type="{{ $type }}"
        aria-label="{{ $label }}"
        title="{{ $label }}"
        @disabled($disabled)
        {{ $attributes->class([
            'ui-icon-button inline-flex items-center justify-center rounded-xl transition',
            $resolvedSize,
            'ui-button-'.$variant => ! $isPlain,
            'disabled:cursor-not-allowed disabled:opacity-55',
        ]) }}
    ><x-ui.icon :name="$icon" :size="$iconSize" /></button>
@endif