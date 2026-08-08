@props([
    'caption' => null,
    'compact' => false,
])

<div class="ui-table-wrap">
    <table {{ $attributes->class(['ui-table', 'ui-table-compact' => $compact]) }}>
        @if ($caption)<caption class="sr-only">{{ $caption }}</caption>@endif
        {{ $slot }}
    </table>
</div>
