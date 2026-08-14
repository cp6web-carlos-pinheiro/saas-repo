@props([
    'title',
    'subtitle' => null,
    'titleClass' => 'text-2xl',
    'breadcrumbs' => [],
    'eyebrow' => null,
])

<div {{ $attributes->class(['ui-page-header']) }}>
    <div class="ui-page-header-main">
        @if (!empty($breadcrumbs))
            <x-ui.breadcrumb :items="$breadcrumbs" />
        @endif

        @if ($eyebrow)
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-primary)]">{{ $eyebrow }}</p>
        @endif

        <h1 class="font-display {{ $titleClass }} font-bold text-[var(--ui-text)]">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="ui-page-actions">{{ $actions }}</div>
    @endisset
</div>