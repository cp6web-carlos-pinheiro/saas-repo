@extends('layouts.client-area')

@section('title', __('ui.rbac_approvals').' | '.__('rbac.title'))
@section('client-page-title', __('rbac.title'))

@section('client-content')
<div class="w-full p-5 md:p-8">
    <div>
        <h1 class="font-display text-3xl font-bold">{{ __('ui.rbac_approvals') }}</h1>
        <p class="mt-2 text-sm text-[#5f6368]">{{ __('rbac.pending_requests') }}</p>
    </div>

    @if (session('status'))
        <x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-5 md:p-6">
        <div class="space-y-4">
            @forelse ($pendingChangeRequests as $changeRequest)
                <div class="rounded-xl border border-[#dadce0] p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">#{{ $changeRequest->id }} · {{ $changeRequest->change_type }}</p>
                            <p class="mt-1 text-sm text-[#5f6368]">{{ $changeRequest->reason ?: '-' }}</p>
                            <p class="mt-1 text-xs text-[#5f6368]">{{ optional($changeRequest->created_at)->format('d/m/Y H:i') }}</p>
                        </div>

                        @if ($canApproveChanges)
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('company-access.rbac.change-requests.approve', $changeRequest) }}">
                                    @csrf
                                    <input type="hidden" name="review_notes" value="Aprovado via tela de aprovações">
                                    <x-ui.button type="submit" variant="brand-primary" class="rounded-full text-xs">{{ __('rbac.approve') }}</x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('company-access.rbac.change-requests.reject', $changeRequest) }}">
                                    @csrf
                                    <input type="hidden" name="review_notes" value="Rejeitado via tela de aprovações">
                                    <x-ui.button type="submit" variant="danger-outline" class="rounded-full text-xs">{{ __('rbac.reject') }}</x-ui.button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-[#5f6368]">{{ __('rbac.empty_requests') }}</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $pendingChangeRequests->links() }}</div>
    </x-ui.panel>
</div>
@endsection
