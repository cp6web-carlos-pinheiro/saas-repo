@props([
    'id' => null,
    'name',
    'value' => null,
    'placeholder' => 'Selecione uma data',
    'min' => null,
    'max' => null,
])

@php
    $resolvedId = $id ?? str_replace(['[', ']', '.'], '_', $name);
    $parsedValue = $value ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
    $formattedValue = $parsedValue ? $parsedValue->format('d/m/Y') : null;
@endphp

<div {{ $attributes->class(['ui-date-picker']) }} data-ui-date-picker>
    <button type="button" class="ui-date-picker-trigger" aria-expanded="false" aria-controls="{{ $resolvedId }}-panel" data-ui-date-picker-trigger>
        <x-ui.icon name="calendar" size="sm" />
        <span data-ui-date-picker-label data-placeholder="{{ $placeholder }}">{{ $formattedValue ?? $placeholder }}</span>
        <x-ui.icon name="chevron-down" size="sm" class="ml-auto" />
    </button>
    <div id="{{ $resolvedId }}-panel" class="ui-date-picker-panel hidden" data-ui-date-picker-panel>
        <x-ui.calendar :id="$resolvedId.'-calendar'" :name="$name" :selected="$value" :min="$min" :max="$max" />
    </div>
</div>
