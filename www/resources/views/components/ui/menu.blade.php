@props([
    'variant' => 'base',
    'ariaLabel' => null,
])

@php
    $variantClasses = [
        'base' => 'ui-menu',
        'docs' => 'ui-menu docs-list',
    ];

    $resolvedClass = $variantClasses[$variant] ?? $variantClasses['base'];
@endphp

<nav {{ $attributes->class([$resolvedClass])->merge(['aria-label' => $ariaLabel]) }}>
    {{ $slot }}
</nav>
