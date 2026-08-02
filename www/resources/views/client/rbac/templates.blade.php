@extends('layouts.client-area')

@section('title', __('ui.rbac_templates').' | '.__('rbac.title'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div>
        <h1 class="font-display text-3xl font-bold">{{ __('ui.rbac_templates') }}</h1>
        <p class="mt-2 text-sm text-[#5f6368]">{{ __('rbac.templates_seeded_note') }}</p>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="mt-6 space-y-5">
        @foreach ($templates as $template)
            @php
                $application = $templateApplications->get($template->id);
            @endphp
            <x-ui.panel class="border-[#dadce0] shadow-none" padding="p-5 md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $template->name }}</h2>
                        <p class="text-sm text-[#5f6368]">{{ __('rbac.template_version') }} {{ $template->current_version }} · {{ $template->module_focus ?: '-' }}</p>
                        @if ($application)
                            <p class="text-xs text-emerald-700">Aplicado: v{{ $application->applied_version }}</p>
                        @endif
                    </div>

                    @if ($canManageTemplates)
                        <form method="POST" action="{{ route('company-access.rbac.templates.apply', $template) }}">
                            @csrf
                            <input type="hidden" name="version" value="{{ $template->current_version }}">
                            <x-ui.button type="submit" variant="surface-muted" class="rounded-full">{{ __('rbac.template_apply') }}</x-ui.button>
                        </form>
                    @endif
                </div>

                @if ($canManageTemplates)
                    <form method="POST" action="{{ route('company-access.rbac.templates.versions.store', $template) }}" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block text-sm font-medium">
                                {{ __('rbac.template_publish') }}
                                <input name="display_name" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2" required>
                            </label>

                            <label class="block text-sm font-medium">
                                {{ __('rbac.approval_reason') }}
                                <input name="notes" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2">
                            </label>
                        </div>

                        <label class="block text-sm font-medium">
                            {{ __('rbac.template_permissions') }}
                            <select name="permission_ids[]" multiple class="mt-2 h-36 w-full rounded-xl border border-[#dadce0] px-3 py-2" required>
                                @foreach (\App\Modules\Identity\Infrastructure\Persistence\Models\Permission::query()->orderBy('module')->orderBy('name')->get() as $permission)
                                    <option value="{{ $permission->id }}">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($permission->module) }} · {{ $permission->label() }}</option>
                                @endforeach
                            </select>
                        </label>

                        <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('rbac.save') }}</x-ui.button>
                    </form>
                @endif
            </x-ui.panel>
        @endforeach
    </div>
</div>
@endsection
