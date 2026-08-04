@props(['variant' => 'info'])

@php
    $variantClasses = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'error' => 'border-rose-200 bg-rose-50 text-rose-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'info' => 'border-sky-200 bg-sky-50 text-sky-700',
    ];

    $classes = $variantClasses[$variant] ?? $variantClasses['info'];
    $role = in_array($variant, ['error', 'warning'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" aria-live="polite" {{ $attributes->class(['rounded-2xl border px-4 py-3 text-sm', $classes]) }}>
    {{ $slot }}
</div>
