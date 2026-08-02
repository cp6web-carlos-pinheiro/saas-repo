@extends('layouts.client-area')

@section('title', __('ui.rbac_history').' | '.__('rbac.title'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div>
        <h1 class="font-display text-3xl font-bold">{{ __('ui.rbac_history') }}</h1>
        <p class="mt-2 text-sm text-[#5f6368]">{{ __('rbac.history_filters') }}</p>
    </div>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <label class="block text-sm font-medium">
                {{ __('rbac.filter_actor') }}
                <select name="actor_user_id" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2">
                    <option value="0">-</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((int) $auditFilters['actor_user_id'] === (int) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-medium">
                {{ __('rbac.filter_module') }}
                <select name="module" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2">
                    <option value="">-</option>
                    @foreach ($availableModules as $module)
                        <option value="{{ $module }}" @selected($auditFilters['module'] === $module)>{{ \App\Modules\Identity\Infrastructure\Persistence\Models\Permission::moduleLabel($module) }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-medium">
                {{ __('rbac.filter_from') }}
                <input type="date" name="from" value="{{ $auditFilters['from'] }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2">
            </label>

            <label class="block text-sm font-medium">
                {{ __('rbac.filter_to') }}
                <input type="date" name="to" value="{{ $auditFilters['to'] }}" class="mt-2 w-full rounded-xl border border-[#dadce0] px-3 py-2">
            </label>

            <div class="md:col-span-2 xl:col-span-4">
                <x-ui.button type="submit" variant="surface-muted" class="rounded-full">{{ __('rbac.save') }}</x-ui.button>
            </div>
        </form>

        <div class="mt-6 space-y-3">
            @forelse ($history as $event)
                <div class="rounded-xl border border-[#dadce0] p-4">
                    <p class="font-semibold">{{ $event->event }}</p>
                    <p class="mt-1 text-sm text-[#5f6368]">{{ optional($event->occurred_at)->format('d/m/Y H:i') }} · user #{{ $event->user_id ?: '-' }}</p>
                </div>
            @empty
                <p class="text-sm text-[#5f6368]">{{ __('rbac.empty_history') }}</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $history->links() }}</div>
    </x-ui.panel>
</div>
@endsection
