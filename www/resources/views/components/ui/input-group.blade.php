@props(['label' => null])

<div @if($label) role="group" aria-label="{{ $label }}" @endif {{ $attributes->class(['ui-input-group']) }}>
    {{ $slot }}
</div>
