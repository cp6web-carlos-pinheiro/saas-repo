@props([
    'variant' => 'base',
    'title' => null,
    'subtitle' => null,
    'headerClass' => null,
    'contentClass' => null,
    'footerClass' => null,
])

@php
    $variantStyles = [
        'base' => [
            'container' => 'flex flex-col',
            'header' => '',
            'content' => '',
            'footer' => '',
        ],
        'industrial' => [
            'container' => 'ind-sidebar',
            'header' => 'ind-brand',
            'content' => 'ind-module-list',
            'footer' => 'ind-logout-area',
        ],
        'docs' => [
            'container' => 'docs-sidebar',
            'header' => '',
            'content' => 'docs-sidebar-scroll',
            'footer' => '',
        ],
        'admin' => [
            'container' => 'w-72 shrink-0 bg-slate-50 border-r border-slate-200 text-slate-900 p-6 flex flex-col',
            'header' => '',
            'content' => 'mt-8',
            'footer' => 'mt-auto pt-6',
        ],
    ];

    $styles = $variantStyles[$variant] ?? $variantStyles['base'];

    $resolvedHeaderClass = $headerClass ?? $styles['header'];
    $resolvedContentClass = $contentClass ?? $styles['content'];
    $resolvedFooterClass = $footerClass ?? $styles['footer'];

    $hasHeaderSlot = isset($header);
    $hasFooterSlot = isset($footer);
@endphp

<aside {{ $attributes->class([$styles['container']]) }}>
    @if ($hasHeaderSlot || filled($title) || filled($subtitle))
        <div @class([$resolvedHeaderClass])>
            @if ($hasHeaderSlot)
                {{ $header }}
            @else
                @if (filled($title))
                    <h1 class="font-display text-2xl font-bold">{{ $title }}</h1>
                @endif

                @if (filled($subtitle))
                    <p class="mt-2 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
    @endif

    <div @class([$resolvedContentClass])>
        {{ $slot }}
    </div>

    @if ($hasFooterSlot)
        <div @class([$resolvedFooterClass])>
            {{ $footer }}
        </div>
    @endif
</aside>
