@props([
    'id',
    'target',
    'active' => false,
    'disabled' => false,
])

<button
    type="button"
    id="{{ $id }}"
    role="tab"
    data-ui-tab
    aria-controls="{{ $target }}"
    aria-selected="{{ $active ? 'true' : 'false' }}"
    tabindex="{{ $active ? '0' : '-1' }}"
    @disabled($disabled)
    {{ $attributes->class(['ui-tab']) }}
>{{ $slot }}</button>
