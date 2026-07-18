<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.dashboard') }} | {{ __('ui.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafd;
            --surface: #ffffff;
            --surface-muted: #f1f3f4;
            --text: #202124;
            --text-muted: #5f6368;
            --line: #dadce0;
            --primary: #1a73e8;
            --primary-soft: #e8f0fe;
            --danger: #d93025;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Roboto", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 100% 0%, #e8f0fe 0%, rgba(232, 240, 254, 0) 34%),
                radial-gradient(circle at 0% 100%, #fce8e6 0%, rgba(252, 232, 230, 0) 30%),
                var(--bg);
            min-height: 100vh;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .main-area {
            display: grid;
            grid-template-rows: auto 1fr;
            min-width: 0;
        }

        .topbar {
            height: 64px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 0.9rem;
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .icon-button {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 999px;
            background: transparent;
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .icon-button:hover {
            background: var(--surface-muted);
            color: var(--text);
        }

        .topbar-title {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--line);
            padding: 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            padding: 0.3rem 0.4rem 0.7rem;
            border-bottom: 1px solid var(--line);
        }

        .brand strong {
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--text);
        }

        .brand span {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .module-list {
            display: grid;
            gap: 0.25rem;
            padding-right: 0.2rem;
            overflow: auto;
        }

        .module-list a {
            text-decoration: none;
            color: var(--text);
            font-size: 0.9rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .module-list a:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .logout-area {
            margin-top: auto;
            padding-top: 0.6rem;
            border-top: 1px solid var(--line);
        }

        .logout-button {
            width: 100%;
            border: 1px solid #f6aea9;
            background: #fce8e6;
            color: var(--danger);
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.62rem 0.85rem;
            cursor: pointer;
            transition: filter 0.2s ease;
        }

        .logout-button:hover {
            filter: brightness(0.98);
        }

        .content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }

        .status-banner {
            width: min(620px, 100%);
            border: 1px solid #c7e8d3;
            background: #eaf8ef;
            color: #196a3f;
            border-radius: 12px;
            padding: 0.8rem 1rem;
            font-size: 0.92rem;
        }

        .welcome {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 500;
            color: var(--text);
        }

        .settings-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.24);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.22s ease;
            z-index: 24;
        }

        .settings-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .settings-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: min(360px, 100%);
            height: 100vh;
            background: var(--surface);
            border-left: 1px solid var(--line);
            box-shadow: -10px 0 30px rgba(15, 23, 42, 0.1);
            transform: translateX(100%);
            transition: transform 0.22s ease;
            z-index: 25;
            padding: 1.2rem;
            display: grid;
            grid-template-rows: auto auto 1fr;
            gap: 1rem;
        }

        .settings-panel.is-open {
            transform: translateX(0);
        }

        .settings-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
        }

        .settings-panel h2 {
            margin: 0;
            font-size: 1.05rem;
        }

        .settings-panel p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .settings-field {
            display: grid;
            gap: 0.5rem;
            align-content: start;
        }

        .settings-field label {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .settings-field select {
            width: 100%;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--text);
            border-radius: 12px;
            min-height: 42px;
            padding: 0 0.8rem;
            font-size: 0.92rem;
        }

        .settings-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.8rem;
        }

        .settings-save {
            border: none;
            border-radius: 999px;
            padding: 0.55rem 1rem;
            background: var(--primary);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
        }

        .settings-close {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 0.55rem 1rem;
            background: var(--surface);
            color: var(--text);
            font-size: 0.88rem;
            cursor: pointer;
        }

        @media (max-width: 920px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                top: 64px;
                left: 0;
                bottom: 0;
                width: 280px;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 15;
                box-shadow: 0 6px 16px rgba(60, 64, 67, 0.25);
            }

            .sidebar.is-open {
                transform: translateX(0);
            }

            .content {
                padding-top: 1.5rem;
            }

            .welcome {
                width: 100%;
                max-width: 480px;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside id="sidebar" class="sidebar" aria-label="{{ __('ui.modules') }}">
            <div class="brand">
                <strong>{{ __('ui.app_name') }}</strong>
                <span>{{ __('ui.modules') }}</span>
            </div>

            <nav class="module-list" aria-label="{{ __('ui.modules') }}">
                <a href="#">{{ __('ui.module_bom') }}</a>
                <a href="#">{{ __('ui.module_eco') }}</a>
                <a href="#">{{ __('ui.module_engineering_change') }}</a>
                <a href="#">{{ __('ui.module_genealogy') }}</a>
                <a href="#">{{ __('ui.module_identity') }}</a>
                <a href="#">{{ __('ui.module_inventory') }}</a>
                <a href="#">{{ __('ui.module_mes') }}</a>
                <a href="#">{{ __('ui.module_mrp') }}</a>
                <a href="#">{{ __('ui.module_observability') }}</a>
                <a href="#">{{ __('ui.module_product') }}</a>
                <a href="#">{{ __('ui.module_production') }}</a>
                <a href="#">{{ __('ui.module_purchasing') }}</a>
                <a href="#">{{ __('ui.module_routing') }}</a>
                <a href="#">{{ __('ui.module_scheduling') }}</a>
                <a href="#">{{ __('ui.module_tenant') }}</a>
            </nav>

            <div class="logout-area">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">{{ __('ui.logout') }}</button>
                </form>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button id="menuToggle" class="icon-button" type="button" aria-label="{{ __('ui.open_menu') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <span class="topbar-title">{{ __('ui.dashboard') }}</span>
                </div>

                <div class="topbar-right">
                    <a class="icon-button" href="{{ route('docs.index') }}" aria-label="{{ __('ui.open_documentation') }}" title="{{ __('ui.documentation') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 4.75h8.4a2.85 2.85 0 0 1 2.85 2.85v11.65H8.6a2.85 2.85 0 0 0-2.85 2.85V7.6A2.85 2.85 0 0 1 8.6 4.75h.15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.25 7.75h6.5M8.25 10.75h6.5M8.25 13.75h4.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </a>
                    <button id="settingsToggle" class="icon-button" type="button" aria-label="{{ __('ui.settings') }}" aria-controls="settingsPanel" aria-expanded="false">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a1.2 1.2 0 0 1 0 1.7l-1 1a1.2 1.2 0 0 1-1.7 0l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a1.2 1.2 0 0 1-1.2 1.2h-1.4A1.2 1.2 0 0 1 11.4 20v-.2a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a1.2 1.2 0 0 1-1.7 0l-1-1a1.2 1.2 0 0 1 0-1.7l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H6A1.2 1.2 0 0 1 4.8 13v-1.4A1.2 1.2 0 0 1 6 10.4h.2a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a1.2 1.2 0 0 1 0-1.7l1-1a1.2 1.2 0 0 1 1.7 0l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4A1.2 1.2 0 0 1 12.6 2.8H14A1.2 1.2 0 0 1 15.2 4v.2a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a1.2 1.2 0 0 1 1.7 0l1 1a1.2 1.2 0 0 1 0 1.7l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6h.2A1.2 1.2 0 0 1 21.2 11.6V13a1.2 1.2 0 0 1-1.2 1.2h-.2a1 1 0 0 0-.9.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </header>

            <main class="content">
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif
                <h1 class="welcome">{{ __('ui.welcome') }}</h1>
            </main>
        </div>
    </div>

    <div id="settingsOverlay" class="settings-overlay" aria-hidden="true"></div>
    <aside id="settingsPanel" class="settings-panel" aria-hidden="true" aria-label="{{ __('ui.settings_panel_title') }}">
        <div class="settings-panel-header">
            <h2>{{ __('ui.settings_panel_title') }}</h2>
            <button id="settingsClose" class="settings-close" type="button">{{ __('ui.close') }}</button>
        </div>

        <p>{{ __('ui.settings_panel_description') }}</p>

        @php($currentLocale = auth()->user()?->preferred_locale ?? app()->getLocale())
        <form method="POST" action="{{ route('preferences.language.update') }}" class="settings-field">
            @csrf
            <label for="preferredLocale">{{ __('ui.language') }}</label>
            <select id="preferredLocale" name="preferred_locale" required>
                <option value="pt_BR" @selected($currentLocale === 'pt_BR')>{{ __('ui.portuguese') }}</option>
                <option value="en" @selected($currentLocale === 'en')>{{ __('ui.english') }}</option>
                <option value="es" @selected($currentLocale === 'es')>{{ __('ui.spanish') }}</option>
            </select>

            <div class="settings-actions">
                <button class="settings-save" type="submit">{{ __('ui.save') }}</button>
            </div>
        </form>
    </aside>

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
</body>
</html>
