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
        <x-ui.icon name="calendar" size="sm" class="text-[var(--ui-text-muted)]" />
        <span class="tabular-nums">{{ $formattedDate }}</span>
    </span>
</x-ui.definition-item>