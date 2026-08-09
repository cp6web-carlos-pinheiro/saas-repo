@props(['position' => 'start'])

<span {{ $attributes->class(['ui-input-addon', 'ui-input-addon-end' => $position === 'end']) }}>{{ $slot }}</span>
