@extends('layouts.client-area')

@php($editing = $role !== null)

@section('title', ($editing ? __('rbac.update') : __('rbac.create')).' | '.__('rbac.title'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ $editing ? __('rbac.update') : __('rbac.create') }}</h1>
        </div>
        <x-ui.button :href="$editing ? route('company-access.rbac.roles.show', $role) : route('company-access.rbac.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ $editing ? route('company-access.rbac.roles.update', $role) : route('company-access.rbac.roles.store') }}" class="space-y-6">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block text-sm font-medium">
                    {{ __('rbac.role_name') }}
                    <input name="name" value="{{ old('name', $role?->name) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                </label>

                <label class="block text-sm font-medium">
                    {{ __('rbac.role_slug') }}
                    <input name="slug" value="{{ old('slug', $role?->slug) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3" required>
                </label>
            </div>

            <label class="block text-sm font-medium">
                {{ __('rbac.role_description') }}
                <input name="description" value="{{ old('description', $role?->description) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-4 py-3">
            </label>

            <div>
                <h2 class="text-base font-semibold">{{ __('rbac.permission_matrix') }}</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($permissionsByModule as $module => $permissions)
                        <fieldset class="rounded-xl border border-[#dadce0] p-4" data-module-permissions>
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <legend class="text-sm font-semibold">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</legend>
                                <button type="button" class="text-xs text-[#1a73e8]" data-select-module>{{ __('rbac.select_all_module') }}</button>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-start gap-2 rounded-lg border border-[#f1f3f4] p-2 text-sm">
                                        <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array((string) $permission->id, array_map('strval', old('permission_ids', $selectedPermissionIds)), true))>
                                        <span>
                                            <span class="block font-medium">{{ $permission->label() }}</span>
                                            <span class="block text-xs text-[#5f6368]">{{ $permission->slug }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="text-base font-semibold">{{ __('rbac.role_users') }}</h2>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($users as $user)
                        <label class="flex items-start gap-2 rounded-lg border border-[#f1f3f4] p-3 text-sm">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" @checked(in_array((string) $user->id, array_map('strval', old('user_ids', $selectedUserIds)), true))>
                            <span>
                                <span class="block font-medium">{{ $user->name }}</span>
                                <span class="block text-xs text-[#5f6368]">{{ $user->email }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4">
                <label class="flex items-start gap-2 text-sm font-medium">
                    <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval') == '1')>
                    <span>{{ __('rbac.approval_mode') }}</span>
                </label>
                <input name="approval_reason" value="{{ old('approval_reason') }}" class="mt-3 w-full rounded-xl border border-amber-300 px-3 py-2" placeholder="{{ __('rbac.approval_reason') }}">
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('company-access.rbac.roles.show', $role) : route('company-access.rbac.index')" variant="surface-muted" class="rounded-full" :full="true">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button type="submit" variant="brand-primary" class="rounded-full" :full="true">{{ $editing ? __('rbac.update') : __('rbac.create') }}</x-ui.button>
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
