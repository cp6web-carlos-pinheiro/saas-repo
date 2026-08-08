@props([
    'id' => null,
    'label' => null,
    'value' => 0,
    'max' => 100,
    'suffix' => '%',
    'showValue' => true,
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $numericMax = max(1, (float) $max);
    $numericValue = min($numericMax, max(0, (float) $value));
    $progress = ($numericValue / $numericMax) * 100;
    $variantClass = in_array($variant, ['primary', 'success', 'warning', 'danger'], true)
        ? 'ui-progress-'.$variant
        : 'ui-progress-primary';
    $sizeClass = in_array($size, ['sm', 'md', 'lg'], true) ? 'ui-progress-'.$size : 'ui-progress-md';
@endphp

<div
    @if($id) id="{{ $id }}" @endif
    {{ $attributes->class(['ui-progress', $variantClass, $sizeClass]) }}
    role="progressbar"
    aria-valuemin="0"
    aria-valuemax="{{ $numericMax + 0 }}"
    aria-valuenow="{{ $numericValue + 0 }}"
    @if($label) aria-label="{{ $label }}" @endif
    data-ui-progress
    data-ui-progress-suffix="{{ $suffix }}"
>
    @if ($label || $showValue)
        <div class="ui-progress-meta">
            @if ($label)<span>{{ $label }}</span>@endif
            @if ($showValue)<span data-ui-progress-value>{{ $numericValue + 0 }}{{ $suffix }}</span>@endif
        </div>
    @endif
    <div class="ui-progress-track" aria-hidden="true">
        <span class="ui-progress-fill" data-ui-progress-fill style="width: {{ $progress }}%"></span>
    </div>
</div>
