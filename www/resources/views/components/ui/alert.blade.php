@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
    'closeLabel' => 'Fechar alerta',
])

@php
    $variantClasses = [
        'success' => 'border-[color-mix(in_srgb,var(--ui-success)_35%,transparent)] bg-[var(--ui-success-soft)] text-[var(--ui-success)]',
        'error' => 'border-[color-mix(in_srgb,var(--ui-danger)_35%,transparent)] bg-[var(--ui-danger-soft)] text-[var(--ui-danger)]',
        'warning' => 'border-[color-mix(in_srgb,var(--ui-warning)_35%,transparent)] bg-[var(--ui-warning-soft)] text-[var(--ui-warning-text)]',
        'info' => 'border-[color-mix(in_srgb,var(--ui-info)_35%,transparent)] bg-[var(--ui-info-soft)] text-[var(--ui-info)]',
    ];
    $icons = [
        'success' => 'circle-check',
        'error' => 'circle-x',
        'warning' => 'alert-triangle',
        'info' => 'info-circle',
    ];
    $classes = $variantClasses[$variant] ?? $variantClasses['info'];
    $icon = $icons[$variant] ?? $icons['info'];
    $role = in_array($variant, ['error', 'warning'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" aria-live="polite" data-ui-alert {{ $attributes->class(['rounded-2xl border p-4 text-sm', $classes]) }}>
    <div class="flex items-start gap-3">
        <x-ui.icon :name="$icon" class="mt-0.5" />
        <div class="min-w-0 flex-1">
            @if ($title)<strong class="block font-semibold">{{ $title }}</strong>@endif
            <div @class(['leading-6', 'mt-1' => $title])>{{ $slot }}</div>
        </div>
        @if ($dismissible)
            <button type="button" class="ui-alert-close" data-ui-alert-dismiss aria-label="{{ $closeLabel }}">
                <x-ui.icon name="x" size="sm" />
            </button>
        @endif
    </div>
</div>
