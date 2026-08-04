@extends('layouts.public')

@section('bodyClass', 'ind-page')

@section('content')
    @php
        $moduleLabels = [
            'products' => __('ui.module_products'),
            'inventory' => __('ui.module_inventory'),
            'purchasing' => __('ui.module_purchasing'),
            'sales' => __('ui.module_sales'),
            'production_mrp' => __('ui.module_production_mrp'),
            'reports' => __('ui.module_reports'),
            'audit' => __('ui.module_audit'),
            'users' => __('ui.module_users'),
            'suppliers' => __('ui.module_suppliers'),
            'customers' => __('ui.module_customers'),
        ];

        $moduleIcons = [
            'production_mrp' => 'production_mrp',
            'inventory' => 'inventory',
            'purchasing' => 'purchasing',
            'sales' => 'sales',
            'products' => 'products',
            'suppliers' => 'suppliers',
            'customers' => 'customers',
            'reports' => 'reports',
            'audit' => 'audit',
            'users' => 'users',
        ];

        $moduleLinks = [
            'suppliers' => route('purchasing.suppliers.index'),
            'customers' => route('customers.index'),
        ];

        $moduleSubitems = [
            'products' => [
                ['label' => __('ui.product_register'), 'href' => route('products.index'), 'active' => request()->routeIs('products.index') || request()->routeIs('products.create') || request()->routeIs('products.show') || request()->routeIs('products.edit')],
                ['label' => __('ui.product_versions'), 'href' => route('products.versions'), 'active' => request()->routeIs('products.versions') || request()->routeIs('products.versions.*')],
            ],
            'sales' => [
                ['label' => __('ui.sales_register'), 'href' => route('sales.index'), 'active' => request()->routeIs('sales.*')],
                ['label' => __('ui.sales_customers'), 'href' => route('customers.index'), 'active' => request()->routeIs('customers.*')],
            ],
            'production_mrp' => [
                ['label' => __('ui.bom_structures'), 'href' => route('bom.structures.index'), 'active' => request()->routeIs('bom.structures.*')],
                ['label' => __('ui.bom_revisions'), 'href' => route('bom.material-lists.index'), 'active' => request()->routeIs('bom.material-lists.*')],
                ['label' => __('ui.production_orders').' ('.__('ui.coming_soon').')', 'href' => null, 'active' => false],
                ['label' => __('ui.production_postings').' ('.__('ui.coming_soon').')', 'href' => null, 'active' => false],
                ['label' => __('ui.module_routing').' ('.__('ui.coming_soon').')', 'href' => null, 'active' => false],
                ['label' => __('ui.work_centers').' ('.__('ui.coming_soon').')', 'href' => null, 'active' => false],
                ['label' => __('ui.production_calendar').' ('.__('ui.coming_soon').')', 'href' => null, 'active' => false],
                ['label' => __('ui.module_scheduling').' ('.__('ui.coming_soon').')', 'href' => null, 'active' => false],
            ],
            'purchasing' => [
                ['label' => __('ui.purchasing_suppliers'), 'href' => route('purchasing.suppliers.index'), 'active' => request()->routeIs('purchasing.suppliers.*')],
                ['label' => __('ui.purchasing_requisition'), 'href' => route('purchasing.requisitions.index'), 'active' => request()->routeIs('purchasing.requisitions.*')],
                ['label' => __('ui.purchasing_quotation'), 'href' => route('purchasing.quotations.index'), 'active' => request()->routeIs('purchasing.quotations.*')],
                ['label' => __('ui.purchasing_order'), 'href' => route('purchasing.orders.index'), 'active' => request()->routeIs('purchasing.orders.*')],
                ['label' => __('ui.purchasing_receipt'), 'href' => route('purchasing.receipts.index'), 'active' => request()->routeIs('purchasing.receipts.*')],
            ],
            'inventory' => [
                ['label' => __('ui.inventory_plants'), 'href' => route('inventory.plants.index'), 'active' => request()->routeIs('inventory.plants.*')],
                ['label' => __('ui.inventory_warehouses'), 'href' => route('inventory.warehouses.index'), 'active' => request()->routeIs('inventory.warehouses.*')],
                ['label' => __('ui.admin_units'), 'href' => route('admin-data.index', ['domain' => 'units']), 'active' => request()->routeIs('admin-data.*') && request()->route('domain') === 'units'],
                ['label' => __('ui.admin_categories'), 'href' => route('admin-data.index', ['domain' => 'categories']), 'active' => request()->routeIs('admin-data.*') && request()->route('domain') === 'categories'],
                ['label' => __('ui.admin_brands'), 'href' => route('admin-data.index', ['domain' => 'brands']), 'active' => request()->routeIs('admin-data.*') && request()->route('domain') === 'brands'],
                ['label' => __('ui.inventory_movements'), 'href' => null, 'active' => false],
                ['label' => __('ui.inventory_count'), 'href' => null, 'active' => false],
            ],
            'users' => [
                ['label' => __('ui.manage_accesses'), 'href' => route('company-access.users.index'), 'active' => request()->routeIs('company-access.users.*')],
                ['label' => __('ui.rbac_roles'), 'href' => route('company-access.rbac.roles.index'), 'active' => request()->routeIs('company-access.rbac.roles.*')],
            ],
        ];

        $modulePriority = [
            'production_mrp',
            'inventory',
            'purchasing',
            'sales',
            'products',
            'suppliers',
            'customers',
            'reports',
            'audit',
            'users',
        ];

        $user = auth()->user();
        $company = null;
        $availableModules = [];
        $canManageAccesses = false;
        $organization = null;
        $subscription = null;
        $subscriptionPlan = null;

        if ($user instanceof \App\Modules\Identity\Infrastructure\Persistence\Models\User && (int) ($user->current_company_id ?? 0) > 0) {
            $company = \App\Modules\Tenant\Infrastructure\Persistence\Models\Company::query()->find((int) $user->current_company_id);

            if ($company !== null) {
                $organization = \App\Models\SaaS\Organization::query()->where('company_id', $company->id)->first();
                $subscription = $organization
                    ? \App\Models\SaaS\Subscription::query()->where('organization_id', $organization->id)->latest('id')->first()
                    : null;
                $subscriptionPlan = $subscription
                    ? app(\App\Services\SaaS\AccountOnboardingService::class)->planForCode($subscription->plan_code)
                    : null;
                $availableModules = app(\App\Services\SaaS\CompanyUserAccessService::class)->accessibleModules($user, $company);
                $availableModules = array_values(array_diff($availableModules, ['suppliers', 'customers']));
                $canManageAccesses = app(\App\Services\SaaS\CompanyUserAccessService::class)->canManageCompanyAccess($user, $company);

            }
        }

        usort($availableModules, static function (string $left, string $right) use ($modulePriority): int {
            $leftOrder = array_search($left, $modulePriority, true);
            $rightOrder = array_search($right, $modulePriority, true);

            $leftRank = $leftOrder === false ? PHP_INT_MAX : $leftOrder;
            $rightRank = $rightOrder === false ? PHP_INT_MAX : $rightOrder;

            if ($leftRank === $rightRank) {
                return strcmp($left, $right);
            }

            return $leftRank <=> $rightRank;
        });

        $activeCompanyName = $company?->name ?? __('ui.app_name');
    @endphp

    <div class="ind-layout" data-client-sidebar-shell>
        <x-ui.sidebar id="sidebar" variant="industrial" aria-label="{{ __('ui.modules') }}" data-client-sidebar>
            <x-slot:header>
                <div class="ind-brand-title" data-client-sidebar-content>
                    <strong>{{ __('ui.app_name') }}</strong>
                    <span class="mt-1 block text-xs font-medium text-[#5f6368]">{{ $activeCompanyName }}</span>
                </div>
                <button
                    type="button"
                    class="ind-sidebar-toggle"
                    data-client-sidebar-toggle
                    aria-expanded="true"
                    aria-label="{{ __('ui.collapse_sidebar') }}"
                    title="{{ __('ui.toggle_sidebar') }}"
                    data-collapse-label="{{ __('ui.collapse_sidebar') }}"
                    data-expand-label="{{ __('ui.expand_sidebar') }}"
                >
                    <span aria-hidden="true" data-client-sidebar-toggle-icon>←</span>
                </button>
            </x-slot:header>

            <x-ui.menu variant="industrial" :aria-label="__('ui.modules')" data-client-sidebar-content>
                @forelse ($availableModules as $module)
                    @if (isset($moduleLabels[$module]))
                        @php
                            $subitems = $moduleSubitems[$module] ?? [];
                            $hasActiveSubitem = collect($subitems)->contains(static fn (array $item): bool => (bool) ($item['active'] ?? false));
                            $moduleActive = $hasActiveSubitem || (empty($subitems) && $loop->first);
                        @endphp

                        <div @class(['ind-module-group', 'is-open' => $hasActiveSubitem]) data-module-group>
                            @if ($subitems !== [])
                                <button
                                    type="button"
                                    @class(['ind-module-parent', 'ind-module-parent-toggle', 'is-active' => $moduleActive])
                                    data-module-toggle
                                    aria-expanded="{{ $hasActiveSubitem ? 'true' : 'false' }}"
                                >
                                    <span class="inline-flex items-center gap-2">
                                        <span class="inline-flex h-4 w-4 items-center justify-center text-[#5f6368]" aria-hidden="true">
                                            @switch($moduleIcons[$module] ?? null)
                                                @case('production_mrp')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 19h18M5 19V9l4-3 4 3v10M13 19V5h6v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('inventory')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 7.5 12 3l9 4.5-9 4.5-9-4.5Zm0 4.5L12 16.5 21 12M3 16.5 12 21l9-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('purchasing')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 5h2l2 10h10l2-7H7M9 19a1 1 0 1 0 0 .01M17 19a1 1 0 1 0 0 .01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('sales')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M4 18V6m5 12V10m5 8V8m5 10V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M3 20h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('products')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('suppliers')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M4 20V8l8-4 8 4v12" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 20v-4h6v4M9 10h1M14 10h1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('customers')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M16 19a4 4 0 0 0-8 0M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm9 6a4 4 0 0 0-3-3.87M18 4.13A3 3 0 0 1 18 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('financial')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 10h18M8 14h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('reports')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M6 20h12M8 20V8m4 12V4m4 16v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('audit')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 3 5 6v6c0 4.4 3 8.4 7 9 4-0.6 7-4.6 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('users')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M16 19a4 4 0 0 0-8 0M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @default
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/></svg>
                                            @endswitch
                                        </span>
                                        <span>{{ $moduleLabels[$module] }}</span>
                                    </span>
                                    <span class="ind-module-caret" aria-hidden="true">▾</span>
                                </button>

                                <div class="ind-module-subitems" data-module-panel>
                                    @foreach ($subitems as $subitem)
                                        @if (! empty($subitem['href']))
                                            <a href="{{ $subitem['href'] }}" @class(['ind-module-subitem', 'is-active' => (bool) ($subitem['active'] ?? false)])>
                                                {{ $subitem['label'] }}
                                            </a>
                                        @else
                                            <span class="ind-module-subitem is-disabled">{{ $subitem['label'] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <x-ui.menu-item
                                    variant="industrial"
                                    :href="$moduleLinks[$module] ?? '#'"
                                    data-client-sidebar-link
                                    :active="$moduleActive"
                                    class="ind-module-parent"
                                >
                                    <span class="inline-flex items-center gap-2">
                                        <span class="inline-flex h-4 w-4 items-center justify-center text-[#5f6368]" aria-hidden="true">
                                            @switch($moduleIcons[$module] ?? null)
                                                @case('production_mrp')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 19h18M5 19V9l4-3 4 3v10M13 19V5h6v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('inventory')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 7.5 12 3l9 4.5-9 4.5-9-4.5Zm0 4.5L12 16.5 21 12M3 16.5 12 21l9-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('purchasing')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 5h2l2 10h10l2-7H7M9 19a1 1 0 1 0 0 .01M17 19a1 1 0 1 0 0 .01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('sales')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M4 18V6m5 12V10m5 8V8m5 10V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M3 20h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('products')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('suppliers')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M4 20V8l8-4 8 4v12" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 20v-4h6v4M9 10h1M14 10h1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('customers')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M16 19a4 4 0 0 0-8 0M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm9 6a4 4 0 0 0-3-3.87M18 4.13A3 3 0 0 1 18 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('financial')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 10h18M8 14h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('reports')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M6 20h12M8 20V8m4 12V4m4 16v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('audit')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 3 5 6v6c0 4.4 3 8.4 7 9 4-0.6 7-4.6 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('users')
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M16 19a4 4 0 0 0-8 0M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @default
                                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/></svg>
                                            @endswitch
                                        </span>
                                        <span>{{ $moduleLabels[$module] }}</span>
                                    </span>
                                </x-ui.menu-item>
                            @endif
                        </div>
                    @endif
                @empty
                    <x-ui.menu-item variant="industrial" href="#" class="text-muted">{{ __('ui.modules') }}</x-ui.menu-item>
                @endforelse
            </x-ui.menu>

            <x-slot:footer>
                <form method="POST" action="{{ route('logout') }}" data-client-sidebar-content>
                    @csrf
                    <button class="ind-logout-button" type="submit">{{ __('ui.logout') }}</button>
                </form>
            </x-slot:footer>
        </x-ui.sidebar>

        <div class="ind-main-area">
            <header class="ind-topbar">
                <div class="ind-topbar-left">
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

            <main>
                @yield('client-content')
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
        const sidebar = document.getElementById('sidebar');
        const sidebarShell = document.querySelector('[data-client-sidebar-shell]');
        const sidebarCollapseToggle = document.querySelector('[data-client-sidebar-toggle]');
        const settingsToggle = document.getElementById('settingsToggle');
        const settingsPanel = document.getElementById('settingsPanel');
        const settingsOverlay = document.getElementById('settingsOverlay');
        const settingsClose = document.getElementById('settingsClose');

        if (sidebarShell && sidebar && sidebarCollapseToggle) {
            const storageKey = 'client.sidebar-collapsed';
            const contentElements = document.querySelectorAll('[data-client-sidebar-content]');
            const linkElements = document.querySelectorAll('[data-client-sidebar-link]');
            const toggleIcon = document.querySelector('[data-client-sidebar-toggle-icon]');
            const mobileQuery = window.matchMedia('(max-width: 920px)');

            const applySidebarState = (collapsed) => {
                const effectiveCollapsed = collapsed && !mobileQuery.matches;

                sidebarShell.classList.toggle('is-collapsed', effectiveCollapsed);
                sidebar.classList.toggle('is-collapsed', effectiveCollapsed);

                for (const element of contentElements) {
                    element.classList.toggle('hidden', effectiveCollapsed);
                }

                for (const link of linkElements) {
                    link.classList.toggle('justify-center', effectiveCollapsed);
                    link.classList.toggle('px-3', effectiveCollapsed);
                    link.classList.toggle('px-4', !effectiveCollapsed);
                }

                sidebarCollapseToggle.setAttribute('aria-expanded', effectiveCollapsed ? 'false' : 'true');
                sidebarCollapseToggle.setAttribute(
                    'aria-label',
                    effectiveCollapsed ? (sidebarCollapseToggle.dataset.expandLabel ?? 'Expand sidebar') : (sidebarCollapseToggle.dataset.collapseLabel ?? 'Collapse sidebar'),
                );

                if (toggleIcon) {
                    toggleIcon.textContent = effectiveCollapsed ? '→' : '←';
                }
            };

            const initialCollapsed = window.localStorage.getItem(storageKey) === '1';
            applySidebarState(initialCollapsed);

            sidebarCollapseToggle.addEventListener('click', () => {
                const collapsed = !sidebar.classList.contains('is-collapsed');
                applySidebarState(collapsed);
                window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
            });

            mobileQuery.addEventListener('change', () => {
                const collapsed = window.localStorage.getItem(storageKey) === '1';
                applySidebarState(collapsed);
            });
        }

        const moduleGroups = document.querySelectorAll('[data-module-group]');

        if (moduleGroups.length > 0) {
            const setModuleGroupState = (group, isOpen) => {
                group.classList.toggle('is-open', isOpen);
                const toggle = group.querySelector('[data-module-toggle]');

                if (toggle) {
                    toggle.setAttribute('aria-expanded', String(isOpen));
                }
            };

            for (const group of moduleGroups) {
                const toggle = group.querySelector('[data-module-toggle]');

                if (! toggle) {
                    continue;
                }

                toggle.addEventListener('click', () => {
                    const willOpen = !group.classList.contains('is-open');

                    for (const other of moduleGroups) {
                        setModuleGroupState(other, false);
                    }

                    setModuleGroupState(group, willOpen);
                });
            }
        }

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

        window.addEventListener('load', function () {
            if (typeof window.initializeUiSelects === 'function') {
                window.initializeUiSelects();
            }
        });
    </script>
@endsection
