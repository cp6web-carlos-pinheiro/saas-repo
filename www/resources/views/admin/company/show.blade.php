@extends('layouts.global-admin')
@section('title', $company->name.' | '.__('global_company.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$company->name"
        :subtitle="__('global_company.details')"
        :breadcrumbs="[['label' => __('global_company.title'), 'href' => route('global-admin.companies.index')], ['label' => $company->name]]"
    >
        <x-slot:actions>
            <x-ui.definition-item-status
                :label="__('global_company.status')"
                :value="$company->is_active ? __('global_company.active') : __('global_company.inactive')"
                :tone="$company->is_active ? 'success' : 'neutral'"
                inline
            />
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        <div class="grid gap-6 md:grid-cols-2">
            <x-ui.panel padding="p-5">
                <h2 class="font-display text-xl font-semibold text-[var(--ui-text)]">{{ __('global_company.details') }}</h2>
                <x-ui.definition-grid class="mt-4" cols="sm:grid-cols-2">
                    <x-ui.definition-item :label="__('global_company.company_id')">{{ $company->id }}</x-ui.definition-item>
                    <x-ui.definition-item :label="__('global_company.name')">{{ $company->name }}</x-ui.definition-item>
                    <x-ui.definition-item :label="__('global_company.code')">{{ $company->code }}</x-ui.definition-item>
                    <x-ui.definition-item-status :label="__('global_company.status')" :value="$company->is_active ? __('global_company.active') : __('global_company.inactive')" :tone="$company->is_active ? 'success' : 'neutral'" />
                    <x-ui.definition-item-date :label="__('global_company.created_at')" :value="$company->created_at" />
                    <x-ui.definition-item-date :label="__('global_company.updated_at')" :value="$company->updated_at" />
                </x-ui.definition-grid>
            </x-ui.panel>

            <x-ui.panel padding="p-5">
                <h2 class="font-display text-xl font-semibold text-[var(--ui-text)]">{{ __('global_company.selected_plan_details') }}</h2>

                @if ($subscription !== null)
                    <x-ui.definition-grid class="mt-4" cols="sm:grid-cols-2">
                        <x-ui.definition-item :label="__('global_company.plan_label')">{{ $selectedPlan['label'] ?? __('global_company.no_active_plan') }}</x-ui.definition-item>
                        <x-ui.definition-item :label="__('global_company.plan_code')">{{ $subscription->plan_code }}</x-ui.definition-item>
                        <x-ui.definition-item-status :label="__('global_company.plan_status')" :value="$selectedPlanStatus" tone="info" />
                        <x-ui.definition-item-money :label="__('global_plan.amount_short')" :amount-cents="$selectedPlan['amount_cents'] ?? null" />
                        <x-ui.definition-item :label="__('global_company.plan_billing_cycle')">{{ $selectedPlan['billing_cycle_label'] ?? '—' }}</x-ui.definition-item>
                        <x-ui.definition-item :label="__('global_company.plan_payment_method')">{{ $selectedPlan['payment_method'] ?? '—' }}</x-ui.definition-item>
                        <x-ui.definition-item-date :label="__('global_company.plan_starts_at')" :value="$subscription->starts_at" />
                        <x-ui.definition-item-date :label="__('global_company.plan_ends_at')" :value="$subscription->ends_at" />
                    </x-ui.definition-grid>
                @else
                    <p class="mt-4 text-sm text-[var(--ui-text-muted)]">{{ __('global_company.no_selected_plan_details') }}</p>
                @endif
            </x-ui.panel>
        </div>

        <x-ui.panel class="mt-6" padding="p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-xl font-semibold text-[var(--ui-text)]">{{ __('global_company.related_users') }}</h2>
                <x-ui.button :href="route('global-admin.customers.create', ['company_id' => $company->id])" variant="primary" class="rounded-full">
                    <x-ui.icon name="plus" size="sm" /> {{ __('global_company.create_user_for_company') }}
                </x-ui.button>
            </div>

            @if ($company->users->isEmpty())
                <x-ui.empty-state class="mt-4" icon="users" :title="__('global_company.no_related_users')" />
            @else
                <x-ui.table class="mt-4" :caption="__('global_company.related_users')">
                    <thead>
                        <tr>
                            <x-ui.table.head>ID</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_company.user_name') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_company.user_email') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_company.user_status') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_company.user_email_verified') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_company.user_linked_at') }}</x-ui.table.head>
                            <x-ui.table.head align="right"><span class="sr-only">{{ __('global_company.user_actions') }}</span></x-ui.table.head>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($company->users as $user)
                            <x-ui.table.row>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $user->id }}</x-ui.table.cell>
                                <x-ui.table.cell class="font-medium text-[var(--ui-text)]">{{ $user->name }}</x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $user->email }}</x-ui.table.cell>
                                <x-ui.table.cell>
                                    <x-ui.definition-item-status
                                        :label="__('global_company.user_status')"
                                        :value="$user->is_active ? __('global_company.active') : __('global_company.inactive')"
                                        :tone="$user->is_active ? 'success' : 'neutral'"
                                        inline
                                    />
                                </x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : __('global_company.user_not_verified') }}</x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $user->pivot?->created_at?->format('d/m/Y H:i') ?? '—' }}</x-ui.table.cell>
                                <x-ui.table.cell align="right">
                                    <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $user->name])">
                                        <x-ui.icon-button :href="route('global-admin.customers.edit', ['customer' => $user->id, 'company_id' => $company->id])" icon="pencil" :label="__('global_company.user_edit')" />
                                    </x-ui.row-actions>
                                </x-ui.table.cell>
                            </x-ui.table.row>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.panel>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.companies.index')" variant="secondary" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.companies.edit', $company)" variant="primary" class="rounded-full">
                <x-ui.icon name="pencil" size="sm" /> {{ __('global_company.edit') }}
            </x-ui.button>

            <x-ui.confirm-button
                :action="route('global-admin.companies.destroy', $company)"
                :label="__('global_company.remove')"
                :confirm-title="__('global_company.confirm_delete_title')"
                :confirm-text="__('global_company.confirm_delete_text', ['name' => $company->name])"
                :confirm-label="__('global_company.confirm_delete_confirm')"
                :cancel-label="__('global_company.confirm_delete_cancel')"
                class="rounded-full"
            />
        </div>
    </x-ui.panel>
</div>
@endsection