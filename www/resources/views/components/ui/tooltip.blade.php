@props([
    'content',
    'position' => 'top',
])

@php($tooltipId = 'tooltip-'.uniqid())

<span {{ $attributes->class(['ui-tooltip']) }} data-ui-tooltip>
    <span aria-describedby="{{ $tooltipId }}" class="ui-tooltip-trigger">{{ $slot }}</span>
    <span id="{{ $tooltipId }}" role="tooltip" class="ui-tooltip-content ui-tooltip-{{ $position }}">{{ $content }}</span>
</span>
