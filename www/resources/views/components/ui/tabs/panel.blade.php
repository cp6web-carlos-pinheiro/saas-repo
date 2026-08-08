@props([
    'id',
    'labelledby',
    'active' => false,
])

<section id="{{ $id }}" role="tabpanel" aria-labelledby="{{ $labelledby }}" tabindex="0" @if(! $active) hidden @endif {{ $attributes->class(['ui-tab-panel']) }}>{{ $slot }}</section>
