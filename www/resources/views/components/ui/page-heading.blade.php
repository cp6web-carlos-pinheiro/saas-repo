@props([
    'title',
    'subtitle' => null,
    'titleClass' => 'text-2xl',
])

<div {{ $attributes }}>
    <h1 class="font-display {{ $titleClass }} font-bold">{{ $title }}</h1>

    @if ($subtitle)
        <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
    @endif
</div>
