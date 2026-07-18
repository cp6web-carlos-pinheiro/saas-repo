@props([
    'variant' => 'base',
    'href' => '#',
    'active' => false,
])

@php
    $variantClasses = [
        'base' => 'ui-menu-item',
        'industrial' => 'ui-menu-item ind-menu-item',
        'docs' => 'ui-menu-item docs-menu-item',
        'admin' => 'ui-menu-item admin-nav-link block rounded-lg px-4 py-2.5 border transition',
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
