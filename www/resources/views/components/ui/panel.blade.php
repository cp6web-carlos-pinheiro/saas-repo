@props(['padding' => 'p-6'])

<div {{ $attributes->class(['rounded-3xl border border-[var(--ui-border)] bg-[var(--ui-surface)] text-[var(--ui-text)] shadow-[var(--ui-shadow-sm)]', $padding]) }}>
    {{ $slot }}
</div>
