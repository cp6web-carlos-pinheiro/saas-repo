@props([
    'id' => null,
    'label' => 'Ações',
    'align' => 'right',
    'disabled' => false,
])

@php
    $resolvedId = $id ?? 'dropdown-'.uniqid();
    $alignmentClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div class="relative inline-flex" data-ui-dropdown>
    <x-ui.button type="button" variant="outline" :disabled="$disabled" data-ui-dropdown-trigger aria-haspopup="menu" aria-expanded="false" aria-controls="{{ $resolvedId }}">
        {{ $label }}
        <x-ui.icon name="chevron-down" size="sm" class="ui-dropdown-chevron" />
    </x-ui.button>
    <div id="{{ $resolvedId }}" role="menu" data-ui-dropdown-menu class="ui-dropdown-menu {{ $alignmentClass }} hidden">
        {{ $slot }}
    </div>
</div>
