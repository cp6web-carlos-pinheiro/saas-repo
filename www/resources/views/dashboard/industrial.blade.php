@extends('layouts.google')

@section('title', __('ui.dashboard').' | '.__('ui.app_name'))

@section('bodyClass', 'ind-page')

@section('content')
    <div class="ind-layout">
        <x-ui.sidebar id="sidebar" variant="industrial" aria-label="{{ __('ui.modules') }}">
            <x-slot:header>
                <strong>{{ __('ui.app_name') }}</strong>
            </x-slot:header>

            <x-ui.menu variant="industrial" :aria-label="__('ui.modules')">
                @php
                    $moduleLabels = [
                        'bom' => __('ui.module_bom'),
                        'eco' => __('ui.module_eco'),
                        'engineering-change' => __('ui.module_engineering_change'),
                        'genealogy' => __('ui.module_genealogy'),
                        'identity' => __('ui.module_identity'),
                        'inventory' => __('ui.module_inventory'),
                        'mes' => __('ui.module_mes'),
                        'mrp' => __('ui.module_mrp'),
                        'observability' => __('ui.module_observability'),
                        'product' => __('ui.module_product'),
                        'production' => __('ui.module_production'),
                        'purchasing' => __('ui.module_purchasing'),
                        'routing' => __('ui.module_routing'),
                        'scheduling' => __('ui.module_scheduling'),
                        'tenant' => __('ui.module_tenant'),
                    ];
                @endphp

                @forelse ($availableModules as $module)
                    @if (isset($moduleLabels[$module]))
                        <x-ui.menu-item variant="industrial" href="#" :active="$loop->first">{{ $moduleLabels[$module] }}</x-ui.menu-item>
                    @endif
                @empty
                    <x-ui.menu-item variant="industrial" href="#" class="text-muted">{{ __('ui.modules') }}</x-ui.menu-item>
                @endforelse
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
                    <button id="menuToggle" class="ind-icon-button" type="button" aria-label="{{ __('ui.open_menu') }}" aria-controls="sidebar" aria-expanded="false" aria-pressed="false">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <span class="ind-topbar-title">{{ __('ui.dashboard') }}</span>
                </div>

                <div class="ind-topbar-right">
                    <button id="settingsToggle" class="ind-icon-button" type="button" aria-label="{{ __('ui.settings') }}" aria-controls="settingsPanel" aria-expanded="false" aria-pressed="false">
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
        @php
            $subscriptionPlanName = $subscriptionPlan['label'] ?? ($subscription?->plan_code ?? __('ui.no_subscription'));
            $subscriptionAmount = 'R$ '.number_format((($subscriptionPlan['amount_cents'] ?? 0) / 100), 2, ',', '.');
            $subscriptionPaymentMethod = $subscriptionPlan['payment_method'] ?? '-';
            $subscriptionDueDate = $subscription?->ends_at?->format('d/m/Y') ?? __('ui.no_due_date');
        @endphp

        <div class="ind-settings-panel-header">
            <h2>{{ __('ui.settings_panel_title') }}</h2>
            <button id="settingsClose" class="ind-settings-close" type="button">{{ __('ui.close') }}</button>
        </div>

        @php($currentLocale = auth()->user()?->preferred_locale ?? app()->getLocale())
        <form method="POST" action="{{ route('preferences.language.update') }}" class="ind-settings-section ind-settings-field">
            @csrf
            <label for="preferredLocale">{{ __('ui.language') }}</label>
            <div class="ind-settings-language-row flex items-start justify-between gap-3">
                <x-ui.select id="preferredLocale" name="preferred_locale" required>
                    <option value="pt_BR" @selected($currentLocale === 'pt_BR')>{{ __('ui.portuguese') }}</option>
                    <option value="en" @selected($currentLocale === 'en')>{{ __('ui.english') }}</option>
                    <option value="es" @selected($currentLocale === 'es')>{{ __('ui.spanish') }}</option>
                </x-ui.select>
                <div class="ind-settings-actions">
                    <button class="ind-settings-save" type="submit">{{ __('ui.save') }}</button>
                </div>
            </div>
        </form>

        <a href="{{ route('docs.index') }}" class="ind-settings-nav-link" aria-label="{{ __('ui.open_documentation') }}">
            <span>{{ __('ui.documentation') }}</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 4.75h8.4a2.85 2.85 0 0 1 2.85 2.85v11.65H8.6a2.85 2.85 0 0 0-2.85 2.85V7.6A2.85 2.85 0 0 1 8.6 4.75h.15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.25 7.75h6.5M8.25 10.75h6.5M8.25 13.75h4.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </a>

        @if ($canManageAccesses)
            <a href="{{ route('company-access.users.index') }}" class="ind-settings-nav-link" aria-label="{{ __('ui.manage_accesses') }}">
                <span>{{ __('ui.manage_accesses') }}</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 7.5A3.5 3.5 0 1 1 10.5 11 3.5 3.5 0 0 1 7 7.5Zm8 1A2.5 2.5 0 1 1 17.5 11 2.5 2.5 0 0 1 15 8.5ZM4.75 18a4.25 4.25 0 0 1 8.5 0v.25H4.75V18Zm9.5.25v-.25a4.86 4.86 0 0 0-1.09-3.08A3.75 3.75 0 0 1 19.25 18v.25h-5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        @endif

        <section class="ind-settings-section ind-subscription-card">
            <div class="ind-subscription-heading">
                <div>
                    <h3>{{ __('ui.subscription_section_title') }}</h3>
                    <p>{{ __('ui.subscription_section_description') }}</p>
                </div>
            </div>

            <dl class="ind-subscription-details">
                <div class="ind-subscription-detail">
                    <dt>{{ __('ui.current_plan') }}</dt>
                    <dd>{{ $subscriptionPlanName }}</dd>
                </div>
                <div class="ind-subscription-detail">
                    <dt>{{ __('global_plan.amount_short') }}</dt>
                    <dd>{{ $subscriptionAmount }}</dd>
                </div>
                <div class="ind-subscription-detail">
                    <dt>{{ __('ui.payment_method') }}</dt>
                    <dd>{{ $subscriptionPaymentMethod }}</dd>
                </div>
                <div class="ind-subscription-detail">
                    <dt>{{ __('ui.due_date') }}</dt>
                    <dd>{{ $subscriptionDueDate }}</dd>
                </div>
            </dl>

            <a href="{{ route('billing.subscription.show') }}" class="ind-subscription-action">{{ __('ui.renew_or_change_plan') }}</a>
        </section>
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

        const setMenuState = (isOpen) => {
            sidebar?.classList.toggle('is-open', isOpen);
            menuToggle?.setAttribute('aria-expanded', String(isOpen));
            menuToggle?.setAttribute('aria-pressed', String(isOpen));
        };

        menuToggle?.addEventListener('click', function () {
            const isOpen = sidebar?.classList.contains('is-open') ?? false;
            setMenuState(!isOpen);
        });

        const setSettingsState = (isOpen) => {
            settingsPanel?.classList.toggle('is-open', isOpen);
            settingsOverlay?.classList.toggle('is-open', isOpen);
            settingsPanel?.setAttribute('aria-hidden', String(!isOpen));
            settingsOverlay?.setAttribute('aria-hidden', String(!isOpen));
            settingsToggle?.setAttribute('aria-expanded', String(isOpen));
            settingsToggle?.setAttribute('aria-pressed', String(isOpen));
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
