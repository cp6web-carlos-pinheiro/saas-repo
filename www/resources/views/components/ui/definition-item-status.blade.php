@props([
    'label',
    'value' => '—',
    'tone' => 'neutral',
    'inline' => false,
])

@php
    $toneClasses = [
        'success' => 'bg-emerald-100 text-emerald-700',
        'danger' => 'bg-rose-100 text-rose-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'info' => 'bg-sky-100 text-sky-700',
        'neutral' => 'bg-slate-100 text-slate-700',
    ];

    $badgeClass = $toneClasses[$tone] ?? $toneClasses['neutral'];
@endphp

@if ($inline)
    <span {{ $attributes->class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', $badgeClass]) }}>
        {{ $value }}
    </span>
@else
    <x-ui.definition-item :label="$label" {{ $attributes }}>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
            {{ $value }}
        </span>
    </x-ui.definition-item>
@endif
