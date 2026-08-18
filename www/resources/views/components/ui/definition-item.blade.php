@props([
    'label',
    'valueClass' => null,
])

<div {{ $attributes->class(['rounded-xl border border-[var(--ui-border)] p-4']) }}>
    <dt class="text-xs font-semibold uppercase tracking-wide text-[var(--ui-text-muted)]">{{ $label }}</dt>
    <dd @class(['mt-2 text-sm font-medium text-[var(--ui-text)] break-words', $valueClass])>
        {{ $slot }}
    </dd>
</div>