@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'full' => false,
    'disabled' => false,
    'readonly' => false,
    'loading' => false,
    'loadingLabel' => 'Carregando',
])

@php
    $variantClasses = [
        'primary' => 'ui-button-primary',
        'neutral' => 'ui-button-neutral',
        'info' => 'ui-button-info',
        'success' => 'ui-button-success',
        'warning' => 'ui-button-warning',
        'secondary' => 'ui-button-secondary',
        'outline' => 'ui-button-outline',
        'danger' => 'ui-button-danger',
        'ghost' => 'ui-button-ghost',
        'inverse-outline' => 'border border-white/30 text-white hover:bg-white/10',
        'brand-primary' => 'bg-[#1a73e8] text-white hover:-translate-y-px hover:bg-[#0f4fa1] hover:shadow-md',
        'surface-muted' => 'bg-[#f1f3f4] text-[#202124] hover:bg-[#e8eaed]',
        'danger-outline' => 'border border-red-300 text-red-800 hover:border-[#d93025] hover:bg-[#fce8e6]',
        'material-back' => 'border border-[#c7cacf] bg-white text-[#3c4043] hover:bg-[#f8f9fa] hover:border-[#9aa0a6]',
        'material-versions' => 'border border-[#8ab4f8] bg-[#e8f0fe] text-[#174ea6] hover:bg-[#d2e3fc] hover:border-[#669df6]',
        'material-edit' => 'bg-[#1a73e8] text-white hover:-translate-y-px hover:bg-[#1557b0] hover:shadow-md',
        'material-remove' => 'bg-[#d93025] text-white hover:bg-[#b3261e] hover:shadow-md',
    ];

    $sizeClasses = [
        'sm' => 'min-h-9 px-3 py-2 text-xs',
        'md' => 'min-h-10 px-4 py-2 text-sm',
        'lg' => 'min-h-12 px-6 py-3 text-base',
    ];

    $variantClass = $variantClasses[$variant] ?? $variantClasses['primary'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $fullClass = $full ? 'w-full' : null;
    $a11yClasses = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ui-focus)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--ui-surface)]';
    $disabledClasses = 'disabled:cursor-not-allowed disabled:opacity-55 disabled:shadow-none disabled:hover:translate-y-0';
    $isLoading = (bool) $loading;
    $isDisabled = (bool) $disabled || $isLoading;
    $isReadonly = (bool) $readonly && ! $isDisabled;
    $isUnavailable = $isDisabled || $isReadonly;
@endphp

@if ($href)
    <a
        href="{{ $isUnavailable ? '#' : $href }}"
        @if($isUnavailable) aria-disabled="true" @endif
        @if($isDisabled) tabindex="-1" @endif
        @if($isReadonly) data-ui-readonly="true" @endif
        @if($isLoading) aria-busy="true" @endif
        {{ $attributes->class(['ui-button inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition no-underline cursor-pointer', $variantClass, $sizeClass, $fullClass, $a11yClasses, $isDisabled ? 'pointer-events-none opacity-55' : null, 'ui-button-readonly' => $isReadonly]) }}
    >
        @if ($isLoading)
            <span class="ui-spinner" aria-hidden="true"></span>
            <span>{{ $loadingLabel }}</span>
        @else
            {{ $slot }}
        @endif
    </a>
@else
    <button type="{{ $type }}" @disabled($isDisabled) @if($isReadonly) aria-disabled="true" data-ui-readonly="true" @endif @if($isLoading) aria-busy="true" @endif {{ $attributes->class(['ui-button inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition cursor-pointer', $variantClass, $sizeClass, $fullClass, $a11yClasses, $disabledClasses, 'ui-button-readonly' => $isReadonly]) }}>
        @if ($isLoading)
            <span class="ui-spinner" aria-hidden="true"></span>
            <span>{{ $loadingLabel }}</span>
        @else
            {{ $slot }}
        @endif
    </button>
@endif
