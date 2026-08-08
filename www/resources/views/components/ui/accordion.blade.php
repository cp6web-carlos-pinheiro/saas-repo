@props([
    'items' => [],
    'multiple' => false,
    'defaultOpen' => [],
])

<div {{ $attributes->class(['ui-accordion']) }} data-ui-accordion data-ui-accordion-multiple="{{ $multiple ? 'true' : 'false' }}">
    @foreach ($items as $index => $item)
        @php
            $itemKey = $item['value'] ?? $index;
            $disabled = (bool) ($item['disabled'] ?? false);
        @endphp
        <details @if(in_array($itemKey, $defaultOpen, true)) open @endif @if($disabled) data-disabled="true" @endif class="ui-accordion-item">
            <summary class="ui-accordion-trigger" @if($disabled) aria-disabled="true" tabindex="-1" @endif>
                <span>{{ $item['title'] ?? '' }}</span>
                <x-ui.icon name="chevron-down" size="sm" class="ui-accordion-icon" />
            </summary>
            <div class="ui-accordion-content">{{ $item['content'] ?? '' }}</div>
        </details>
    @endforeach
</div>
