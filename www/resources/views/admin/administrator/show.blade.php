@extends('layouts.global-admin')
@section('title', $administrator->name.' | '.__('global_admin.modules.administrators'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_admin.modules.administrators'), 'href' => route('global-admin.administrators.index')], ['label' => $administrator->name]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-[#5f6368]">{{ __('global_admin.details') }}</p>
                <h1 class="font-display text-3xl font-bold">{{ $administrator->name }}</h1>
            </div>
            <span class="rounded-full px-3 py-1 text-xs {{ $administrator->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $administrator->is_active ? __('global_admin.active') : __('global_admin.inactive') }}
            </span>
        </div>

        <dl class="mt-8 divide-y divide-[#dadce0]">
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_admin.email') }}</dt>
                <dd class="font-medium">{{ $administrator->email }}</dd>
            </div>
            <div class="flex justify-between gap-4 py-4">
                <dt class="text-[#5f6368]">{{ __('global_admin.created_at') }}</dt>
                <dd class="font-medium">{{ $administrator->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-ui.button :href="route('global-admin.administrators.index')" variant="surface-muted" class="rounded-full">
                {{ __('ui.back') }}
            </x-ui.button>

            <x-ui.button :href="route('global-admin.administrators.edit', $administrator)" variant="brand-primary" class="rounded-full">
                {{ __('global_admin.edit') }}
            </x-ui.button>

            <form method="POST" action="{{ route('global-admin.administrators.destroy', $administrator) }}" data-admin-delete-confirm data-admin-name="{{ $administrator->name }}" data-confirm-title="{{ __('global_admin.confirm_delete_title') }}" data-confirm-text="{{ __('global_admin.confirm_delete_text') }}" data-confirm-confirm="{{ __('global_admin.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('global_admin.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" class="rounded-full">{{ __('global_admin.remove') }}</x-ui.button>
            </form>
        </div>
    </x-ui.panel>
</div>
@endsection
