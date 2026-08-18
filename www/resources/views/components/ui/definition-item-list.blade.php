@props([
    'label',
    'items' => [],
    'emptyText' => '—',
])

@php
    $normalizedItems = collect($items)
        ->filter(static fn ($item): bool => is_scalar($item) && trim((string) $item) !== '')
        ->map(static fn ($item): string => (string) $item)
        ->values();
@endphp

<x-ui.definition-item :label="$label" {{ $attributes }}>
    @if ($normalizedItems->isEmpty())
        <span class="text-[var(--ui-text-muted)]">{{ $emptyText }}</span>
    @else
        <ul class="space-y-1">
            @foreach ($normalizedItems as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endif
</x-ui.definition-item>