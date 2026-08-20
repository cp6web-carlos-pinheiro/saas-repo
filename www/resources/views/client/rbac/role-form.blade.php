@extends('layouts.client-area')

@php($editing = $role !== null)

@section('title', __('ui.module_users').' | '.__('ui.rbac_roles'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('rbac.update') : __('rbac.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('company-access.rbac.roles.show', $role) : route('company-access.rbac.roles.index')" variant="secondary" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[var(--ui-border)] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('company-access.rbac.roles.update', $role) : route('company-access.rbac.roles.store') }}" class="space-y-6">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('rbac.role_name') }}
                    <x-ui.input name="name" :value="old('name', $role?->name)" class="mt-2" required />
                </label>

                <label class="block text-sm font-medium">
                    {{ __('rbac.role_slug') }}
                    <x-ui.input name="slug" :value="old('slug', $role?->slug)" class="mt-2" required />
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('rbac.role_description') }}
                <x-ui.input name="description" :value="old('description', $role?->description)" class="mt-2" />
            </label>

            <div>
                <h2 class="text-base font-semibold">{{ __('rbac.permission_matrix') }}</h2>
                <div class="mt-4 space-y-4">
                    @php($checkedPermissionIds = array_map('strval', old('permission_ids', $selectedPermissionIds)))
                    @foreach ($permissionsByModule as $module => $permissions)
                        <fieldset class="rounded-xl border border-[var(--ui-border)] p-4" data-module-permissions>
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <legend class="text-sm font-semibold">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</legend>
                                <x-ui.button type="button" variant="ghost" size="sm" data-select-module>{{ __('rbac.select_all_module') }}</x-ui.button>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($permissions->unique('slug')->values() as $permission)
                                    @php($permissionLabel = $permission->label())
                                    @php($permissionDescription = $permission->description())
                                    <div class="rounded-lg border border-[var(--ui-border)] p-2" title="{{ $permission->slug }}">
                                        <x-ui.checkbox id="permission_{{ $permission->id }}" name="permission_ids[]" :value="$permission->id" :checked="in_array((string) $permission->id, $checkedPermissionIds, true)" :description="$permissionDescription !== $permissionLabel ? $permissionDescription : $permission->slug">{{ $permissionLabel }}</x-ui.checkbox>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('company-access.rbac.roles.show', $role) : route('company-access.rbac.roles.index')" variant="secondary" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="rounded-full" :full="true">{{ $editing ? __('rbac.update') : __('rbac.create') }}</x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>

<script>
document.querySelectorAll('[data-select-module]').forEach(function (button) {
    button.addEventListener('click', function () {
        const fieldset = button.closest('[data-module-permissions]');
        if (!fieldset) return;
        fieldset.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = true;
        });
    });
});
</script>
@endsection
