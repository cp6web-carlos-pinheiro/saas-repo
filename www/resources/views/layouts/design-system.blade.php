@extends('layouts.public')

@section('bodyClass', 'ds-shell min-h-screen')

@section('head-preload')
    <script>
        (() => {
            const storageKey = 'beyond-mrp.theme';
            let preference = 'system';

            try {
                const stored = window.localStorage.getItem(storageKey);
                if (stored === 'light' || stored === 'dark' || stored === 'system') preference = stored;
            } catch (_) {}

            const resolved = preference === 'system'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : preference;

            document.documentElement.dataset.theme = resolved;
            document.documentElement.dataset.themePreference = preference;
            document.documentElement.style.colorScheme = resolved;
        })();
    </script>
@endsection

@section('content')
    @php
        $sidebarItems = [
            ['label' => __('ui.dashboard'), 'icon' => 'layout-dashboard', 'href' => '#fundamentos', 'active' => true],
            ['label' => __('ui.domain_engineering'), 'icon' => 'ruler-measure', 'children' => [
                ['label' => 'Produtos e modelos', 'href' => '#formularios'],
                ['label' => 'Estruturas e BOM', 'href' => '#componentes'],
                ['label' => 'Roteiros de fabricação', 'href' => '#dados'],
            ]],
            ['label' => __('ui.domain_planning'), 'icon' => 'calendar', 'children' => [
                ['label' => 'Planejamento MRP', 'href' => '#acoes'],
                ['label' => 'Capacidade e progresso', 'href' => '#indicadores'],
                ['label' => 'Programação de ordens', 'href' => '#dados'],
            ]],
            ['label' => __('ui.domain_shop_floor'), 'icon' => 'building-factory', 'children' => [
                ['label' => 'Ordens em execução', 'href' => '#dados'],
                ['label' => 'Apontamentos', 'href' => '#componentes'],
                ['label' => 'Qualidade e alertas', 'href' => '#feedback'],
            ]],
            ['label' => __('ui.domain_analysis'), 'icon' => 'chart-bar', 'href' => '#feedback'],
            ['label' => __('ui.module_inventory'), 'icon' => 'package', 'children' => [
                ['label' => 'Saldos e disponibilidade', 'href' => '#dados'],
                ['label' => 'Movimentações', 'href' => '#navegacao'],
                ['label' => 'Reservas de materiais', 'href' => '#indicadores'],
            ]],
            ['label' => __('ui.module_purchasing'), 'icon' => 'shopping-cart', 'children' => [
                ['label' => 'Fornecedores', 'href' => '#formularios'],
                ['label' => 'Pedidos de compra', 'href' => '#dados'],
                ['label' => 'Recebimentos', 'href' => '#modais'],
            ]],
            ['label' => __('ui.module_sales'), 'icon' => 'receipt', 'children' => [
                ['label' => 'Pedidos de venda', 'href' => '#dados'],
                ['label' => 'Clientes', 'href' => '#formularios'],
                ['label' => 'Documentos', 'href' => '#componentes'],
            ]],
            ['label' => __('ui.domain_administration'), 'icon' => 'users', 'href' => route('global-admin.docs.index')],
        ];
    @endphp

    <div class="ds-app-shell" data-ds-sidebar-shell>
        <aside id="ds-app-sidebar" class="ds-app-sidebar" data-ds-sidebar aria-label="{{ __('ui.modules') }}">
            <div class="ds-sidebar-brand">
                <a href="{{ route('global-admin.home') }}" class="ds-sidebar-brand-link" aria-label="{{ __('ui.app_name') }}" title="{{ __('ui.app_name') }}">
                    <span class="ds-sidebar-brand-mark" aria-hidden="true">B</span>
                    <span class="min-w-0" data-ds-sidebar-label>
                        <strong class="block truncate text-sm text-[var(--ui-text)]">{{ __('ui.app_name') }}</strong>
                        <small class="block truncate text-xs text-[var(--ui-text-muted)]">Layout System</small>
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

            <nav class="ds-sidebar-nav" aria-label="{{ __('ui.modules') }}">
                <p class="ds-sidebar-eyebrow" data-ds-sidebar-label>{{ __('ui.modules') }}</p>
                @foreach ($sidebarItems as $item)
                    @if(!empty($item['children']))
                        @php($submenuId = 'ds-submenu-'.$loop->index)
                        <div class="ds-sidebar-group" data-ds-sidebar-group>
                            <button
                                type="button"
                                class="ds-sidebar-link w-full"
                                aria-expanded="false"
                                aria-controls="{{ $submenuId }}"
                                aria-label="{{ $item['label'] }}"
                                title="{{ $item['label'] }}"
                                data-ds-sidebar-submenu-toggle
                            >
                                <x-ui.icon :name="$item['icon']" />
                                <span class="min-w-0 flex-1 truncate text-left" data-ds-sidebar-label>{{ $item['label'] }}</span>
                                <x-ui.icon name="chevron-down" size="sm" class="ds-sidebar-submenu-chevron" data-ds-sidebar-label />
                            </button>
                            <div id="{{ $submenuId }}" class="ds-sidebar-submenu hidden" data-ds-sidebar-submenu>
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['href'] }}" class="ds-sidebar-submenu-link" data-ds-sidebar-link>
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
                            <x-ui.icon :name="$item['icon']" />
                            <span data-ds-sidebar-label>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="ds-sidebar-footer">
                <form method="POST" action="{{ route('global-admin.logout') }}">
                    @csrf
                    <button type="submit" class="ds-sidebar-link w-full" aria-label="{{ __('ui.logout') }}" title="{{ __('ui.logout') }}">
                        <x-ui.icon name="logout" />
                        <span data-ds-sidebar-label>{{ __('ui.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <button type="button" class="ds-sidebar-overlay" data-ds-sidebar-overlay aria-label="Fechar menu"></button>

        <div class="ds-app-main">
            <header class="ds-app-header">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="ui-icon-button md:hidden" data-ds-sidebar-mobile-toggle aria-controls="ds-app-sidebar" aria-expanded="false" aria-label="{{ __('ui.open_menu') }}">
                        <x-ui.icon name="menu-2" />
                    </button>
                    <div class="min-w-0">
                        <strong class="block truncate text-sm text-[var(--ui-text)]">Layout System</strong>
                        <span class="block truncate text-xs text-[var(--ui-text-muted)]">Modelo de página do produto</span>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" class="ui-icon-button" data-ui-modal-open="ds-tutorial-panel" aria-label="{{ __('ui.tutorial_help') }}" title="{{ __('ui.tutorial_help') }}">
                        <x-ui.icon name="help-circle" />
                    </button>
                    <button type="button" class="ui-icon-button" data-ui-modal-open="ds-preferences-panel" aria-label="{{ __('ui.settings_panel_title') }}" title="{{ __('ui.settings_panel_title') }}">
                        <x-ui.icon name="settings" />
                    </button>
                </div>
            </header>

            <main class="min-w-0">@yield('design-system-content')</main>
        </div>
    </div>

    <x-ui.modal id="ds-tutorial-panel" size="sheet" title="{{ __('ui.tutorial_panel_title') }}" description="Orientações para a página atual.">
        <div class="space-y-5">
            <x-ui.alert variant="info" title="Modelo de página">
                Este catálogo demonstra o shell aprovado: menu de módulos, header com ações globais e conteúdo construído com componentes compartilhados.
            </x-ui.alert>
            <div>
                <h3 class="font-semibold text-[var(--ui-text)]">Nesta página</h3>
                <nav class="mt-3 grid gap-2" aria-label="Atalhos do tutorial">
                    <a href="#acoes" class="ui-dropdown-item" data-ui-modal-close>Botões e estados</a>
                    <a href="#formularios" class="ui-dropdown-item" data-ui-modal-close>Formulários</a>
                    <a href="#navegacao" class="ui-dropdown-item" data-ui-modal-close>Navegação contextual</a>
                    <a href="#modais" class="ui-dropdown-item" data-ui-modal-close>Modais e sheets</a>
                </nav>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="outline" data-ui-modal-close>{{ __('ui.close') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.modal id="ds-preferences-panel" size="sheet" title="{{ __('ui.settings_panel_title') }}" description="Ajuste a experiência desta interface.">
        <section>
            <h3 class="font-semibold text-[var(--ui-text)]">Tema</h3>
            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">A preferência é salva neste navegador.</p>
            <div class="mt-4 grid grid-cols-3 gap-3" role="group" aria-label="Tema da interface">
                <button type="button" class="ds-theme-choice" data-theme-option="light" aria-label="Usar tema claro">
                    <x-ui.icon name="sun" />
                    <span>Claro</span>
                </button>
                <button type="button" class="ds-theme-choice" data-theme-option="system" aria-label="Usar tema do sistema">
                    <x-ui.icon name="device-desktop" />
                    <span>Sistema</span>
                </button>
                <button type="button" class="ds-theme-choice" data-theme-option="dark" aria-label="Usar tema escuro">
                    <x-ui.icon name="moon" />
                    <span>Escuro</span>
                </button>
            </div>
        </section>
        <x-slot:footer>
            <x-ui.button variant="primary" data-ui-modal-close>{{ __('ui.close') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <div class="ds-demo-toast hidden" data-ui-demo-toast role="status" aria-live="polite" aria-atomic="true">
        <x-ui.icon name="circle-check" />
        <span data-ui-demo-toast-message>Ação executada.</span>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const storageKey = 'beyond-mrp.theme';
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const options = [...document.querySelectorAll('[data-theme-option]')];

            const applyTheme = (preference, persist = true) => {
                const resolved = preference === 'system' ? (media.matches ? 'dark' : 'light') : preference;
                document.documentElement.dataset.theme = resolved;
                document.documentElement.dataset.themePreference = preference;
                document.documentElement.style.colorScheme = resolved;

                for (const option of options) {
                    const active = option.dataset.themeOption === preference;
                    option.setAttribute('aria-pressed', active ? 'true' : 'false');
                }

                if (persist) {
                    try { window.localStorage.setItem(storageKey, preference); } catch (_) {}
                }
            };

            for (const option of options) {
                option.addEventListener('click', () => applyTheme(option.dataset.themeOption ?? 'system'));
            }

            media.addEventListener('change', () => {
                if (document.documentElement.dataset.themePreference === 'system') applyTheme('system', false);
            });

            applyTheme(document.documentElement.dataset.themePreference ?? 'system', false);
        })();
    </script>
@endsection
