@props([
    'ariaLabel' => 'Ações do registro',
])

{{--
    Agrupa os ícones de ação (ver, editar, excluir) no final de uma linha de tabela, com
    espaçamento e alinhamento consistentes. Passe x-ui.icon-button ou x-ui.confirm-button
    (iconOnly) como filhos.
--}}
<div {{ $attributes->class(['ui-row-actions'])->merge(['role' => 'group', 'aria-label' => $ariaLabel]) }}>
    {{ $slot }}
</div>