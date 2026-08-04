@props([
    'select2' => true,
    'id' => null,
    'name' => null,
])

@php
    $resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : null);
    $errorKey = is_string($name) ? str_replace(['][', '[', ']'], ['.', '.', ''], $name) : null;
    $hasError = is_string($errorKey) && $errors->has($errorKey);
    $describedBy = trim((string) ($attributes->get('aria-describedby') ?? ''));

    if ($hasError && is_string($resolvedId) && $resolvedId !== '') {
        $describedBy = trim($describedBy.' '.$resolvedId.'-error');
    }
@endphp

@php
    $componentAttributes = $attributes->except(['aria-describedby', 'aria-invalid'])->class(['ui-select']);
@endphp

<select
    @if ($resolvedId)
        id="{{ $resolvedId }}"
    @endif
    @if ($name)
        name="{{ $name }}"
    @endif
    @if ($hasError)
        aria-invalid="true"
    @endif
    @if ($describedBy !== '')
        aria-describedby="{{ $describedBy }}"
    @endif
    {{ $componentAttributes }}
    @if ($select2)
        data-ui-select2="true"
    @endif
>
    {{ $slot }}
</select>
