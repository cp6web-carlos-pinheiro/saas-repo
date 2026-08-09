@props([
    'id' => null,
    'name' => null,
    'value' => 50,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'suffix' => '',
    'showValue' => true,
    'disabled' => false,
])

@php
    $resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : 'slider-'.uniqid());
    $numericMin = (float) $min;
    $numericMax = max((float) $max, $numericMin + 1);
    $numericValue = min($numericMax, max($numericMin, (float) $value));
    $progress = (($numericValue - $numericMin) / ($numericMax - $numericMin)) * 100;
@endphp

<div {{ $attributes->class(['ui-slider']) }} data-ui-slider data-ui-slider-suffix="{{ $suffix }}" style="--ui-slider-progress: {{ $progress }}%">
    <input
        id="{{ $resolvedId }}"
        @if($name) name="{{ $name }}" @endif
        type="range"
        min="{{ $min }}"
        max="{{ $max }}"
        step="{{ $step }}"
        value="{{ $numericValue }}"
        @disabled($disabled)
        data-ui-slider-input
    >
    @if ($showValue)
        <output for="{{ $resolvedId }}" data-ui-slider-output>{{ $numericValue + 0 }}{{ $suffix }}</output>
    @endif
</div>
