@props([
    'id' => null,
    'name' => null,
    'rows' => 3,
    'unstyled' => false,
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
    $componentAttributes = $attributes->except(['aria-describedby', 'aria-invalid'])->class($unstyled ? [] : ['ui-textarea']);
@endphp

<textarea
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
    rows="{{ $rows }}"
    {{ $componentAttributes }}
>{{ $slot }}</textarea>
