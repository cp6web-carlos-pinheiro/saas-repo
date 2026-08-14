@props([
    'icon' => 'package',
    'title',
    'description' => null,
])

<div {{ $attributes->class(['ui-empty-state']) }}>
    <span class="ui-empty-state-icon"><x-ui.icon :name="$icon" size="lg" /></span>
    <p class="ui-empty-state-title">{{ $title }}</p>
    @if ($description)
        <p class="ui-empty-state-description">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-2 flex flex-wrap items-center justify-center gap-2">{{ $actions }}</div>
    @endisset
</div>