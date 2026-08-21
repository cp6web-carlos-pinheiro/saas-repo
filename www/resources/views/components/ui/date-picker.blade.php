@props([
    'id' => null,
    'name',
    'value' => null,
    'placeholder' => null,
    'clearLabel' => null,
    'min' => null,
    'max' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
    $resolvedId = $id ?? str_replace(['[', ']', '.'], '_', $name);
    $resolvedPlaceholder = $placeholder ?? __('ui.select_date');
    $resolvedClearLabel = $clearLabel ?? __('ui.clear_date');
    $parsedValue = $value ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
    $formattedValue = $parsedValue ? $parsedValue->format('d/m/Y') : null;
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $hasError = $errors->has($errorKey) || filter_var($attributes->get('aria-invalid', false), FILTER_VALIDATE_BOOLEAN);
    $describedBy = trim((string) ($attributes->get('aria-describedby') ?? ''));

    if ($errors->has($errorKey)) {
        $describedBy = implode(' ', array_unique(array_filter(explode(' ', trim($describedBy.' '.$resolvedId.'-error')))));
    }
@endphp

<div {{ $attributes->except(['aria-describedby', 'aria-invalid'])->class(['ui-date-picker']) }} data-ui-date-picker>
    <input
        id="{{ $resolvedId }}-input"
        class="ui-date-picker-native"
        type="date"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($min) min="{{ $min }}" @endif
        @if($max) max="{{ $max }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($hasError) aria-invalid="true" @endif
        @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        tabindex="-1"
        data-ui-date-picker-input
    >
    <button
        id="{{ $resolvedId }}"
        type="button"
        @class(['ui-date-picker-trigger', 'is-invalid' => $hasError])
        aria-expanded="false"
        aria-controls="{{ $resolvedId }}-panel"
        @if($hasError) aria-invalid="true" @endif
        @if($required) aria-required="true" @endif
        @if($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if($disabled || $readonly) disabled @endif
        data-ui-date-picker-trigger
    >
        <x-ui.icon name="calendar" size="sm" />
        <span data-ui-date-picker-label data-placeholder="{{ $resolvedPlaceholder }}">{{ $formattedValue ?? $resolvedPlaceholder }}</span>
        <x-ui.icon name="chevron-down" size="sm" class="ml-auto" />
    </button>
    <div id="{{ $resolvedId }}-panel" class="ui-date-picker-panel hidden" data-ui-date-picker-panel>
        <x-ui.calendar :id="$resolvedId.'-calendar'" :selected="$value" :min="$min" :max="$max" :disabled="$disabled || $readonly" />
        @unless($disabled || $readonly)
            <div class="ui-date-picker-actions">
                <button type="button" class="ui-date-picker-clear" data-ui-date-picker-clear>{{ $resolvedClearLabel }}</button>
            </div>
        @endunless
    </div>
</div>
