@props(['padding' => 'p-6'])

<div {{ $attributes->class(['rounded-3xl border border-slate-200 bg-white shadow-soft', $padding]) }}>
    {{ $slot }}
</div>
