@extends('layouts.global-admin')
@section('title', $customer->name.' | '.__('global_customer.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_customer.title'), 'href' => route('global-admin.customers.index')], ['label' => $customer->name]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-[#5f6368]">{{ __('global_customer.details') }}</p>
                <h1 class="font-display text-3xl font-bold">{{ $customer->name }}</h1>
            </div>
            <span class="rounded-full px-3 py-1 text-xs {{ $customer->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $customer->is_active ? __('global_customer.active') : __('global_customer.inactive') }}
            </span>
        </div>

        <dl class="mt-8 divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.email') }}</dt>
                <dd class="font-medium">{{ $customer->email }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.company') }}</dt>
                <dd class="font-medium">{{ $customer->currentCompany?->name ?? __('global_customer.company_unlinked') }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.company_id') }}</dt>
                <dd class="font-medium">{{ $customer->currentCompany?->id ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_customer.created_at') }}</dt>
                <dd class="font-medium">{{ $customer->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 rounded-2xl border border-[#dadce0] bg-[#f8fafd] p-5">
            <h2 class="font-display text-xl font-semibold">{{ __('global_customer.company_details') }}</h2>

            @if ($customer->currentCompany)
                <dl class="mt-4 divide-y divide-[#dadce0]">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_id') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->id }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_name') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_code') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_status') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->is_active ? __('global_customer.active') : __('global_customer.inactive') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_created_at') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-[#5f6368]">{{ __('global_customer.company_updated_at') }}</dt>
                        <dd class="font-medium">{{ $customer->currentCompany->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 text-sm text-[#5f6368]">{{ __('global_customer.company_unlinked_details') }}</p>
            @endif
        </div>

        <div class="mt-8 rounded-2xl border border-[#dadce0] bg-[#f8fafd] p-5">
            <h2 class="font-display text-xl font-semibold">{{ __('global_customer.access_section') }}</h2>

            @if ($companyAccesses->isNotEmpty())
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#dadce0] text-left text-[#5f6368]">
                                <th class="px-3 py-3">{{ __('global_customer.company') }}</th>
                                <th class="px-3 py-3">{{ __('global_customer.access_profile') }}</th>
                                <th class="px-3 py-3">{{ __('global_customer.modules') }}</th>
                                <th class="px-3 py-3">{{ __('global_customer.current_company') }}</th>
                                <th class="px-3 py-3">{{ __('global_customer.default_company') }}</th>
                                <th class="px-3 py-3">{{ __('global_customer.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companyAccesses as $entry)
                                @php($company = $entry['company'])
                                @php($access = $entry['access'])
                                <tr class="border-b border-[#e8eaed]">
                                    <td class="px-3 py-3">
                                        <a href="{{ route('global-admin.companies.show', $company) }}" class="font-medium text-[#174ea6] no-underline hover:underline">{{ $company->name }}</a>
                                        <p class="text-xs text-[#5f6368]">{{ $company->code }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-[#5f6368]">{{ $access['profile'] === 'administrator' ? __('global_customer.profile_administrator') : __('global_customer.profile_custom') }}</td>
                                    <td class="px-3 py-3 text-[#5f6368]">
                                        <div class="space-y-2">
                                            @foreach ($access['modules'] as $module)
                                                <div>
                                                    <div class="font-medium text-[#202124]">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</div>
                                                    @php($moduleDescription = \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleDescription($module))
                                                    @if ($moduleDescription !== '')
                                                        <div class="text-xs text-[#5f6368]">{{ $moduleDescription }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if (empty($access['modules']))
                                                <div>—</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-[#5f6368]">{{ $entry['is_current'] ? __('global_customer.yes') : __('global_customer.no') }}</td>
                                    <td class="px-3 py-3 text-[#5f6368]">{{ $entry['is_default'] ? __('global_customer.yes') : __('global_customer.no') }}</td>
                                    <td class="px-3 py-3">
                                        <x-ui.button :href="route('global-admin.customers.edit', ['customer' => $customer->id, 'company_id' => $company->id])" variant="surface-muted" size="sm" class="rounded-full">
                                            {{ __('global_customer.edit_company_access') }}
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-4 text-sm text-[#5f6368]">{{ __('global_customer.company_unlinked_details') }}</p>
            @endif
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.customers.index')" variant="surface-muted" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.customers.edit', $customer)" variant="brand-primary" class="rounded-full">
                {{ __('global_customer.edit') }}
            </x-ui.button>

            <form method="POST" action="{{ route('global-admin.customers.destroy', $customer) }}" data-admin-delete-confirm data-admin-name="{{ $customer->name }}" data-confirm-title="{{ __('global_customer.confirm_delete_title') }}" data-confirm-text="{{ __('global_customer.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_customer.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_customer.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('global_customer.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
