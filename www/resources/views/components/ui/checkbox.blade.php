@props([
    'id' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'description' => null,
])

@php($resolvedId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) : 'checkbox-'.uniqid()))

<label for="{{ $resolvedId }}" class="ui-choice-label">
    <input id="{{ $resolvedId }}" name="{{ $name }}" type="checkbox" value="{{ $value }}" @checked($checked) @disabled($disabled) {{ $attributes->class(['ui-choice']) }}>
    <span>
        <span class="ui-choice-title">{{ $slot }}</span>
        @if ($description)<span class="ui-choice-description">{{ $description }}</span>@endif
    </span>
</label>
