@props([
    'variant' => 'base',
    'href' => '#',
    'active' => false,
])

@php
    $variantClasses = [
        'base' => 'ui-menu-item',
        'docs' => 'ui-menu-item docs-menu-item',
    ];

    $resolvedClass = $variantClasses[$variant] ?? $variantClasses['base'];
@endphp

<a
    href="{{ $href }}"
    @if ($active)
        aria-current="page"
    @endif
    {{ $attributes->class([$resolvedClass, 'is-active' => $active]) }}
>
    {{ $slot }}
</a>
