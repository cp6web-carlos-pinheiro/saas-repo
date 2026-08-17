@extends('layouts.public')

@section('bodyClass', 'ui-shell min-h-screen')

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
        <x-slot:sidebarFooter>
            <form method="POST" action="{{ route('global-admin.logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" :full="true" size="lg" class="justify-start gap-2" data-admin-sidebar-logout>
                    <x-ui.icon name="logout" size="sm" />
                    <span data-ds-sidebar-label>{{ __('admin.logout') }}</span>
                </x-ui.button>
            </form>
        </x-slot:sidebarFooter>

        <div class="@yield('admin-content-container-class', 'mx-auto w-full max-w-7xl') p-5 md:p-8">
            @yield('admin-content')
        </div>
    </x-ui.app-shell>
@endsection