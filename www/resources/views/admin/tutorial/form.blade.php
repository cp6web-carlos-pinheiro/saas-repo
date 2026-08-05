@extends('layouts.global-admin')
@php($editing = $tutorial !== null)
@section('title', ($editing ? __('global_tutorial.edit') : __('global_tutorial.create')).' | '.__('global_tutorial.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.breadcrumb :items="[['label' => __('global_tutorial.title'), 'href' => route('global-admin.tutorials.index')], ['label' => $editing ? __('global_tutorial.edit') : __('global_tutorial.create')]]"/>

    <x-ui.panel class="mt-6 border-[#dadce0] shadow-none" padding="p-6 md:p-8">
        <h1 class="font-display text-3xl font-bold">{{ $editing ? __('global_tutorial.edit') : __('global_tutorial.create') }}</h1>

        @if ($errors->any())
            <x-ui.alert class="mt-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.tutorials.update', $tutorial) : route('global-admin.tutorials.store') }}" class="mt-6 space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <label class="block text-sm font-medium">
                {{ __('global_tutorial.route_name') }}
                <x-ui.input name="route_name" :value="old('route_name', $tutorial?->route_name)" required @class(['mt-2', 'border-red-500' => $errors->has('route_name'), 'border-[#dadce0]' => ! $errors->has('route_name')]) />
                @error('route_name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-medium">
                {{ __('global_tutorial.content_html') }}
                <div @class(['ind-html-editor mt-2 min-h-[26rem]', 'border-red-500' => $errors->has('content_html'), 'border-[#dadce0]' => ! $errors->has('content_html')]) data-global-tutorial-html-editor>
                    <div class="ind-html-editor-toolbar" role="toolbar" aria-label="{{ __('global_tutorial.content_html') }}">
                        <button type="button" class="ind-html-editor-button" data-editor-command="formatBlock" data-editor-value="P" title="Parágrafo">P</button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="formatBlock" data-editor-value="H2" title="Título">H2</button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="bold" title="Negrito"><strong>B</strong></button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="italic" title="Itálico"><em>I</em></button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="underline" title="Sublinhado"><u>U</u></button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="insertUnorderedList" title="Lista">• Lista</button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="createLink" title="Link">Link</button>
                        <button type="button" class="ind-html-editor-button" data-editor-command="removeFormat" title="Limpar formatação">Limpar</button>
                    </div>

                    <div
                        class="ind-html-editor-surface"
                        contenteditable="true"
                        data-editor-surface
                        aria-label="{{ __('global_tutorial.content_html') }}"
                    >{!! old('content_html', $tutorial?->content_html ?? '') !!}</div>

                    <x-ui.textarea
                        name="content_html"
                        rows="16"
                        class="hidden"
                        data-editor-source
                    >{!! old('content_html', $tutorial?->content_html) !!}</x-ui.textarea>
                </div>
                @error('content_html')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.tutorials.show', $tutorial) : route('global-admin.tutorials.index')" variant="surface-muted" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="brand-primary" :full="true" class="rounded-full">
                    {{ $editing ? __('global_tutorial.save') : __('global_tutorial.create') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.panel>
</div>
@endsection

@section('scripts')
    <script>
        (() => {
            const htmlEditor = document.querySelector('[data-global-tutorial-html-editor]');

            if (! htmlEditor) {
                return;
            }

            const surface = htmlEditor.querySelector('[data-editor-surface]');
            const source = htmlEditor.querySelector('[data-editor-source]');
            const toolbarButtons = htmlEditor.querySelectorAll('[data-editor-command]');
            const editorForm = htmlEditor.closest('form');

            const syncEditorToSource = () => {
                if (surface instanceof HTMLElement && source instanceof HTMLTextAreaElement) {
                    source.value = surface.innerHTML;
                }
            };

            for (const button of toolbarButtons) {
                button.addEventListener('click', () => {
                    if (! (surface instanceof HTMLElement)) {
                        return;
                    }

                    surface.focus();

                    const command = button.getAttribute('data-editor-command');
                    const value = button.getAttribute('data-editor-value');

                    if (! command) {
                        return;
                    }

                    if (command === 'createLink') {
                        const link = window.prompt('Informe a URL do link', 'https://');

                        if (link && link.trim() !== '') {
                            document.execCommand('createLink', false, link.trim());
                        }
                    } else {
                        document.execCommand(command, false, value ?? undefined);
                    }

                    syncEditorToSource();
                });
            }

            surface?.addEventListener('input', syncEditorToSource);
            syncEditorToSource();
            editorForm?.addEventListener('submit', syncEditorToSource);
        })();
    </script>
@endsection
