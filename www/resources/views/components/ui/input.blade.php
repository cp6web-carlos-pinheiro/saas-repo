@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
    'unstyled' => false,
])

@php
    $resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : null);
@endphp

<input
    type="{{ $type }}"
    @if ($resolvedId)
        id="{{ $resolvedId }}"
    @endif
    @if ($name)
        name="{{ $name }}"
    @endif
    {{ $attributes->class($unstyled ? [] : ['ui-input']) }}
/>
