@props([
    'searchName' => 'search',
    'searchValue' => null,
    'searchPlaceholder' => 'Buscar...',
    'searchLabel' => null,
])

{{--
    Barra de filtros padrão para listagens: campo de busca (GET, envia com o formulário da
    página) + slot para filtros adicionais (selects, datas) + slot para ações (novo registro,
    exportar). Não substitui filtros GET existentes — é só o invólucro visual compartilhado.
--}}
<div {{ $attributes->class(['ui-filter-bar']) }}>
    @if ($searchName)
        <div class="ui-filter-bar-search">
            <x-ui.input
                type="search"
                :name="$searchName"
                :value="$searchValue"
                icon="search"
                :placeholder="$searchPlaceholder"
                :aria-label="$searchLabel ?? $searchPlaceholder"
            />
        </div>
    @endif

    @isset($fields)
        <div class="ui-filter-bar-fields">{{ $fields }}</div>
    @endisset

    @isset($actions)
        <div class="ui-filter-bar-actions">{{ $actions }}</div>
    @endisset
</div>