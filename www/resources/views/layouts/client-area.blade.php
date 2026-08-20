@extends('layouts.public')

@section('bodyClass', 'ui-shell')
@section('themeSwitcherHandled', 'true')

@section('content')
    @php
        $moduleLabels = [
            'engineering' => __('ui.domain_engineering'),
            'planning' => __('ui.domain_planning'),
            'shop_floor' => __('ui.domain_shop_floor'),
            'analysis' => __('ui.domain_analysis'),
            'administration' => __('ui.domain_administration'),
            'inventory' => __('ui.module_inventory'),
            'purchasing' => __('ui.module_purchasing'),
            'sales' => __('ui.module_sales'),
        ];

        $moduleIcons = [
            'engineering' => 'products',
            'planning' => 'production_mrp',
            'shop_floor' => 'production_mrp',
            'analysis' => 'reports',
            'administration' => 'users',
            'inventory' => 'inventory',
            'purchasing' => 'purchasing',
            'sales' => 'sales',
        ];

        $moduleSubitems = [
            'engineering' => [
                ['label' => __('ui.product_register'), 'href' => route('products.index'), 'active' => request()->routeIs('products.index') || request()->routeIs('products.create') || request()->routeIs('products.show') || request()->routeIs('products.edit')],
                ['label' => __('ui.product_versions'), 'href' => route('products.versions'), 'active' => request()->routeIs('products.versions') || request()->routeIs('products.versions.*')],
                ['label' => __('ui.bom_structures'), 'href' => route('bom.structures.index'), 'active' => request()->routeIs('bom.structures.*')],
                ['label' => __('ui.bom_revisions'), 'href' => route('bom.material-lists.index'), 'active' => request()->routeIs('bom.material-lists.*')],
                ['label' => __('ui.module_routing'), 'href' => route('production.routing.index'), 'active' => request()->routeIs('production.routing.*')],
                ['label' => __('ui.work_centers'), 'href' => route('production.work-centers.index'), 'active' => request()->routeIs('production.work-centers.*')],
            ],
            'planning' => [
                ['label' => __('ui.module_scheduling'), 'href' => route('production.scheduling.index'), 'active' => request()->routeIs('production.scheduling.*')],
                ['label' => __('ui.production_calendar'), 'href' => route('production.calendar.index'), 'active' => request()->routeIs('production.calendar.*')],
            ],
            'shop_floor' => [
                ['label' => __('ui.production_orders'), 'href' => route('production.orders.index'), 'active' => request()->routeIs('production.orders.*')],
            ],
            'analysis' => [
                ['label' => __('ui.production_postings'), 'href' => route('production.analytics.index'), 'active' => request()->routeIs('production.analytics.*')],
            ],
            'administration' => [
                ['label' => __('ui.manage_accesses'), 'href' => route('company-access.users.index'), 'active' => request()->routeIs('company-access.users.*')],
                ['label' => __('ui.rbac_roles'), 'href' => route('company-access.rbac.roles.index'), 'active' => request()->routeIs('company-access.rbac.roles.*')],
            ],
            'sales' => [
                ['label' => __('ui.sales_register'), 'href' => route('sales.index'), 'active' => request()->routeIs('sales.*')],
                ['label' => __('ui.sales_customers'), 'href' => route('customers.index'), 'active' => request()->routeIs('customers.*')],
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
                ['label' => __('ui.admin_units'), 'href' => route('admin-data.units.index'), 'active' => request()->routeIs('admin-data.units.*')],
                ['label' => __('ui.admin_categories'), 'href' => route('admin-data.categories.index'), 'active' => request()->routeIs('admin-data.categories.*')],
                ['label' => __('ui.admin_brands'), 'href' => route('admin-data.brands.index'), 'active' => request()->routeIs('admin-data.brands.*')],
                ['label' => __('ui.inventory_movements'), 'href' => route('inventory.movements.index'), 'active' => request()->routeIs('inventory.movements.*')],
                ['label' => __('ui.inventory_count'), 'href' => route('inventory.balances.index'), 'active' => request()->routeIs('inventory.balances.*')],
            ],
        ];

        $modulePriority = [
            'engineering',
            'planning',
            'shop_floor',
            'analysis',
            'inventory',
            'purchasing',
            'sales',
            'administration',
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
                $organization = $company;
                $subscription = $company
                    ? \App\Models\SaaS\Subscription::query()->where('company_id', $company->id)->latest('id')->first()
                    : null;
                $subscriptionPlan = $subscription
                    ? app(\App\Services\SaaS\AccountOnboardingService::class)->planForCode($subscription->plan_code)
                    : null;
                $availableModules = app(\App\Services\SaaS\CompanyUserAccessService::class)->accessibleModules($user, $company);
                $availableModules = array_values(array_diff($availableModules, ['suppliers', 'customers']));
                $accessibleModuleSet = array_fill_keys($availableModules, true);
                $domainRequirements = [
                    'engineering' => ['production_mrp', 'products'],
                    'planning' => ['production_mrp'],
                    'shop_floor' => ['production_mrp'],
                    'analysis' => ['production_mrp'],
                    'inventory' => ['inventory'],
                    'purchasing' => ['purchasing'],
                    'sales' => ['sales'],
                    'administration' => ['users'],
                ];
                $availableModules = collect($domainRequirements)
                    ->filter(static fn (array $requirements): bool => collect($requirements)->contains(static fn (string $requirement): bool => isset($accessibleModuleSet[$requirement])))
                    ->keys()
                    ->all();
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

        foreach (array_keys($moduleSubitems) as $domainKey) {
            array_unshift($moduleSubitems[$domainKey], [
                'label' => __('ui.dashboard'),
                'href' => route('domains.dashboard', ['domain' => $domainKey]),
                'active' => request()->routeIs('domains.dashboard') && (string) request()->route('domain') === $domainKey,
            ]);
        }

        $activeCompanyName = $company?->name ?? __('ui.app_name');
        $currentPageTitle = trim((string) $__env->yieldContent('client-page-title'));
        $rawTutorialRouteName = (string) (request()->route()?->getName() ?? '');
        $tutorialRouteName = $rawTutorialRouteName !== '' ? $rawTutorialRouteName : trim((string) request()->path(), '/');
        $pageTutorial = $tutorialRouteName !== ''
            ? \App\Models\PageTutorial::query()->where('route_name', $tutorialRouteName)->first()
            : null;
        $canEditTutorial = $company !== null
            && $user instanceof \App\Modules\Identity\Infrastructure\Persistence\Models\User
            && app(\App\Services\SaaS\CompanyUserAccessService::class)->isCompanyAdministrator($user, $company);
    @endphp

    @php
        $clientNavigation = collect($availableModules)
            ->filter(fn (string $module): bool => $module !== 'administration' || $canManageAccesses)
            ->map(fn (string $module): array => [
                'label' => $moduleLabels[$module] ?? ucfirst($module),
                'icon' => [
                    'engineering' => 'package',
                    'planning' => 'calendar',
                    'shop_floor' => 'building-factory',
                    'analysis' => 'chart-bar',
                    'administration' => 'users',
                    'inventory' => 'building-factory',
                    'purchasing' => 'shopping-cart',
                    'sales' => 'receipt',
                ][$module] ?? 'package',
                'active' => collect($moduleSubitems[$module] ?? [])->contains(fn (array $item): bool => (bool) ($item['active'] ?? false)),
                'children' => $moduleSubitems[$module] ?? [],
            ])
            ->values()
            ->all();
    @endphp

    <x-ui.app-shell
        :navigation="$clientNavigation"
        navigation-label="{{ __('ui.modules') }}"
        brand-name="{{ __('ui.app_name') }}"
        brand-href="{{ route('dashboard.industrial') }}"
        :brand-subtitle="$activeCompanyName"
        :header-title="$currentPageTitle"
    >
        <x-slot:headerActions>
            <x-ui.icon-button type="button" icon="help-circle" variant="ghost" :label="__('ui.tutorial_help')" data-ui-modal-open="tutorialPanel" aria-controls="tutorialPanel" />
            <x-ui.icon-button type="button" icon="settings" variant="ghost" :label="__('ui.settings')" data-ui-modal-open="settingsPanel" aria-controls="settingsPanel" />
        </x-slot:headerActions>

        <x-slot:sidebarFooter>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" :full="true" size="lg" class="justify-start gap-2">
                    <x-ui.icon name="logout" size="sm" />
                    <span data-ds-sidebar-label>{{ __('ui.logout') }}</span>
                </x-ui.button>
            </form>
        </x-slot:sidebarFooter>

        @yield('client-content')
    </x-ui.app-shell>

    <x-ui.modal id="tutorialPanel" size="sheet" :title="__('ui.tutorial_panel_title')" :close-label="__('ui.close')">
        @if ($canEditTutorial)
            <form method="POST" action="{{ route('page-tutorials.upsert') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="route_name" value="{{ $tutorialRouteName }}" />

                <div class="ui-client-html-editor" data-html-editor>
                    <x-ui.editor-toolbar :aria-label="__('ui.tutorial_content_html')">
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="formatBlock" data-editor-value="P" title="{{ __('ui.editor_paragraph') }}">P</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="formatBlock" data-editor-value="H2" title="{{ __('ui.editor_title') }}">H2</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="bold" title="{{ __('ui.editor_bold') }}"><strong>B</strong></button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="italic" title="{{ __('ui.editor_italic') }}"><em>I</em></button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="underline" title="{{ __('ui.editor_underline') }}"><u>U</u></button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="insertUnorderedList" title="{{ __('ui.editor_list') }}">• {{ __('ui.editor_list') }}</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="createLink" title="{{ __('ui.editor_link') }}">{{ __('ui.editor_link') }}</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="removeFormat" title="{{ __('ui.editor_clear_formatting') }}">{{ __('ui.editor_clear') }}</button>
                    </x-ui.editor-toolbar>

                    <div
                        id="tutorialContentEditor"
                        class="ui-client-html-editor-surface"
                        contenteditable="true"
                        data-editor-surface
                        aria-label="{{ __('ui.tutorial_content_html') }}"
                    >{!! old('content_html', $pageTutorial?->content_html ?? '') !!}</div>

                    <x-ui.textarea id="tutorialContent" name="content_html" rows="12" class="hidden" data-editor-source>{!! old('content_html', $pageTutorial?->content_html ?? '') !!}</x-ui.textarea>
                </div>

                <div class="flex justify-end">
                    <x-ui.button type="submit" variant="primary">{{ __('ui.save') }}</x-ui.button>
                </div>
            </form>
        @else
            @if ($pageTutorial !== null)
                <article class="ui-client-tutorial-content">
                    {!! $pageTutorial->content_html !!}
                </article>
            @else
                <x-ui.alert variant="warning">{{ __('ui.tutorial_empty') }}</x-ui.alert>
            @endif
        @endif
        <x-slot:footer>
            <x-ui.button variant="primary" data-ui-modal-close>{{ __('ui.close') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.modal id="settingsPanel" size="sheet" :title="__('ui.settings_panel_title')" :close-label="__('ui.close')">
        @php
            $subscriptionPlanName = $subscriptionPlan['label'] ?? ($subscription?->plan_code ?? __('ui.no_subscription'));
            $subscriptionAmount = 'R$ '.number_format((($subscriptionPlan['amount_cents'] ?? 0) / 100), 2, ',', '.');
            $subscriptionPaymentMethod = $subscriptionPlan['payment_method'] ?? '-';
            $subscriptionDueDate = $subscription?->ends_at?->format('d/m/Y') ?? __('ui.no_due_date');
        @endphp

        <div class="space-y-6">
            <x-ui.theme-picker />

            @php($currentLocale = auth()->user()?->preferred_locale ?? app()->getLocale())
            <form method="POST" action="{{ route('preferences.language.update') }}" class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
                @csrf
                <x-ui.field :label="__('ui.language')" for="preferredLocale" :required="true" :error="$errors->first('preferred_locale')">
                    <div class="flex items-end gap-3">
                        <x-ui.select id="preferredLocale" name="preferred_locale" required :aria-describedby="$errors->has('preferred_locale') ? 'preferredLocale-error' : null">
                            <option value="pt_BR" @selected($currentLocale === 'pt_BR')>{{ __('ui.portuguese') }}</option>
                            <option value="en" @selected($currentLocale === 'en')>{{ __('ui.english') }}</option>
                            <option value="es" @selected($currentLocale === 'es')>{{ __('ui.spanish') }}</option>
                        </x-ui.select>
                        <x-ui.button type="submit" variant="primary">{{ __('ui.save') }}</x-ui.button>
                    </div>
                </x-ui.field>
            </form>

        <section class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4">
            <div>
                <h3 class="font-semibold text-[var(--ui-text)]">{{ __('ui.subscription_section_title') }}</h3>
                <p class="mt-1 text-sm leading-6 text-[var(--ui-text-muted)]">{{ __('ui.subscription_section_description') }}</p>
            </div>

            <dl class="mt-4 divide-y divide-[var(--ui-border)] border-y border-[var(--ui-border)]">
                <div class="flex items-baseline justify-between gap-4 py-3">
                    <dt class="text-xs text-[var(--ui-text-muted)]">{{ __('ui.current_plan') }}</dt>
                    <dd class="text-right text-sm font-semibold text-[var(--ui-text)]">{{ $subscriptionPlanName }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-3">
                    <dt class="text-xs text-[var(--ui-text-muted)]">{{ __('global_plan.amount_short') }}</dt>
                    <dd class="text-right text-sm font-semibold text-[var(--ui-text)]">{{ $subscriptionAmount }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-3">
                    <dt class="text-xs text-[var(--ui-text-muted)]">{{ __('ui.payment_method') }}</dt>
                    <dd class="text-right text-sm font-semibold text-[var(--ui-text)]">{{ $subscriptionPaymentMethod }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 py-3">
                    <dt class="text-xs text-[var(--ui-text-muted)]">{{ __('ui.due_date') }}</dt>
                    <dd class="text-right text-sm font-semibold text-[var(--ui-text)]">{{ $subscriptionDueDate }}</dd>
                </div>
            </dl>

            <x-ui.button class="mt-4" :href="route('billing.subscription.show')" variant="outline" :full="true">{{ __('ui.renew_or_change_plan') }}</x-ui.button>
        </section>
        </div>
        <x-slot:footer>
            <x-ui.button variant="primary" data-ui-modal-close>{{ __('ui.close') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endsection

@section('scripts')
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarShell = document.querySelector('[data-client-sidebar-shell]');
        const sidebarCollapseToggle = document.querySelector('[data-client-sidebar-toggle]');

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
                    effectiveCollapsed ? (sidebarCollapseToggle.dataset.expandLabel ?? '') : (sidebarCollapseToggle.dataset.collapseLabel ?? ''),
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

        const htmlEditor = document.querySelector('[data-html-editor]');

        if (htmlEditor) {
            const surface = htmlEditor.querySelector('[data-editor-surface]');
            const source = htmlEditor.querySelector('[data-editor-source]');
            const toolbarButtons = htmlEditor.querySelectorAll('[data-editor-command]');
            const editorForm = htmlEditor.closest('form');

            const syncEditorToSource = () => {
                if (surface instanceof HTMLElement && source instanceof HTMLTextAreaElement) {
                    source.value = surface.innerHTML;
                }
            };

            for (const button of toolbarButtons) {
                button.addEventListener('click', function () {
                    if (! (surface instanceof HTMLElement)) {
                        return;
                    }

                    surface.focus();

                    const command = button.getAttribute('data-editor-command');
                    const value = button.getAttribute('data-editor-value');

                    if (! command) {
                        return;
                    }

                    if (command === 'createLink') {
                        const link = window.prompt(@json(__('ui.editor_link_prompt')), 'https://');

                        if (link && link.trim() !== '') {
                            document.execCommand('createLink', false, link.trim());
                        }
                    } else {
                        document.execCommand(command, false, value ?? undefined);
                    }

                    syncEditorToSource();
                });
            }

            surface?.addEventListener('input', syncEditorToSource);
            syncEditorToSource();

            editorForm?.addEventListener('submit', syncEditorToSource);
        }

        window.addEventListener('load', function () {
            if (typeof window.initializeUiSelects === 'function') {
                window.initializeUiSelects();
            }
        });
    </script>
@endsection
