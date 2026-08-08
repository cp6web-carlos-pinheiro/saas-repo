@props([
    'id' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'description' => null,
])

@php($resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : 'switch-'.uniqid()))

<label for="{{ $resolvedId }}" class="ui-switch-label">
    <input id="{{ $resolvedId }}" name="{{ $name }}" type="checkbox" value="{{ $value }}" @checked($checked) @disabled($disabled) {{ $attributes->class(['peer sr-only']) }}>
    <span class="ui-switch-track" aria-hidden="true"><span class="ui-switch-thumb"></span></span>
    <span>
        <span class="ui-choice-title">{{ $slot }}</span>
        @if ($description)<span class="ui-choice-description">{{ $description }}</span>@endif
    </span>
</label>
