@props([
    'items' => [],
    'ariaLabel' => 'Breadcrumb',
])

@php
    $normalizedItems = collect($items)
        ->filter(static fn ($item) => is_array($item) && filled($item['label'] ?? null))
        ->values();
@endphp

@if ($normalizedItems->isNotEmpty())
    <nav {{ $attributes->class(['ui-breadcrumb'])->merge(['aria-label' => $ariaLabel]) }}>
        <ol>
            @foreach ($normalizedItems as $index => $item)
                @php
                    $isLast = $index === $normalizedItems->count() - 1;
                    $href = $item['href'] ?? null;
                @endphp

                <li>
                    @if (! $isLast && filled($href))
                        <a href="{{ $href }}">{{ $item['label'] }}</a>
                    @else
                        <span aria-current="page">{{ $item['label'] }}</span>
                    @endif
                </li>

                @if (! $isLast)
                    <li class="ui-breadcrumb-separator" aria-hidden="true">/</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
