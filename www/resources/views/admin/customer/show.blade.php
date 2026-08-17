@extends('layouts.global-admin')
@section('title', $customer->name.' | '.__('global_customer.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$customer->name"
        :subtitle="__('global_customer.details')"
        :breadcrumbs="[['label' => __('global_customer.title'), 'href' => route('global-admin.customers.index')], ['label' => $customer->name]]"
    >
        <x-slot:actions>
            <x-ui.definition-item-status
                :label="__('global_customer.status')"
                :value="$customer->is_active ? __('global_customer.active') : __('global_customer.inactive')"
                :tone="$customer->is_active ? 'success' : 'neutral'"
                inline
            />
        </x-slot:actions>
    </x-ui.page-heading>

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        <x-ui.definition-grid cols="sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.definition-item class="xl:col-span-2" :label="__('global_customer.email')">{{ $customer->email }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_customer.company')">{{ $customer->currentCompany?->name ?? __('global_customer.company_unlinked') }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('global_customer.company_id')">{{ $customer->currentCompany?->id ?? '—' }}</x-ui.definition-item>
            <x-ui.definition-item-date class="sm:col-span-2 xl:col-span-1" :label="__('global_customer.created_at')" :value="$customer->created_at" />
        </x-ui.definition-grid>

        <x-ui.panel class="mt-8" padding="p-5">
            <h2 class="font-display text-xl font-semibold text-[var(--ui-text)]">{{ __('global_customer.company_details') }}</h2>

            @if ($customer->currentCompany)
                <x-ui.definition-grid class="mt-4" cols="sm:grid-cols-2 xl:grid-cols-3">
                    <x-ui.definition-item :label="__('global_customer.company_id')">{{ $customer->currentCompany->id }}</x-ui.definition-item>
                    <x-ui.definition-item class="sm:col-span-2" :label="__('global_customer.company_name')">{{ $customer->currentCompany->name }}</x-ui.definition-item>
                    <x-ui.definition-item :label="__('global_customer.company_code')">{{ $customer->currentCompany->code }}</x-ui.definition-item>
                    <x-ui.definition-item-status :label="__('global_customer.company_status')" :value="$customer->currentCompany->is_active ? __('global_customer.active') : __('global_customer.inactive')" :tone="$customer->currentCompany->is_active ? 'success' : 'neutral'" />
                    <x-ui.definition-item-date :label="__('global_customer.company_created_at')" :value="$customer->currentCompany->created_at" />
                    <x-ui.definition-item-date :label="__('global_customer.company_updated_at')" :value="$customer->currentCompany->updated_at" />
                </x-ui.definition-grid>
            @else
                <p class="mt-4 text-sm text-[var(--ui-text-muted)]">{{ __('global_customer.company_unlinked_details') }}</p>
            @endif
        </x-ui.panel>

        <x-ui.panel class="mt-6" padding="p-5">
            <h2 class="font-display text-xl font-semibold text-[var(--ui-text)]">{{ __('global_customer.access_section') }}</h2>

            @if ($companyAccesses->isNotEmpty())
                <x-ui.table class="mt-4" :caption="__('global_customer.access_section')">
                    <thead>
                        <tr>
                            <x-ui.table.head>{{ __('global_customer.company') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_customer.access_profile') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_customer.modules') }}</x-ui.table.head>
                            <x-ui.table.head>{{ __('global_customer.current_company') }}</x-ui.table.head>
                            <x-ui.table.head align="right"><span class="sr-only">{{ __('ui.actions') }}</span></x-ui.table.head>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companyAccesses as $entry)
                            @php($company = $entry['company'])
                            @php($access = $entry['access'])
                            <x-ui.table.row>
                                <x-ui.table.cell>
                                    <a href="{{ route('global-admin.companies.show', $company) }}" class="font-medium text-[var(--ui-primary)] no-underline hover:underline">{{ $company->name }}</a>
                                    <p class="text-xs text-[var(--ui-text-muted)]">{{ $company->code }}</p>
                                </x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $access['profile'] === 'administrator' ? __('global_customer.profile_administrator') : __('global_customer.profile_custom') }}</x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">
                                    <div class="space-y-2">
                                        @foreach ($access['modules'] as $module)
                                            <div>
                                                <div class="font-medium text-[var(--ui-text)]">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</div>
                                                @php($moduleDescription = \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleDescription($module))
                                                @if ($moduleDescription !== '')
                                                    <div class="text-xs text-[var(--ui-text-muted)]">{{ $moduleDescription }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if (empty($access['modules']))
                                            <div>—</div>
                                        @endif
                                    </div>
                                </x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $entry['is_current'] ? __('global_customer.yes') : __('global_customer.no') }}</x-ui.table.cell>
                                <x-ui.table.cell align="right">
                                    <x-ui.row-actions :aria-label="__('ui.row_actions_for', ['name' => $company->name])">
                                        <x-ui.icon-button :href="route('global-admin.customers.edit', ['customer' => $customer->id, 'company_id' => $company->id])" icon="pencil" :label="__('global_customer.edit_company_access')" />
                                    </x-ui.row-actions>
                                </x-ui.table.cell>
                            </x-ui.table.row>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <p class="mt-4 text-sm text-[var(--ui-text-muted)]">{{ __('global_customer.company_unlinked_details') }}</p>
            @endif
        </x-ui.panel>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.customers.index')" variant="secondary" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.customers.edit', $customer)" variant="primary" class="rounded-full">
                <x-ui.icon name="pencil" size="sm" /> {{ __('global_customer.edit') }}
            </x-ui.button>

            <x-ui.confirm-button
                :action="route('global-admin.customers.destroy', $customer)"
                :label="__('global_customer.remove')"
                :confirm-title="__('global_customer.confirm_delete_title')"
                :confirm-text="__('global_customer.confirm_delete_text', ['name' => $customer->name])"
                :confirm-label="__('global_customer.confirm_delete_confirm')"
                :cancel-label="__('global_customer.confirm_delete_cancel')"
                class="rounded-full"
            />
        </div>
    </x-ui.panel>
</div>
@endsection