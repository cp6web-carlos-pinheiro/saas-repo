@props([
    'column',
    'label',
    'sort' => request()->query('sort'),
    'direction' => request()->query('direction', 'asc'),
])

@php
    $isActive = $sort === $column;
    $currentDirection = $direction === 'desc' ? 'desc' : 'asc';
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->except('page'), [
        'sort' => $column,
        'direction' => $nextDirection,
    ]);
    $url = url()->current().'?'.\Illuminate\Support\Arr::query($query);
    $ariaSort = $isActive ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none';
    $icon = $isActive ? ($currentDirection === 'asc' ? '↑' : '↓') : '↕';
@endphp

<th {{ $attributes->class(['px-3 py-3']) }} aria-sort="{{ $ariaSort }}">
    <a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-[var(--ui-primary)]" title="{{ __('ui.sort_by', ['column' => $label]) }}">
        <span>{{ $label }}</span>
        <span aria-hidden="true">{{ $icon }}</span>
        <span class="sr-only">{{ $isActive ? ($currentDirection === 'asc' ? __('ui.sorted_ascending') : __('ui.sorted_descending')) : __('ui.activate_sort') }}</span>
    </a>
</th>