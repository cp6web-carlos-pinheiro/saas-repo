@props([
    'cols' => 'sm:grid-cols-2 xl:grid-cols-3',
])

<dl {{ $attributes->class(['grid gap-4', $cols]) }}>
    {{ $slot }}
</dl>
