@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'full' => false,
    'disabled' => false,
])

@php
    $variantClasses = [
        'primary' => 'bg-night text-white hover:opacity-90',
        'secondary' => 'border border-slate-300 text-slate-800 hover:bg-slate-50',
        'outline' => 'border border-slate-300 text-slate-800 hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
        'inverse-outline' => 'border border-white/30 text-white hover:bg-white/10',
        'brand-primary' => 'bg-[#1a73e8] text-white hover:-translate-y-px hover:bg-[#0f4fa1] hover:shadow-md',
        'surface-muted' => 'bg-[#f1f3f4] text-[#202124] hover:bg-[#e8eaed]',
        'danger-outline' => 'border border-red-300 text-red-800 hover:border-[#d93025] hover:bg-[#fce8e6]',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-2 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-sm',
    ];

    $variantClass = $variantClasses[$variant] ?? $variantClasses['primary'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $fullClass = $full ? 'w-full' : null;
    $a11yClasses = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1a73e8]/35 focus-visible:ring-offset-2 focus-visible:ring-offset-white';
    $disabledClasses = 'disabled:cursor-not-allowed disabled:opacity-55 disabled:shadow-none disabled:hover:translate-y-0 disabled:hover:bg-inherit';
    $isDisabled = (bool) $disabled;
@endphp

@if ($href)
    <a
        href="{{ $isDisabled ? '#' : $href }}"
        @if($isDisabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->class(['inline-flex items-center justify-center rounded-xl font-semibold transition no-underline cursor-pointer', $variantClass, $sizeClass, $fullClass, $a11yClasses, $isDisabled ? 'pointer-events-none opacity-55' : null]) }}
    >
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($isDisabled) {{ $attributes->class(['inline-flex items-center justify-center rounded-xl font-semibold transition cursor-pointer', $variantClass, $sizeClass, $fullClass, $a11yClasses, $disabledClasses]) }}>
        {{ $slot }}
    </button>
@endif
