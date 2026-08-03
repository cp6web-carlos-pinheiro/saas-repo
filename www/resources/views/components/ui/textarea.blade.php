@props([
    'id' => null,
    'name' => null,
    'rows' => 3,
    'unstyled' => false,
])

@php
    $resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : null);
@endphp

<textarea
    @if ($resolvedId)
        id="{{ $resolvedId }}"
    @endif
    @if ($name)
        name="{{ $name }}"
    @endif
    rows="{{ $rows }}"
    {{ $attributes->class($unstyled ? [] : ['ui-textarea']) }}
>{{ $slot }}</textarea>
