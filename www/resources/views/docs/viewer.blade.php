@extends('layouts.public')

@section('title', $currentTitle.' | '.__('ui.documentation').' | '.__('ui.app_name'))

@section('content')
    <div class="docs-layout">
        <x-ui.sidebar variant="docs">
            <x-slot:header>
                <a class="docs-back-link" href="{{ route('dashboard.industrial') }}">&larr; {{ __('ui.back_to_dashboard') }}</a>
                <h1>{{ __('ui.documentation') }}</h1>
                <p>{{ __('ui.documentation_subtitle') }}</p>
            </x-slot:header>

                <x-ui.menu variant="docs" :aria-label="__('ui.documentation')">
                    @foreach ($documents as $document)
                        <x-ui.menu-item
                            variant="docs"
                            :active="$currentScope === 'root' && $document['file'] === $currentFile"
                            :href="route('docs.show', ['file' => $document['file']])"
                        >
                            {{ $document['label'] }}
                        </x-ui.menu-item>
                    @endforeach
                </x-ui.menu>

                @if (! empty($devDocuments))
                    <details class="docs-folder" {{ $currentScope === 'dev' ? 'open' : '' }}>
                        <summary>Desenvolvimento</summary>

                        <x-ui.menu variant="docs" class="docs-list-nested" aria-label="doc dev">
                            @foreach ($devDocuments as $document)
                                <x-ui.menu-item
                                    variant="docs"
                                    :active="$currentScope === 'dev' && $document['file'] === $currentFile"
                                    :href="route('docs.dev.show', ['file' => $document['file']])"
                                >
                                    {{ $document['label'] }}
                                </x-ui.menu-item>
                            @endforeach
                        </x-ui.menu>
                    </details>
                @endif
        </x-ui.sidebar>

        <main class="docs-content-wrap">
            <x-ui.breadcrumb :items="[
                ['label' => __('ui.app_name'), 'href' => route('dashboard.industrial')],
                ['label' => __('ui.documentation'), 'href' => route('docs.index')],
                ['label' => $currentTitle],
            ]" />

            <article class="docs-article markdown-body">
                {!! $contentHtml !!}
            </article>
        </main>
    </div>
@endsection
