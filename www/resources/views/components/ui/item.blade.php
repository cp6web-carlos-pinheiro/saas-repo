@props([
    'title',
    'description' => null,
    'icon' => null,
    'href' => null,
])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class(['ui-item']) }}>
    @if($icon)<span class="ui-item-media"><x-ui.icon :name="$icon" /></span>@endif
    <span class="ui-item-content">
        <strong class="ui-item-title">{{ $title }}</strong>
        @if($description)<span class="ui-item-description">{{ $description }}</span>@endif
    </span>
    @isset($actions)<span class="ui-item-actions">{{ $actions }}</span>@endisset
</{{ $tag }}>
