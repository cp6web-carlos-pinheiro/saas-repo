@props([
    'action',
    'method' => 'DELETE',
    'label' => 'Excluir',
    'icon' => 'trash',
    'confirmTitle' => 'Confirmar exclusão',
    'confirmText' => 'Esta ação não pode ser desfeita.',
    'confirmLabel' => 'Excluir',
    'cancelLabel' => 'Cancelar',
    'iconOnly' => false,
])

{{--
    Ação destrutiva padrão: formulário com o verbo HTTP correto e uma confirmação acessível
    (data-ui-confirm, tratado em resources/js/app.js com SweetAlert2 + foco restaurado ao
    fechar). Use iconOnly em ações de linha de tabela e o padrão completo em telas de detalhe.
--}}
<form
    method="POST"
    action="{{ $action }}"
    data-ui-confirm="{{ $confirmTitle }}"
    data-ui-confirm-text="{{ $confirmText }}"
    data-ui-confirm-confirm="{{ $confirmLabel }}"
    data-ui-confirm-cancel="{{ $cancelLabel }}"
    class="inline-flex"
>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @if ($iconOnly)
        <x-ui.icon-button type="submit" variant="ghost" :icon="$icon" :label="$label" {{ $attributes }} />
    @else
        <x-ui.button type="submit" variant="danger" {{ $attributes }}>
            <x-ui.icon :name="$icon" size="sm" />
            {{ $label }}
        </x-ui.button>
    @endif
</form>