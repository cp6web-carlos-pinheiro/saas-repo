@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $variantClasses = [
        'primary' => 'bg-night text-white hover:opacity-90',
        'secondary' => 'border border-slate-300 text-slate-700 hover:bg-slate-50',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-2 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-sm',
    ];

    $variantClass = $variantClasses[$variant] ?? $variantClasses['primary'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<button type="{{ $type }}" {{ $attributes->class(['rounded-xl font-semibold transition', $variantClass, $sizeClass]) }}>
    {{ $slot }}
</button>
