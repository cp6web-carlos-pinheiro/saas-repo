@props([
    'label',
    'value' => null,
    'format' => 'd/m/Y H:i',
    'emptyText' => '—',
])

@php
    $formattedDate = $emptyText;

    if ($value instanceof \DateTimeInterface) {
        $formattedDate = $value->format($format);
    } elseif (is_string($value) && trim($value) !== '') {
        try {
            $formattedDate = \Carbon\Carbon::parse($value)->format($format);
        } catch (\Throwable $exception) {
            $formattedDate = $value;
        }
    }
@endphp

<x-ui.definition-item :label="$label" {{ $attributes }}>
    <span class="inline-flex items-center gap-2">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-[#5f6368]" aria-hidden="true">
            <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 3v4M16 3v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <span class="tabular-nums">{{ $formattedDate }}</span>
    </span>
</x-ui.definition-item>
