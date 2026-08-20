@extends('layouts.public')

@section('bodyClass', 'ui-shell min-h-screen')
@section('themeSwitcherHandled', 'true')

@section('head-preload')
    @include('partials.theme-preload')
@endsection

@section('content')
    @php
        // Navegação do Global Admin centralizada aqui: hoje só este layout consome estes
        // itens, então não há duplicação a resolver. Caso outra área precise do mesmo
        // conjunto no futuro, extrair para um helper compartilhado (ver doc/11-design-system.md).
        //
        // O item ativo usa request()->routeIs('global-admin.{modulo}.*') em vez de comparar a
        // URL atual, então listagem, criação, detalhe e edição do módulo mantêm o destaque
        // correto no menu.
        $globalAdminNavigation = [
            [
                'label' => __('global_admin.home'),
                'href' => route('global-admin.home'),
                'icon' => 'layout-dashboard',
                'active' => request()->routeIs('global-admin.home'),
            ],
            [
                'label' => __('global_admin.modules.customers'),
                'href' => route('global-admin.customers.index'),
                'icon' => 'users',
                'active' => request()->routeIs('global-admin.customers.*'),
            ],
            [
                'label' => __('global_admin.modules.companies'),
                'href' => route('global-admin.companies.index'),
                'icon' => 'building-factory',
                'active' => request()->routeIs('global-admin.companies.*'),
            ],
            [
                'label' => __('global_admin.modules.plans'),
                'href' => route('global-admin.plans.index'),
                'icon' => 'certificate',
                'active' => request()->routeIs('global-admin.plans.*'),
            ],
            [
                'label' => __('global_admin.modules.documentation'),
                'href' => route('global-admin.docs.index'),
                'icon' => 'info-circle',
                'active' => request()->routeIs('global-admin.docs.*'),
            ],
            [
                'label' => __('global_admin.modules.tutorials'),
                'href' => route('global-admin.tutorials.index'),
                'icon' => 'help-circle',
                'active' => request()->routeIs('global-admin.tutorials.*'),
            ],
            [
                'label' => __('global_admin.modules.administrators'),
                'href' => route('global-admin.administrators.index'),
                'icon' => 'shield-check',
                'active' => request()->routeIs('global-admin.administrators.*'),
            ],
        ];
    @endphp

    <x-ui.app-shell
        :navigation="$globalAdminNavigation"
        navigation-label="{{ __('global_admin.title') }}"
        brand-name="{{ __('ui.app_name') }}"
        brand-href="{{ route('global-admin.home') }}"
        brand-subtitle="{{ __('global_admin.title') }}"
        header-title="{{ __('global_admin.title') }}"
    >
        <x-slot:headerActions>
            <x-ui.icon-button type="button" icon="help-circle" variant="ghost" :label="__('ui.tutorial_help')" data-ui-modal-open="globalAdminHelpPanel" aria-controls="globalAdminHelpPanel" />
            <x-ui.icon-button type="button" icon="settings" variant="ghost" :label="__('ui.settings')" data-ui-modal-open="globalAdminSettingsPanel" aria-controls="globalAdminSettingsPanel" />
        </x-slot:headerActions>

        <x-slot:sidebarFooter>
            {{-- Acesso ao Layout System: fica no rodapé do menu, separado por divisor, para
                 permanecer identificável sem se misturar aos módulos operacionais acima. --}}
            <a
                href="{{ route('global-admin.design-system') }}"
                target="_blank"
                rel="noopener"
                class="ds-sidebar-link"
                aria-label="{{ __('global_admin.layout_system') }}"
                title="{{ __('global_admin.layout_system') }}"
            >
                <x-ui.icon name="palette" />
                <span data-ds-sidebar-label>{{ __('global_admin.layout_system') }}</span>
            </a>

            <div class="my-2 border-t border-[var(--ui-border)]" data-ds-sidebar-label aria-hidden="true"></div>

            <form method="POST" action="{{ route('global-admin.logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" :full="true" size="lg" class="justify-start gap-2" data-admin-sidebar-logout>
                    <x-ui.icon name="logout" size="sm" />
                    <span data-ds-sidebar-label>{{ __('admin.logout') }}</span>
                </x-ui.button>
            </form>
        </x-slot:sidebarFooter>

        <div class="@yield('admin-content-container-class', 'mx-auto w-full max-w-7xl') p-5 md:p-8">
            @yield('admin-content')
        </div>
    </x-ui.app-shell>

    <x-ui.modal id="globalAdminHelpPanel" size="sheet" :title="__('ui.tutorial_panel_title')" :close-label="__('ui.close')">
        <div class="space-y-4">
            <x-ui.alert variant="info">{{ __('global_admin.eyebrow') }}</x-ui.alert>
            <nav class="grid gap-2" aria-label="{{ __('ui.tutorial_panel_title') }}">
                <a href="{{ route('global-admin.tutorials.index') }}" class="ui-dropdown-item" data-ui-modal-close>
                    <x-ui.icon name="help-circle" size="sm" />
                    <span>{{ __('global_admin.modules.tutorials') }}</span>
                </a>
                <a href="{{ route('global-admin.docs.index') }}" class="ui-dropdown-item" data-ui-modal-close>
                    <x-ui.icon name="info-circle" size="sm" />
                    <span>{{ __('global_admin.modules.documentation') }}</span>
                </a>
                <a href="{{ route('global-admin.design-system') }}" class="ui-dropdown-item" data-ui-modal-close>
                    <x-ui.icon name="palette" size="sm" />
                    <span>{{ __('global_admin.layout_system') }}</span>
                </a>
            </nav>
        </div>
        <x-slot:footer>
            <x-ui.button variant="primary" data-ui-modal-close>{{ __('ui.close') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.modal id="globalAdminSettingsPanel" size="sheet" :title="__('ui.settings_panel_title')" :close-label="__('ui.close')">
        <x-ui.theme-picker />
        <x-slot:footer>
            <x-ui.button variant="primary" data-ui-modal-close>{{ __('ui.close') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endsection
