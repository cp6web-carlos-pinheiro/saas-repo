@props([
    'id' => null,
    'name' => null,
    'month' => null,
    'selected' => null,
    'min' => null,
    'max' => null,
    'disabled' => false,
])

@php
    $resolvedId = $id ?? 'calendar-'.uniqid();
    $resolvedMonth = $month ?? ($selected ? substr($selected, 0, 7) : now()->format('Y-m'));
@endphp

<div
    id="{{ $resolvedId }}"
    {{ $attributes->class(['ui-calendar']) }}
    data-ui-calendar
    data-ui-calendar-month="{{ $resolvedMonth }}"
    data-ui-calendar-selected="{{ $selected }}"
    @if($min) data-ui-calendar-min="{{ $min }}" @endif
    @if($max) data-ui-calendar-max="{{ $max }}" @endif
    @if($disabled) data-ui-calendar-disabled="true" @endif
>
    <div class="ui-calendar-header">
        <button type="button" class="ui-icon-button" data-ui-calendar-previous aria-label="Mês anterior"><x-ui.icon name="chevron-left" size="sm" /></button>
        <strong class="ui-calendar-heading" aria-live="polite" data-ui-calendar-heading></strong>
        <button type="button" class="ui-icon-button" data-ui-calendar-next aria-label="Próximo mês"><x-ui.icon name="chevron-left" size="sm" class="rotate-180" /></button>
    </div>
    <div class="ui-calendar-weekdays" aria-hidden="true">
        @for ($weekday = 0; $weekday < 7; $weekday++)<span data-ui-calendar-weekday="{{ $weekday }}"></span>@endfor
    </div>
    <div class="ui-calendar-grid" role="grid" aria-label="Calendário" data-ui-calendar-grid></div>
    @if($name)<input type="hidden" name="{{ $name }}" value="{{ $selected }}" data-ui-calendar-input>@endif
</div>
