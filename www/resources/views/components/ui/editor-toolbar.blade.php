@props([
    'ariaLabel' => 'Formatação',
])

{{--
    Toolbar de editor (usada pelo editor de tutoriais e demais conteúdos ricos). Agrupe
    x-ui.icon-button dentro; use <span class="ui-editor-toolbar-divider"></span> entre grupos.
--}}
<div {{ $attributes->class(['ui-editor-toolbar']) }} role="toolbar" aria-label="{{ $ariaLabel }}">
    {{ $slot }}
</div>