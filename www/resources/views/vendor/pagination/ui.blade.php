{{--
    View de paginação padrão do Layout System. Registrada via Paginator::defaultView() /
    Paginator::defaultSimpleView() em AppServiceProvider, então qualquer {{ $items->links() }}
    já existente passa a usar este componente sem alterar controllers, filtros ou query string.
--}}
@if ($paginator->hasPages())
    <nav class="ui-pagination" role="navigation" aria-label="{{ __('ui.pagination_navigation') }}">
        <p class="text-xs">
            {!! __('ui.pagination_showing', [
                'first' => $paginator->firstItem() ?? 0,
                'last' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ]) !!}
        </p>

        <div class="ui-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="ui-pagination-link" aria-disabled="true" aria-label="{{ __('ui.pagination_previous') }}">
                    <x-ui.icon name="chevron-left" size="sm" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="ui-pagination-link" rel="prev" aria-label="{{ __('ui.pagination_previous') }}">
                    <x-ui.icon name="chevron-left" size="sm" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="ui-pagination-link" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="ui-pagination-link is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="ui-pagination-link">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="ui-pagination-link" rel="next" aria-label="{{ __('ui.pagination_next') }}">
                    <x-ui.icon name="chevron-left" size="sm" class="rotate-180" />
                </a>
            @else
                <span class="ui-pagination-link" aria-disabled="true" aria-label="{{ __('ui.pagination_next') }}">
                    <x-ui.icon name="chevron-left" size="sm" class="rotate-180" />
                </span>
            @endif
        </div>
    </nav>
@endif