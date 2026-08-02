@extends('layouts.client-area')

@section('title', __('rbac.title').' | '.__('ui.app_name'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div>
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('rbac.title') }}</h1>
            <p class="mt-2 text-sm text-[#5f6368]">{{ __('rbac.subtitle') }}</p>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('company-access.rbac.roles.index') }}" class="rounded-xl border border-[#dadce0] bg-white p-5 transition hover:shadow-sm">
            <p class="text-sm text-[#5f6368]">{{ __('ui.rbac_roles') }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $rolesCount }}</p>
        </a>
        <a href="{{ route('company-access.rbac.templates.index') }}" class="rounded-xl border border-[#dadce0] bg-white p-5 transition hover:shadow-sm">
            <p class="text-sm text-[#5f6368]">{{ __('ui.rbac_templates') }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $templatesCount }}</p>
        </a>
        <a href="{{ route('company-access.rbac.approvals.index') }}" class="rounded-xl border border-[#dadce0] bg-white p-5 transition hover:shadow-sm">
            <p class="text-sm text-[#5f6368]">{{ __('ui.rbac_approvals') }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $pendingRequestsCount }}</p>
        </a>
        <a href="{{ route('company-access.rbac.history.index') }}" class="rounded-xl border border-[#dadce0] bg-white p-5 transition hover:shadow-sm">
            <p class="text-sm text-[#5f6368]">{{ __('ui.rbac_history') }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $historyCount }}</p>
        </a>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <h2 class="text-lg font-semibold">Cobertura de autorização backend</h2>
            <p class="mt-2 text-sm text-[#5f6368]">Revisão automática inicial das rotas tenant para identificar endpoints sem proteção explícita.</p>

            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                <div class="rounded-lg border border-[#dadce0] p-3">
                    <dt class="text-[#5f6368]">Total</dt>
                    <dd class="text-xl font-semibold">{{ $tenantRouteCoverage['total'] }}</dd>
                </div>
                <div class="rounded-lg border border-emerald-300 bg-emerald-50 p-3">
                    <dt class="text-emerald-700">Protegidas</dt>
                    <dd class="text-xl font-semibold text-emerald-700">{{ $tenantRouteCoverage['protected'] }}</dd>
                </div>
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-3">
                    <dt class="text-amber-700">Pendentes</dt>
                    <dd class="text-xl font-semibold text-amber-700">{{ $tenantRouteCoverage['missing'] }}</dd>
                </div>
            </dl>

            @if ($tenantRouteCoverage['missing_routes'] !== [])
                <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm">
                    <p class="font-semibold">Rotas para revisão manual</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($tenantRouteCoverage['missing_routes'] as $route)
                            <li>{{ $route }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-ui.panel>

        <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
            <h2 class="text-lg font-semibold">Padrão de naming de permissões</h2>
            <p class="mt-2 text-sm text-[#5f6368]">Formato esperado: modulo.acao ou modulo.recurso.acao</p>

            @if ($invalidPermissionSlugs === [])
                <x-ui.alert class="mt-4" variant="success">Nenhuma permissão fora do padrão foi encontrada.</x-ui.alert>
            @else
                <x-ui.alert class="mt-4" variant="error">Foram encontradas permissões fora do padrão.</x-ui.alert>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-[#5f6368]">
                    @foreach ($invalidPermissionSlugs as $slug)
                        <li>{{ $slug }}</li>
                    @endforeach
                </ul>
            @endif
        </x-ui.panel>
    </div>
</div>
@endsection
