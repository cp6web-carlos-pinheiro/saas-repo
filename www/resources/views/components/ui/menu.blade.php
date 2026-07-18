@props([
    'variant' => 'base',
    'ariaLabel' => null,
])

@php
    $variantClasses = [
        'base' => 'ui-menu',
        'industrial' => 'ui-menu ind-module-list',
        'docs' => 'ui-menu docs-list',
        'admin' => 'ui-menu space-y-2 text-xl',
    ];

    $resolvedClass = $variantClasses[$variant] ?? $variantClasses['base'];
@endphp

<nav {{ $attributes->class([$resolvedClass])->merge(['aria-label' => $ariaLabel]) }}>
    {{ $slot }}
</nav>
