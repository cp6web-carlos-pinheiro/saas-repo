@props(['label' => 'Abas'])

<div data-ui-tabs {{ $attributes->class(['ui-tabs']) }} aria-label="{{ $label }}">{{ $slot }}</div>
