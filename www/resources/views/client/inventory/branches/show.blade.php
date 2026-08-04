@extends('layouts.client-area')

@section('title', __('ui.module_inventory').' | '.__('ui.inventory_branches'))
@section('client-page-title', __('branch.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-3xl font-bold">{{ $branch->name }}</h1>
        <div class="flex flex-wrap gap-3">
            <x-ui.button :href="route('inventory.branches.index')" variant="material-back" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
            <x-ui.button :href="route('inventory.branches.edit', $branch)" variant="material-edit" class="rounded-full">{{ __('branch.edit') }}</x-ui.button>
            <form method="POST" action="{{ route('inventory.branches.destroy', $branch) }}" data-admin-delete-confirm data-admin-name="{{ $branch->name }}" data-confirm-title="{{ __('branch.confirm_delete_title') }}" data-confirm-text="{{ __('branch.confirm_delete_text') }}" data-confirm-confirm="{{ __('branch.confirm_delete_confirm') }}" data-confirm-cancel="{{ __('branch.confirm_delete_cancel') }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="material-remove" class="rounded-full">{{ __('branch.remove') }}</x-ui.button>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <x-ui.definition-grid>
            <x-ui.definition-item :label="__('branch.reference')">#{{ $branch->id }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('branch.name')">{{ $branch->name }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('branch.code')">{{ $branch->code }}</x-ui.definition-item>
            <x-ui.definition-item :label="__('branch.plants_count')">{{ $branch->plants_count ?? 0 }}</x-ui.definition-item>
            <x-ui.definition-item-status :label="__('branch.status')" :value="$branch->is_active ? __('branch.active') : __('branch.inactive')" :tone="$branch->is_active ? 'success' : 'neutral'" />
            <x-ui.definition-item-date :label="__('branch.created_at')" :value="$branch->created_at" />
        </x-ui.definition-grid>
    </x-ui.panel>
</div>
@endsection
