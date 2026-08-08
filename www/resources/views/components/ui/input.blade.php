@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
    'unstyled' => false,
    'prefix' => null,
    'suffix' => null,
    'icon' => null,
    'iconPosition' => 'start',
])

@php
    $resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : null);
    $errorKey = is_string($name) ? str_replace(['][', '[', ']'], ['.', '.', ''], $name) : null;
    $hasError = is_string($errorKey) && $errors->has($errorKey);
    $ariaInvalid = $hasError || filter_var($attributes->get('aria-invalid', false), FILTER_VALIDATE_BOOLEAN);
    $describedBy = trim((string) ($attributes->get('aria-describedby') ?? ''));

    if ($hasError && is_string($resolvedId) && $resolvedId !== '') {
        $describedBy = trim($describedBy.' '.$resolvedId.'-error');
    }
    $hasDecoration = ! $unstyled && ($prefix !== null || $suffix !== null || $icon !== null);
    $resolvedIconPosition = $iconPosition === 'end' ? 'end' : 'start';
    $componentAttributes = $attributes->except(['aria-describedby', 'aria-invalid'])->class($unstyled ? [] : [
        'ui-input',
        'ui-input-group-control' => $hasDecoration,
    ]);
@endphp

@if ($hasDecoration)
<div class="ui-input-group">
    @if ($prefix !== null)
        <span class="ui-input-addon">{{ $prefix }}</span>
    @endif
    @if ($icon !== null && $resolvedIconPosition === 'start')
        <span class="ui-input-addon ui-input-addon-icon"><x-ui.icon :name="$icon" size="sm" /></span>
    @endif
@endif
<input
    type="{{ $type }}"
    @if ($resolvedId)
        id="{{ $resolvedId }}"
    @endif
    @if ($name)
        name="{{ $name }}"
    @endif
    @if ($ariaInvalid)
        aria-invalid="true"
    @endif
    @if ($describedBy !== '')
        aria-describedby="{{ $describedBy }}"
    @endif
    {{ $componentAttributes }}
/>
@if ($hasDecoration)
    @if ($icon !== null && $resolvedIconPosition === 'end')
        <span class="ui-input-addon ui-input-addon-icon"><x-ui.icon :name="$icon" size="sm" /></span>
    @endif
    @if ($suffix !== null)
        <span class="ui-input-addon">{{ $suffix }}</span>
    @endif
</div>
@endif
