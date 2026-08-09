@props([
    'id' => null,
    'name',
    'value',
    'checked' => false,
    'disabled' => false,
    'description' => null,
])

@php($resolvedId = $id ?? str_replace(['[', ']', '.'], '_', $name.'_'.$value))

<label for="{{ $resolvedId }}" class="ui-choice-label">
    <input id="{{ $resolvedId }}" name="{{ $name }}" type="radio" value="{{ $value }}" @checked($checked) @disabled($disabled) {{ $attributes->class(['ui-choice']) }}>
    <span>
        <span class="ui-choice-title">{{ $slot }}</span>
        @if ($description)<span class="ui-choice-description">{{ $description }}</span>@endif
    </span>
</label>
