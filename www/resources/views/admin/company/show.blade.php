@extends('layouts.global-admin')
@section('title', $company->name.' | '.__('global_company.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_company.title'), 'href' => route('global-admin.companies.index')], ['label' => $company->name]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-[#5f6368]">{{ __('global_company.details') }}</p>
                <h1 class="font-display text-3xl font-bold">{{ $company->name }}</h1>
            </div>
            <span class="rounded-full px-3 py-1 text-xs {{ $company->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $company->is_active ? __('global_company.active') : __('global_company.inactive') }}
            </span>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-[#dadce0] bg-white p-5">
                <h2 class="font-display text-xl font-semibold">{{ __('global_company.details') }}</h2>
                <dl class="mt-4 divide-y divide-[#dadce0]">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_company.company_id') }}</dt>
                        <dd class="font-medium">{{ $company->id }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_company.name') }}</dt>
                        <dd class="font-medium">{{ $company->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_company.code') }}</dt>
                        <dd class="font-medium">{{ $company->code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_company.status') }}</dt>
                        <dd class="font-medium">{{ $company->is_active ? __('global_company.active') : __('global_company.inactive') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_company.created_at') }}</dt>
                        <dd class="font-medium">{{ $company->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_company.updated_at') }}</dt>
                        <dd class="font-medium">{{ $company->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-[#dadce0] bg-[#f8fafd] p-5">
                <h2 class="font-display text-xl font-semibold">{{ __('global_company.selected_plan_details') }}</h2>

                @if ($subscription !== null)
                    <dl class="mt-4 divide-y divide-[#dadce0]">
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_label') }}</dt>
                            <dd class="font-medium">{{ $selectedPlan['label'] ?? __('global_company.no_active_plan') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_code') }}</dt>
                            <dd class="font-medium">{{ $subscription->plan_code }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_status') }}</dt>
                            <dd class="font-medium">{{ $selectedPlanStatus }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_billing_cycle') }}</dt>
                            <dd class="font-medium">{{ $selectedPlan['billing_cycle_label'] ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_payment_method') }}</dt>
                            <dd class="font-medium">{{ $selectedPlan['payment_method'] ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_starts_at') }}</dt>
                            <dd class="font-medium">{{ $subscription->starts_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-[#5f6368]">{{ __('global_company.plan_ends_at') }}</dt>
                            <dd class="font-medium">{{ $subscription->ends_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-4 text-sm text-[#5f6368]">{{ __('global_company.no_selected_plan_details') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-8 rounded-2xl border border-[#dadce0] bg-[#f8fafd] p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-xl font-semibold">{{ __('global_company.related_users') }}</h2>
                <x-ui.button :href="route('global-admin.customers.create', ['company_id' => $company->id])" variant="brand-primary" class="rounded-full">
                    {{ __('global_company.create_user_for_company') }}
                </x-ui.button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                            <th class="px-3 py-3">ID</th>
                            <th class="px-3 py-3">{{ __('global_company.user_name') }}</th>
                            <th class="px-3 py-3">{{ __('global_company.user_email') }}</th>
                            <th class="px-3 py-3">{{ __('global_company.user_status') }}</th>
                            <th class="px-3 py-3">{{ __('global_company.user_email_verified') }}</th>
                            <th class="px-3 py-3">{{ __('global_company.user_linked_at') }}</th>
                            <th class="px-3 py-3">{{ __('global_company.user_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($company->users as $user)
                            <tr class="border-b border-[#e8eaed]">
                                <td class="px-3 py-3 text-[#5f6368]">{{ $user->id }}</td>
                                <td class="px-3 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-3 py-3 text-[#5f6368]">{{ $user->email }}</td>
                                <td class="px-3 py-3 text-[#5f6368]">{{ $user->is_active ? __('global_company.active') : __('global_company.inactive') }}</td>
                                <td class="px-3 py-3 text-[#5f6368]">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : __('global_company.user_not_verified') }}</td>
                                <td class="px-3 py-3 text-[#5f6368]">{{ $user->pivot?->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-3 py-3">
                                    <x-ui.button :href="route('global-admin.customers.edit', ['customer' => $user->id, 'company_id' => $company->id])" variant="surface-muted" size="sm" class="rounded-full">
                                        {{ __('global_company.user_edit') }}
                                    </x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-[#5f6368]">{{ __('global_company.no_related_users') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.companies.index')" variant="surface-muted" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.companies.edit', $company)" variant="brand-primary" class="rounded-full">
                {{ __('global_company.edit') }}
            </x-ui.button>

            <form method="POST" action="{{ route('global-admin.companies.destroy', $company) }}" data-admin-delete-confirm data-admin-name="{{ $company->name }}" data-confirm-title="{{ __('global_company.confirm_delete_title') }}" data-confirm-text="{{ __('global_company.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_company.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_company.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('global_company.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
