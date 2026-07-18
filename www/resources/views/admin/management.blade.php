@extends('layouts.google')

@section('title', __('admin.title').' | '.__('ui.app_name'))

@section('bodyClass', 'bg-slate-100 text-slate-900')

@section('content')
  <div class="flex min-h-screen">
    <x-ui.sidebar variant="admin">
      <x-slot:header>
        <div>
          <h1 class="font-display text-2xl font-bold">{{ __('admin.heading') }}</h1>
          <p class="text-sm text-slate-500 mt-2">{{ __('admin.menu_description') }}</p>
        </div>
      </x-slot:header>

      <x-ui.menu variant="admin">
        <x-ui.menu-item variant="admin" href="#visao-geral" data-section-link="visao-geral">
          <span class="inline-flex items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5h7v6H4V5Zm9 0h7v14h-7V5ZM4 13h7v6H4v-6Z" fill="currentColor"/></svg>
            <span>{{ __('admin.overview') }}</span>
          </span>
        </x-ui.menu-item>
        <x-ui.menu-item variant="admin" href="#sec-empresas" data-section-link="sec-empresas">
          <span class="inline-flex items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span>{{ __('admin.companies_plans') }}</span>
          </span>
        </x-ui.menu-item>
        <x-ui.menu-item variant="admin" href="#sec-usuarios" data-section-link="sec-usuarios">
          <span class="inline-flex items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 19a4 4 0 0 0-8 0m11 0a3 3 0 0 0-6 0m6 0v1H5v-1m14 0a3 3 0 0 0-3-3M5 19a3 3 0 0 1 3-3m0 0a4 4 0 1 1 8 0M9 9a3 3 0 1 0 0 .01M17 9a2 2 0 1 0 0 .01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span>{{ __('admin.users_admins') }}</span>
          </span>
        </x-ui.menu-item>
        <x-ui.menu-item variant="admin" :href="route('admin.management')">
          <span class="inline-flex items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.34-5.66M20 4v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>{{ __('admin.refresh_panel') }}</span>
          </span>
        </x-ui.menu-item>
        <x-ui.menu-item variant="admin" :href="route('dashboard.industrial')">
          <span class="inline-flex items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 17 5 12l5-5M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>{{ __('admin.back_dashboard') }}</span>
          </span>
        </x-ui.menu-item>
      </x-ui.menu>

      <x-slot:footer>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full rounded-lg bg-coral px-4 py-2.5 text-sm font-semibold hover:brightness-110 transition">{{ __('admin.logout') }}</button>
        </form>
      </x-slot:footer>
    </x-ui.sidebar>

    <main class="flex-1 px-8 py-8 space-y-6">
      <x-ui.breadcrumb :items="[
        ['label' => __('ui.app_name'), 'href' => route('dashboard.industrial')],
        ['label' => __('admin.title')],
      ]" />

      @if (session('status'))
        <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
      @endif

      @if ($errors->any())
        <x-ui.alert variant="error">
          <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </x-ui.alert>
      @endif

      <x-ui.panel id="visao-geral">
        <x-ui.page-heading
          :title="__('admin.overview')"
          :subtitle="__('admin.overview_description')"
        />

        <div class="mt-5 grid md:grid-cols-2 xl:grid-cols-4 gap-3">
          <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs text-slate-500">{{ __('admin.filtered_companies') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ $companies->total() }}</p>
          </article>
          <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs text-slate-500">{{ __('admin.filtered_users') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ $users->total() }}</p>
          </article>
          <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs text-slate-500">{{ __('admin.companies_page') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ $companies->currentPage() }}/{{ $companies->lastPage() }}</p>
          </article>
          <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs text-slate-500">{{ __('admin.users_page') }}</p>
            <p class="mt-1 text-2xl font-bold">{{ $users->currentPage() }}/{{ $users->lastPage() }}</p>
          </article>
        </div>
      </x-ui.panel>

      <x-ui.panel id="sec-empresas">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display text-2xl font-bold">{{ __('admin.companies_plans') }}</h2>
          <p class="text-sm text-slate-500">{{ __('admin.manage_customers') }}</p>
        </div>

        <form method="GET" action="{{ route('admin.management') }}" class="mb-5 grid md:grid-cols-4 gap-3">
          <input type="hidden" name="user_search" value="{{ $userFilters['search'] }}" />
          <input type="hidden" name="user_is_active" value="{{ $userFilters['is_active'] }}" />
          <input type="hidden" name="user_email_verified" value="{{ $userFilters['email_verified'] }}" />
          <input type="hidden" name="user_is_platform_admin" value="{{ $userFilters['is_platform_admin'] }}" />

          <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="company_search">{{ __('admin.company_search') }}</label>
            <input id="company_search" name="company_search" type="text" value="{{ $companyFilters['search'] }}" placeholder="{{ __('admin.company_search_placeholder') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="company_is_active">{{ __('admin.company_status') }}</label>
            <select id="company_is_active" name="company_is_active" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
              <option value="" @selected($companyFilters['is_active'] === '')>{{ __('admin.all') }}</option>
              <option value="1" @selected($companyFilters['is_active'] === '1')>{{ __('admin.active_plural') }}</option>
              <option value="0" @selected($companyFilters['is_active'] === '0')>{{ __('admin.inactive_plural') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="company_plan_status">{{ __('admin.plan_status') }}</label>
            <select id="company_plan_status" name="company_plan_status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
              <option value="" @selected($companyFilters['plan_status'] === '')>{{ __('admin.all') }}</option>
              @foreach (['trialing', 'active', 'past_due', 'canceled', 'suspended'] as $statusOption)
                <option value="{{ $statusOption }}" @selected($companyFilters['plan_status'] === $statusOption)>{{ $statusOption }}</option>
              @endforeach
            </select>
          </div>

          <div class="md:col-span-4 flex gap-2">
            <x-ui.button type="submit" variant="primary">{{ __('admin.filter_companies') }}</x-ui.button>
            <a href="{{ route('admin.management', [
              'user_search' => $userFilters['search'],
              'user_is_active' => $userFilters['is_active'],
              'user_email_verified' => $userFilters['email_verified'],
              'user_is_platform_admin' => $userFilters['is_platform_admin'],
            ]) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold transition hover:bg-slate-50">{{ __('admin.clear_company_filters') }}</a>
          </div>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b border-slate-200">
                <th class="py-3 pr-4">{{ __('admin.company') }}</th>
                <th class="py-3 pr-4">{{ __('admin.organization') }}</th>
                <th class="py-3 pr-4">{{ __('admin.users') }}</th>
                <th class="py-3 pr-4">{{ __('admin.plan') }}</th>
                <th class="py-3 pr-4">{{ __('admin.trial') }}</th>
                <th class="py-3 pr-4">{{ __('admin.company_status_col') }}</th>
                <th class="py-3">{{ __('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($companies as $company)
                @php
                  $organization = $organizationsByCompany[$company->id] ?? null;
                  $subscription = $organization ? ($subscriptionsByOrganization[$organization->id] ?? null) : null;
                  $trial = $organization ? ($trialsByOrganization[$organization->id] ?? null) : null;
                @endphp
                <tr class="border-b border-slate-100 align-top">
                  <td class="py-4 pr-4">
                    <p class="font-semibold">{{ $company->name }}</p>
                    <p class="text-xs text-slate-500">{{ __('admin.code') }}: {{ $company->code }}</p>
                  </td>
                  <td class="py-4 pr-4">
                    @if ($organization)
                      <p>{{ $organization->name }}</p>
                      <p class="text-xs text-slate-500">{{ __('admin.slug') }}: {{ $organization->slug }}</p>
                    @else
                      <span class="text-xs text-rose-600">{{ __('admin.organization_missing') }}</span>
                    @endif
                  </td>
                  <td class="py-4 pr-4">
                    <p>{{ $company->users_count }}</p>
                  </td>
                  <td class="py-4 pr-4">
                    <p class="font-medium">{{ $subscription?->plan_code ?? __('admin.without_plan') }}</p>
                    <p class="text-xs text-slate-500">{{ $subscription?->status ?? __('admin.not_configured') }}</p>
                  </td>
                  <td class="py-4 pr-4">
                    @if ($trial)
                      <p>{{ $trial->status }}</p>
                      <p class="text-xs text-slate-500">{{ __('admin.trial_end') }}: {{ optional($trial->trial_end_date)->format('d/m/Y H:i') }}</p>
                    @else
                      <span class="text-xs text-slate-500">{{ __('admin.without_trial') }}</span>
                    @endif
                  </td>
                  <td class="py-4 pr-4">
                    <span class="px-2 py-1 rounded-full text-xs {{ $company->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                      {{ $company->is_active ? __('admin.active') : __('admin.inactive') }}
                    </span>
                  </td>
                  <td class="py-4">
                    <div class="space-y-3 min-w-80">
                      <form method="POST" action="{{ route('admin.companies.status', $company) }}" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="{{ $company->is_active ? '0' : '1' }}" />
                        <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50 transition">
                          {{ $company->is_active ? __('admin.deactivate_company') : __('admin.activate_company') }}
                        </button>
                      </form>

                      <form method="POST" action="{{ route('admin.companies.plan', $company) }}" class="flex flex-wrap gap-2">
                        @csrf
                        @method('PATCH')
                        <label for="plan_code_{{ $company->id }}" class="sr-only">{{ __('admin.plan') }}</label>
                        <input id="plan_code_{{ $company->id }}" type="text" name="plan_code" value="{{ $subscription?->plan_code ?? 'free_trial' }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs" />
                        <label for="plan_status_{{ $company->id }}" class="sr-only">{{ __('admin.plan_status') }}</label>
                        <select id="plan_status_{{ $company->id }}" name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-xs">
                          @foreach (['trialing', 'active', 'past_due', 'canceled', 'suspended'] as $planStatus)
                            <option value="{{ $planStatus }}" @selected(($subscription?->status ?? 'trialing') === $planStatus)>{{ $planStatus }}</option>
                          @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 rounded-xl bg-night text-white text-xs font-semibold hover:opacity-90 transition">{{ __('admin.save_plan') }}</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="py-6 text-center text-slate-500">{{ __('admin.no_companies') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $companies->links() }}
        </div>
      </x-ui.panel>

      <x-ui.panel id="sec-usuarios">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display text-2xl font-bold">{{ __('admin.users_admins') }}</h2>
          <p class="text-sm text-slate-500">{{ __('admin.manage_users_description') }}</p>
        </div>

        <form method="GET" action="{{ route('admin.management') }}" class="mb-5 grid md:grid-cols-5 gap-3">
          <input type="hidden" name="company_search" value="{{ $companyFilters['search'] }}" />
          <input type="hidden" name="company_is_active" value="{{ $companyFilters['is_active'] }}" />
          <input type="hidden" name="company_plan_status" value="{{ $companyFilters['plan_status'] }}" />

          <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="user_search">{{ __('admin.user_search') }}</label>
            <input id="user_search" name="user_search" type="text" value="{{ $userFilters['search'] }}" placeholder="{{ __('admin.user_search_placeholder') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="user_is_active">{{ __('admin.status') }}</label>
            <select id="user_is_active" name="user_is_active" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
              <option value="" @selected($userFilters['is_active'] === '')>{{ __('admin.all') }}</option>
              <option value="1" @selected($userFilters['is_active'] === '1')>{{ __('admin.active_plural') }}</option>
              <option value="0" @selected($userFilters['is_active'] === '0')>{{ __('admin.inactive_plural') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="user_email_verified">{{ __('admin.email') }}</label>
            <select id="user_email_verified" name="user_email_verified" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
              <option value="" @selected($userFilters['email_verified'] === '')>{{ __('admin.all') }}</option>
              <option value="1" @selected($userFilters['email_verified'] === '1')>{{ __('admin.confirmed') }}</option>
              <option value="0" @selected($userFilters['email_verified'] === '0')>{{ __('admin.not_confirmed') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="user_is_platform_admin">{{ __('admin.admin') }}</label>
            <select id="user_is_platform_admin" name="user_is_platform_admin" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
              <option value="" @selected($userFilters['is_platform_admin'] === '')>{{ __('admin.all') }}</option>
              <option value="1" @selected($userFilters['is_platform_admin'] === '1')>{{ __('admin.admins_only') }}</option>
              <option value="0" @selected($userFilters['is_platform_admin'] === '0')>{{ __('admin.non_admins') }}</option>
            </select>
          </div>

          <div class="md:col-span-5 flex gap-2">
            <x-ui.button type="submit" variant="primary">{{ __('admin.filter_users') }}</x-ui.button>
            <a href="{{ route('admin.management', [
              'company_search' => $companyFilters['search'],
              'company_is_active' => $companyFilters['is_active'],
              'company_plan_status' => $companyFilters['plan_status'],
            ]) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold transition hover:bg-slate-50">{{ __('admin.clear_user_filters') }}</a>
          </div>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b border-slate-200">
                <th class="py-3 pr-4">{{ __('onboarding.user') }}</th>
                <th class="py-3 pr-4">{{ __('admin.current_company') }}</th>
                <th class="py-3 pr-4">{{ __('admin.linked_companies') }}</th>
                <th class="py-3 pr-4">{{ __('admin.status') }}</th>
                <th class="py-3 pr-4">{{ __('admin.email') }}</th>
                <th class="py-3 pr-4">{{ __('admin.admin') }}</th>
                <th class="py-3">{{ __('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $user)
                <tr class="border-b border-slate-100 align-top">
                  <td class="py-4 pr-4">
                    <p class="font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                  </td>
                  <td class="py-4 pr-4">{{ $user->currentCompany?->name ?? '-' }}</td>
                  <td class="py-4 pr-4">{{ $user->companies_count }}</td>
                  <td class="py-4 pr-4">
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                      {{ $user->is_active ? __('admin.active') : __('admin.inactive') }}
                    </span>
                  </td>
                  <td class="py-4 pr-4">
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->email_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                      {{ $user->email_verified_at ? __('admin.confirmed') : __('admin.not_confirmed') }}
                    </span>
                  </td>
                  <td class="py-4 pr-4">
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->is_platform_admin ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700' }}">
                      {{ $user->is_platform_admin ? __('admin.yes') : __('admin.no') }}
                    </span>
                  </td>
                  <td class="py-4">
                    <div class="flex flex-wrap gap-2 min-w-105">
                      <form method="POST" action="{{ route('admin.users.status', $user) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}" />
                        <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50 transition">{{ $user->is_active ? __('admin.deactivate') : __('admin.activate') }}</button>
                      </form>

                      <form method="POST" action="{{ route('admin.users.email-verification', $user) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_verified" value="{{ $user->email_verified_at ? '0' : '1' }}" />
                        <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50 transition">{{ $user->email_verified_at ? __('admin.unverify_email') : __('admin.verify_email') }}</button>
                      </form>

                      <form method="POST" action="{{ route('admin.users.platform-admin', $user) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_platform_admin" value="{{ $user->is_platform_admin ? '0' : '1' }}" />
                        <button type="submit" class="px-3 py-2 rounded-xl bg-night text-white text-xs font-semibold hover:opacity-90 transition">{{ $user->is_platform_admin ? __('admin.remove_admin') : __('admin.make_admin') }}</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="py-6 text-center text-slate-500">{{ __('admin.no_users') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $users->links() }}
        </div>
      </x-ui.panel>
    </main>
  </div>

@endsection

@section('scripts')
  <script>
    const sectionLinks = Array.from(document.querySelectorAll('[data-section-link]'));
    const sections = sectionLinks
      .map((link) => document.getElementById(link.getAttribute('data-section-link')))
      .filter(Boolean);

    const setActiveLink = (id) => {
      sectionLinks.forEach((link) => {
        const isActive = link.getAttribute('data-section-link') === id;
        link.classList.toggle('is-active', isActive);
        if (isActive) {
          link.setAttribute('aria-current', 'true');
        } else {
          link.removeAttribute('aria-current');
        }
      });
    };

    if (sections.length > 0) {
      const observer = new IntersectionObserver((entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (visible?.target?.id) {
          setActiveLink(visible.target.id);
        }
      }, {
        rootMargin: '-20% 0px -60% 0px',
        threshold: [0.2, 0.5, 0.8],
      });

      sections.forEach((section) => observer.observe(section));
      setActiveLink(sections[0].id);
    }

    window.addEventListener('hashchange', () => {
      const id = window.location.hash.replace('#', '');
      if (id) {
        setActiveLink(id);
      }
    });
  </script>
@endsection
