@props([
    'select2' => true,
    'id' => null,
    'name' => null,
])

@php
    $resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : null);
@endphp

<select
    @if ($resolvedId)
        id="{{ $resolvedId }}"
    @endif
    @if ($name)
        name="{{ $name }}"
    @endif
    {{ $attributes->class(['ui-select']) }}
    @if ($select2)
        data-ui-select2="true"
    @endif
>
    {{ $slot }}
</select>
