@extends('layouts.google')

@section('title', __('ui.dashboard').' | '.__('ui.app_name'))

@section('bodyClass', 'ind-page')

@section('content')
    <div class="ind-layout">
        <x-ui.sidebar id="sidebar" variant="industrial" aria-label="{{ __('ui.modules') }}">
            <x-slot:header>
                <strong>{{ __('ui.app_name') }}</strong>
                <span>{{ __('ui.modules') }}</span>
            </x-slot:header>

            <x-ui.menu variant="industrial" :aria-label="__('ui.modules')">
                <x-ui.menu-item variant="industrial" href="#" active>{{ __('ui.module_bom') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_eco') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_engineering_change') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_genealogy') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_identity') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_inventory') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_mes') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_mrp') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_observability') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_product') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_production') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_purchasing') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_routing') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_scheduling') }}</x-ui.menu-item>
                <x-ui.menu-item variant="industrial" href="#">{{ __('ui.module_tenant') }}</x-ui.menu-item>
            </x-ui.menu>

            <x-slot:footer>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="ind-logout-button" type="submit">{{ __('ui.logout') }}</button>
                </form>
            </x-slot:footer>
        </x-ui.sidebar>

        <div class="ind-main-area">
            <header class="ind-topbar">
                <div class="ind-topbar-left">
                    <button id="menuToggle" class="ind-icon-button" type="button" aria-label="{{ __('ui.open_menu') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <span class="ind-topbar-title">{{ __('ui.dashboard') }}</span>
                </div>

                <div class="ind-topbar-right">
                    <a class="ind-icon-button" href="{{ route('docs.index') }}" aria-label="{{ __('ui.open_documentation') }}" title="{{ __('ui.documentation') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 4.75h8.4a2.85 2.85 0 0 1 2.85 2.85v11.65H8.6a2.85 2.85 0 0 0-2.85 2.85V7.6A2.85 2.85 0 0 1 8.6 4.75h.15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.25 7.75h6.5M8.25 10.75h6.5M8.25 13.75h4.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </a>
                    <button id="settingsToggle" class="ind-icon-button" type="button" aria-label="{{ __('ui.settings') }}" aria-controls="settingsPanel" aria-expanded="false">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a1.2 1.2 0 0 1 0 1.7l-1 1a1.2 1.2 0 0 1-1.7 0l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a1.2 1.2 0 0 1-1.2 1.2h-1.4A1.2 1.2 0 0 1 11.4 20v-.2a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a1.2 1.2 0 0 1-1.7 0l-1-1a1.2 1.2 0 0 1 0-1.7l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H6A1.2 1.2 0 0 1 4.8 13v-1.4A1.2 1.2 0 0 1 6 10.4h.2a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a1.2 1.2 0 0 1 0-1.7l1-1a1.2 1.2 0 0 1 1.7 0l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4A1.2 1.2 0 0 1 12.6 2.8H14A1.2 1.2 0 0 1 15.2 4v.2a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a1.2 1.2 0 0 1 1.7 0l1 1a1.2 1.2 0 0 1 0 1.7l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6h.2A1.2 1.2 0 0 1 21.2 11.6V13a1.2 1.2 0 0 1-1.2 1.2h-.2a1 1 0 0 0-.9.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </header>

            <main class="ind-content">
                @if (session('status'))
                    <div class="ind-status-banner">{{ session('status') }}</div>
                @endif
                <h1 class="ind-welcome">{{ __('ui.welcome') }}</h1>
            </main>
        </div>
    </div>

    <div id="settingsOverlay" class="ind-settings-overlay" aria-hidden="true"></div>
    <aside id="settingsPanel" class="ind-settings-panel" aria-hidden="true" aria-label="{{ __('ui.settings_panel_title') }}">
        <div class="ind-settings-panel-header">
            <h2>{{ __('ui.settings_panel_title') }}</h2>
            <button id="settingsClose" class="ind-settings-close" type="button">{{ __('ui.close') }}</button>
        </div>

        <p>{{ __('ui.settings_panel_description') }}</p>

        @php($currentLocale = auth()->user()?->preferred_locale ?? app()->getLocale())
        <form method="POST" action="{{ route('preferences.language.update') }}" class="ind-settings-field">
            @csrf
            <label for="preferredLocale">{{ __('ui.language') }}</label>
            <select id="preferredLocale" name="preferred_locale" required>
                <option value="pt_BR" @selected($currentLocale === 'pt_BR')>{{ __('ui.portuguese') }}</option>
                <option value="en" @selected($currentLocale === 'en')>{{ __('ui.english') }}</option>
                <option value="es" @selected($currentLocale === 'es')>{{ __('ui.spanish') }}</option>
            </select>

            <div class="ind-settings-actions">
                <button class="ind-settings-save" type="submit">{{ __('ui.save') }}</button>
            </div>
        </form>
    </aside>

@endsection

@section('scripts')
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const settingsToggle = document.getElementById('settingsToggle');
        const settingsPanel = document.getElementById('settingsPanel');
        const settingsOverlay = document.getElementById('settingsOverlay');
        const settingsClose = document.getElementById('settingsClose');

        menuToggle?.addEventListener('click', function () {
            sidebar?.classList.toggle('is-open');
        });

        const setSettingsState = (isOpen) => {
            settingsPanel?.classList.toggle('is-open', isOpen);
            settingsOverlay?.classList.toggle('is-open', isOpen);
            settingsPanel?.setAttribute('aria-hidden', String(!isOpen));
            settingsOverlay?.setAttribute('aria-hidden', String(!isOpen));
            settingsToggle?.setAttribute('aria-expanded', String(isOpen));
        };

        settingsToggle?.addEventListener('click', function () {
            const isOpen = settingsPanel?.classList.contains('is-open') ?? false;
            setSettingsState(!isOpen);
        });

        settingsClose?.addEventListener('click', function () {
            setSettingsState(false);
        });

        settingsOverlay?.addEventListener('click', function () {
            setSettingsState(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setSettingsState(false);
            }
        });
    </script>
@endsection
