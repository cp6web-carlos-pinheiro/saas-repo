@props([
    'name',
    'size' => 'md',
    'label' => null,
])

@php
    if (! is_string($name) || preg_match('/^[a-z0-9-]+$/', $name) !== 1) {
        throw new InvalidArgumentException('Invalid Tabler icon name.');
    }

    $iconPath = resource_path('icons/tabler/'.$name.'.svg');

    if (! is_file($iconPath)) {
        throw new InvalidArgumentException("Tabler icon [{$name}] is not available locally.");
    }

    $svg = file_get_contents($iconPath);

    if ($svg === false) {
        throw new RuntimeException("Tabler icon [{$name}] could not be read.");
    }

    $svg = preg_replace('/^<!--.*?-->\s*/s', '', $svg) ?? $svg;
    $sizeClass = [
        'xs' => 'h-3.5 w-3.5',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
        'xl' => 'h-8 w-8',
    ][$size] ?? 'h-5 w-5';
@endphp

<span
    @if ($label)
        role="img"
        aria-label="{{ $label }}"
    @else
        aria-hidden="true"
    @endif
    {{ $attributes->class(['ui-icon inline-flex shrink-0 items-center justify-center', $sizeClass]) }}
>{!! $svg !!}</span>
