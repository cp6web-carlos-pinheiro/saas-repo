@extends('layouts.global-admin')
@php($editing = $tutorial !== null)
@section('title', ($editing ? __('global_tutorial.edit') : __('global_tutorial.create')).' | '.__('global_tutorial.title'))
@section('admin-content-container-class', 'w-full')
@section('admin-content')
<div class="w-full">
    <x-ui.page-heading
        :title="$editing ? __('global_tutorial.edit') : __('global_tutorial.create')"
        :breadcrumbs="[['label' => __('global_tutorial.title'), 'href' => route('global-admin.tutorials.index')], ['label' => $editing ? __('global_tutorial.edit') : __('global_tutorial.create')]]"
    />

    <x-ui.panel class="mt-6" padding="p-6 md:p-8">
        @if ($errors->any())
            <x-ui.alert class="mb-5" variant="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ $editing ? route('global-admin.tutorials.update', $tutorial) : route('global-admin.tutorials.store') }}" class="space-y-5">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-ui.field :label="__('global_tutorial.route_name')" for="route_name" required :error="$errors->first('route_name')">
                <x-ui.input id="route_name" name="route_name" :value="old('route_name', $tutorial?->route_name)" required />
            </x-ui.field>

            <x-ui.field :label="__('global_tutorial.content_html')" :error="$errors->first('content_html')">
                <div class="ui-editor-frame" data-global-tutorial-html-editor @if($errors->has('content_html')) aria-invalid="true" @endif>
                    <x-ui.editor-toolbar :aria-label="__('global_tutorial.content_html')">
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="formatBlock" data-editor-value="P" title="{{ __('global_tutorial.toolbar.paragraph') }}">P</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="formatBlock" data-editor-value="H2" title="{{ __('global_tutorial.toolbar.heading') }}">H2</button>
                        <span class="ui-editor-toolbar-divider"></span>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="bold" title="{{ __('global_tutorial.toolbar.bold') }}"><strong>B</strong></button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="italic" title="{{ __('global_tutorial.toolbar.italic') }}"><em>I</em></button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="underline" title="{{ __('global_tutorial.toolbar.underline') }}"><u>U</u></button>
                        <span class="ui-editor-toolbar-divider"></span>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="insertUnorderedList" title="{{ __('global_tutorial.toolbar.list') }}">• {{ __('global_tutorial.toolbar.list') }}</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="createLink" title="{{ __('global_tutorial.toolbar.link') }}">{{ __('global_tutorial.toolbar.link') }}</button>
                        <button type="button" class="ui-editor-toolbar-button" data-editor-command="removeFormat" title="{{ __('global_tutorial.toolbar.clear_formatting') }}">{{ __('global_tutorial.toolbar.clear') }}</button>
                    </x-ui.editor-toolbar>

                    <div
                        class="ui-editor-surface"
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
            </x-ui.field>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-ui.button :href="$editing ? route('global-admin.tutorials.show', $tutorial) : route('global-admin.tutorials.index')" variant="secondary" class="rounded-full" :full="true">
                    {{ __('ui.back') }}
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" :full="true" class="rounded-full">
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
