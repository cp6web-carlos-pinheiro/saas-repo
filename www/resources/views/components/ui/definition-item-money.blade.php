@props([
    'label',
    'amountCents' => null,
    'currency' => 'R$',
    'nullText' => '—',
])

@php
    $hasAmount = is_numeric($amountCents);
    $formattedAmount = $hasAmount
        ? $currency.' '.number_format(((float) $amountCents) / 100, 2, ',', '.')
        : $nullText;
@endphp

<x-ui.definition-item :label="$label" {{ $attributes }}>
    <span class="tabular-nums">{{ $formattedAmount }}</span>
</x-ui.definition-item>
