@props([
    // Navigation items: [
    //   'label' => string, 'icon' => tabler icon name, 'href' => string, 'active' => bool,
    //   'children' => [ ['label' => string, 'href' => string, 'active' => bool], ... ],
    // ]
    'navigation' => [],
    'navigationLabel' => null,
    'brandName' => null,
    'brandHref' => null,
    'brandSubtitle' => null,
    'brandInitial' => null,
    'headerTitle' => null,
    'headerSubtitle' => null,
    'logoutAction' => null,
])

@php
    $resolvedBrandName = $brandName ?? __('ui.app_name');
    $resolvedBrandInitial = $brandInitial ?? mb_strtoupper(mb_substr($resolvedBrandName, 0, 1));
    $resolvedNavigationLabel = $navigationLabel ?? __('ui.modules');
@endphp

{{--
    Shell de aplicação aprovado no Layout System.

    Este componente concentra o menu lateral, o header e a área principal, sem acoplar
    nenhuma navegação específica: quem consome (catálogo, área cliente, Global Admin,
    páginas públicas) informa seus próprios itens via `navigation` e conteúdo via slots.
    O body da página que usar este shell deve receber a classe `ui-shell` para herdar os
    tokens semânticos --ui-* e o fundo do shell.
--}}
<div class="ds-app-shell" data-ds-sidebar-shell>
    <aside id="ds-app-sidebar" class="ds-app-sidebar" data-ds-sidebar aria-label="{{ $resolvedNavigationLabel }}">
        <div class="ds-sidebar-brand">
            <a href="{{ $brandHref ?? '#' }}" class="ds-sidebar-brand-link" aria-label="{{ $resolvedBrandName }}" title="{{ $resolvedBrandName }}">
                <span class="ds-sidebar-brand-mark" aria-hidden="true">{{ $resolvedBrandInitial }}</span>
                <span class="min-w-0" data-ds-sidebar-label>
                    <strong class="block truncate text-sm text-[var(--ui-text)]">{{ $resolvedBrandName }}</strong>
                    @if ($brandSubtitle)
                        <small class="block truncate text-xs text-[var(--ui-text-muted)]">{{ $brandSubtitle }}</small>
                    @endif
                </span>
            </a>
            <button
                type="button"
                class="ds-sidebar-toggle"
                data-ds-sidebar-toggle
                aria-expanded="true"
                aria-controls="ds-app-sidebar"
                aria-label="{{ __('ui.collapse_sidebar') }}"
                title="{{ __('ui.toggle_sidebar') }}"
                data-collapse-label="{{ __('ui.collapse_sidebar') }}"
                data-expand-label="{{ __('ui.expand_sidebar') }}"
            >
                <x-ui.icon name="chevron-left" data-ds-sidebar-toggle-icon />
            </button>
        </div>

        <nav class="ds-sidebar-nav" aria-label="{{ $resolvedNavigationLabel }}">
            <p class="ds-sidebar-eyebrow" data-ds-sidebar-label>{{ $resolvedNavigationLabel }}</p>
            @foreach ($navigation as $item)
                @if (!empty($item['children']))
                    @php
                        $submenuId = 'ds-submenu-'.$loop->index;
                        $hasActiveChild = collect($item['children'])->contains(fn ($child) => $child['active'] ?? false);
                        $groupActive = $hasActiveChild || ($item['active'] ?? false);
                    @endphp
                    <div @class(['ds-sidebar-group', 'is-open' => $groupActive]) data-ds-sidebar-group>
                        <button
                            type="button"
                            @class(['ds-sidebar-link w-full', 'is-active' => $groupActive])
                            aria-expanded="{{ $groupActive ? 'true' : 'false' }}"
                            aria-controls="{{ $submenuId }}"
                            aria-label="{{ $item['label'] }}"
                            title="{{ $item['label'] }}"
                            data-ds-sidebar-submenu-toggle
                        >
                            @if (!empty($item['icon']))
                                <x-ui.icon :name="$item['icon']" />
                            @endif
                            <span class="min-w-0 flex-1 truncate text-left" data-ds-sidebar-label>{{ $item['label'] }}</span>
                            <x-ui.icon name="chevron-down" size="sm" class="ds-sidebar-submenu-chevron" data-ds-sidebar-label />
                        </button>
                        <div id="{{ $submenuId }}" @class(['ds-sidebar-submenu', 'hidden' => !$groupActive]) data-ds-sidebar-submenu>
                            @foreach ($item['children'] as $child)
                                <a
                                    href="{{ $child['href'] }}"
                                    @class(['ds-sidebar-submenu-link', 'is-active' => $child['active'] ?? false])
                                    @if($child['active'] ?? false) aria-current="page" @endif
                                    data-ds-sidebar-link
                                >
                                    <span class="ds-sidebar-submenu-dot" aria-hidden="true"></span>
                                    <span>{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $item['href'] }}"
                        aria-label="{{ $item['label'] }}"
                        title="{{ $item['label'] }}"
                        data-ds-sidebar-link
                        @class(['ds-sidebar-link', 'is-active' => $item['active'] ?? false])
                        @if($item['active'] ?? false) aria-current="page" @endif
                    >
                        @if (!empty($item['icon']))
                            <x-ui.icon :name="$item['icon']" />
                        @endif
                        <span data-ds-sidebar-label>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="ds-sidebar-footer">
            {{ $sidebarFooter ?? '' }}
        </div>
    </aside>

    <button type="button" class="ds-sidebar-overlay" data-ds-sidebar-overlay aria-label="{{ __('ui.close_menu') }}"></button>

    <div class="ds-app-main">
        <header class="ds-app-header">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" class="ui-icon-button md:hidden" data-ds-sidebar-mobile-toggle aria-controls="ds-app-sidebar" aria-expanded="false" aria-label="{{ __('ui.open_menu') }}">
                    <x-ui.icon name="menu-2" />
                </button>
                @if ($headerTitle)
                    <div class="min-w-0">
                        <strong class="block truncate text-sm text-[var(--ui-text)]">{{ $headerTitle }}</strong>
                        @if ($headerSubtitle)
                            <span class="block truncate text-xs text-[var(--ui-text-muted)]">{{ $headerSubtitle }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-1">
                {{ $headerActions ?? '' }}
            </div>
        </header>

        <main class="min-w-0">{{ $slot }}</main>
    </div>
</div>

{{ $modals ?? '' }}