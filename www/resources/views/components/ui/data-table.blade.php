@props([
    'filterPlaceholder' => 'Filtrar resultados...',
    'pageSize' => 5,
])

<div {{ $attributes->class(['ui-data-table']) }} data-ui-data-table data-ui-data-table-page-size="{{ max(1, (int) $pageSize) }}">
    <div class="ui-data-table-toolbar">
        <x-ui.input icon="search" :placeholder="$filterPlaceholder" aria-label="{{ $filterPlaceholder }}" data-ui-data-table-filter />
        @isset($actions)<div class="ml-auto">{{ $actions }}</div>@endisset
    </div>
    {{ $slot }}
    <div class="ui-data-table-footer">
        <span data-ui-data-table-status aria-live="polite"></span>
        <div class="flex gap-2">
            <x-ui.button variant="outline" size="sm" data-ui-data-table-previous><x-ui.icon name="chevron-left" size="sm" />{{ __('ui.previous') }}</x-ui.button>
            <x-ui.button variant="outline" size="sm" data-ui-data-table-next>{{ __('ui.next') }}<x-ui.icon name="chevron-left" size="sm" class="rotate-180" /></x-ui.button>
        </div>
    </div>
</div>
