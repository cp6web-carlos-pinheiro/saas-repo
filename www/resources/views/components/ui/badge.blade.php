@props([
    'variant' => 'neutral',
    'size' => 'md',
])

@php
    $variantClass = in_array($variant, ['neutral', 'primary', 'success', 'warning', 'danger', 'info', 'outline'], true)
        ? 'ui-badge-'.$variant
        : 'ui-badge-neutral';
    $sizeClass = $size === 'sm' ? 'ui-badge-sm' : 'ui-badge-md';
@endphp

<span {{ $attributes->class(['ui-badge', $variantClass, $sizeClass]) }}>{{ $slot }}</span>
