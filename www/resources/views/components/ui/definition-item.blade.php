@props([
    'label',
    'valueClass' => null,
])

<div {{ $attributes->class(['rounded-xl border border-[#dadce0] p-4']) }}>
    <dt class="text-xs font-semibold uppercase tracking-wide text-[#5f6368]">{{ $label }}</dt>
    <dd @class(['mt-2 text-sm font-medium text-[#202124] break-words', $valueClass])>
        {{ $slot }}
    </dd>
</div>
