@extends('layouts.global-admin')

@section('title', $currentTitle.' | '.__('ui.documentation').' | '.__('ui.app_name'))
@section('admin-content-container-class', 'w-full')

@section('admin-content')
    @php($indexRouteName = $indexRouteName ?? 'global-admin.docs.index')
    @php($showRouteName = $showRouteName ?? 'global-admin.docs.show')
    @php($showDevRouteName = $showDevRouteName ?? 'global-admin.docs.dev.show')
    @php($backUrl = $backUrl ?? route('global-admin.home'))

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <section class="min-w-0">
            <x-ui.panel padding="p-6 md:p-8">
                <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="font-display text-3xl font-bold text-[var(--ui-text)]">{{ $currentTitle }}</h1>
                        <p class="mt-2 text-sm text-[var(--ui-text-muted)]">{{ __('ui.documentation_subtitle') }}</p>
                    </div>
                    <x-ui.button :href="$backUrl" variant="secondary" class="rounded-full">{{ __('ui.back_to_dashboard') }}</x-ui.button>
                </div>

                <article class="docs-article markdown-body border-0 p-0 text-sm">
                    {!! $contentHtml !!}
                </article>
            </x-ui.panel>
        </section>

        <aside class="xl:sticky xl:top-8 xl:self-start">
            <x-ui.panel padding="p-5">
                <h2 class="font-display text-lg font-semibold text-[var(--ui-text)]">{{ __('ui.documentation') }}</h2>

                <x-ui.menu variant="docs" :aria-label="__('ui.documentation')" class="mt-4">
                    @foreach ($documents as $document)
                        <x-ui.menu-item
                            variant="docs"
                            :active="$currentScope === 'root' && $document['file'] === $currentFile"
                            :href="route($showRouteName, ['file' => $document['file']])"
                        >
                            {{ $document['label'] }}
                        </x-ui.menu-item>
                    @endforeach
                </x-ui.menu>
            </x-ui.panel>
        </aside>
    </div>
@endsection