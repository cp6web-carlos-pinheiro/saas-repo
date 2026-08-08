@props([
    'title',
    'description' => null,
    'open' => false,
])

<details @if($open) open @endif {{ $attributes->class(['ui-collapsible']) }}>
    <summary class="ui-collapsible-trigger">
        <span>
            <strong>{{ $title }}</strong>
            @if($description)<small>{{ $description }}</small>@endif
        </span>
        <x-ui.icon name="chevron-down" size="sm" class="ui-collapsible-icon" />
    </summary>
    <div class="ui-collapsible-content">{{ $slot }}</div>
</details>
