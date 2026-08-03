@extends('layouts.client-area')

@section('title', __('rbac.edit_overrides').' | '.__('rbac.title'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">{{ __('rbac.edit_overrides') }}</h1>
            <p class="mt-1 text-sm text-[#5f6368]">{{ $customer->name }} · {{ $customer->email }}</p>
        </div>
        <x-ui.button :href="request('role_id') ? route('company-access.rbac.roles.show', request('role_id')) : route('company-access.rbac.roles.index')" variant="surface-muted" class="rounded-full">{{ __('ui.back') }}</x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <form method="POST" action="{{ route('company-access.rbac.users.overrides.update', $customer).'?role_id='.request('role_id') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="role_id" value="{{ request('role_id') }}">

            @foreach ($permissionsByModule as $module => $permissions)
                <div class="rounded-xl border border-[#dadce0] p-4">
                    <h2 class="text-sm font-semibold">{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($permissions as $permission)
                            @php
                                $override = $overrideMap->get($permission->id);
                                $state = $override ? ($override->is_allowed ? 'allow' : 'deny') : 'inherit';
                                $isInherited = in_array($permission->slug, $inheritedPermissions, true);
                            @endphp
                            <div class="rounded-lg border border-[#f1f3f4] p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium">{{ $permission->label() }}</p>
                                        <p class="text-xs text-[#5f6368]">{{ $permission->slug }} · {{ $isInherited ? __('rbac.inheritance') : 'Sem herança' }}</p>
                                    </div>

                                    <select name="overrides[{{ $permission->id }}][state]" class="rounded-xl border border-[#dadce0] px-3 py-2 text-sm">
                                        <option value="inherit" @selected($state === 'inherit')>{{ __('rbac.override_inherit') }}</option>
                                        <option value="allow" @selected($state === 'allow')>{{ __('rbac.override_allow') }}</option>
                                        <option value="deny" @selected($state === 'deny')>{{ __('rbac.override_deny') }}</option>
                                    </select>
                                </div>

                                <input name="overrides[{{ $permission->id }}][reason]" value="{{ old('overrides.'.$permission->id.'.reason', $override?->reason) }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2 text-sm" placeholder="{{ __('rbac.override_reason') }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <x-ui.button type="submit" variant="brand-primary" class="rounded-full">{{ __('rbac.save') }}</x-ui.button>
        </form>
    </x-ui.panel>
</div>
@endsection
