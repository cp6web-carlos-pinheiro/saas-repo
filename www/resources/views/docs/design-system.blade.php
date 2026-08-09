@extends('layouts.design-system')

@section('title', 'Layout System | '.__('ui.app_name'))

@section('design-system-content')
    @php
        $colors = [
            ['name' => 'Canvas', 'token' => '--ui-canvas', 'light' => '#F3F6FA', 'dark' => '#080E18'],
            ['name' => 'Surface', 'token' => '--ui-surface', 'light' => '#FFFFFF', 'dark' => '#111927'],
            ['name' => 'Primary', 'token' => '--ui-primary', 'light' => '#2563EB', 'dark' => '#60A5FA'],
            ['name' => 'Text', 'token' => '--ui-text', 'light' => '#172033', 'dark' => '#F4F7FB'],
            ['name' => 'Success', 'token' => '--ui-success', 'light' => '#15803D', 'dark' => '#4ADE80'],
            ['name' => 'Warning', 'token' => '--ui-warning', 'light' => '#FBBF24', 'dark' => '#FACC15'],
            ['name' => 'Danger', 'token' => '--ui-danger', 'light' => '#DC2626', 'dark' => '#F87171'],
            ['name' => 'Border', 'token' => '--ui-border', 'light' => '#D8E0EA', 'dark' => '#2B3A4F'],
        ];

        $boatIconGroups = [
            [
                'title' => 'Embarcação e navegação',
                'description' => 'Produto acabado, direção, orientação e desempenho na água.',
                'icons' => [
                    ['name' => 'ship', 'label' => 'Embarcação'],
                    ['name' => 'speedboat', 'label' => 'Lancha'],
                    ['name' => 'sailboat', 'label' => 'Veleiro'],
                    ['name' => 'anchor', 'label' => 'Ancoragem'],
                    ['name' => 'steering-wheel', 'label' => 'Comando e direção'],
                    ['name' => 'compass', 'label' => 'Orientação'],
                    ['name' => 'navigation', 'label' => 'Navegação'],
                    ['name' => 'ripple', 'label' => 'Hidrodinâmica'],
                ],
            ],
            [
                'title' => 'Fabricação e montagem',
                'description' => 'Operações de oficina, acabamento e validação das dimensões.',
                'icons' => [
                    ['name' => 'tools', 'label' => 'Ferramentas'],
                    ['name' => 'hammer', 'label' => 'Montagem'],
                    ['name' => 'hammer-drill', 'label' => 'Furação'],
                    ['name' => 'paint', 'label' => 'Pintura'],
                    ['name' => 'dimensions', 'label' => 'Controle dimensional'],
                ],
            ],
            [
                'title' => 'Movimentação e logística',
                'description' => 'Içamento de estruturas e abastecimento da linha de produção.',
                'icons' => [
                    ['name' => 'crane', 'label' => 'Içamento'],
                    ['name' => 'forklift', 'label' => 'Movimentação interna'],
                ],
            ],
            [
                'title' => 'Sistemas de bordo',
                'description' => 'Propulsão, instrumentação, combustível e instalação elétrica.',
                'icons' => [
                    ['name' => 'propeller', 'label' => 'Propulsão'],
                    ['name' => 'engine', 'label' => 'Motorização'],
                    ['name' => 'gauge', 'label' => 'Instrumentação'],
                    ['name' => 'gas-station', 'label' => 'Combustível'],
                    ['name' => 'battery', 'label' => 'Baterias'],
                    ['name' => 'bolt', 'label' => 'Instalação elétrica'],
                ],
            ],
            [
                'title' => 'Qualidade e segurança',
                'description' => 'Inspeção, conformidade, certificação e proteção no estaleiro.',
                'icons' => [
                    ['name' => 'clipboard-check', 'label' => 'Inspeção'],
                    ['name' => 'shield-check', 'label' => 'Conformidade'],
                    ['name' => 'certificate', 'label' => 'Certificação'],
                    ['name' => 'helmet', 'label' => 'Segurança do trabalho'],
                    ['name' => 'fire-extinguisher', 'label' => 'Combate a incêndio'],
                    ['name' => 'lifebuoy', 'label' => 'Segurança naval'],
                ],
            ],
        ];

        $marineIcons = array_merge(...array_map(
            static fn (array $group): array => $group['icons'],
            $boatIconGroups,
        ));

        $icons = [
            ['name' => 'package', 'label' => 'Produto'],
            ['name' => 'search', 'label' => 'Pesquisar'],
            ['name' => 'plus', 'label' => 'Adicionar'],
            ['name' => 'copy', 'label' => 'Copiar'],
            ['name' => 'pencil', 'label' => 'Editar'],
            ['name' => 'download', 'label' => 'Baixar'],
            ['name' => 'trash', 'label' => 'Excluir'],
            ['name' => 'dots-vertical', 'label' => 'Mais ações'],
            ['name' => 'alert-triangle', 'label' => 'Atenção'],
            ['name' => 'sun', 'label' => 'Tema claro'],
            ['name' => 'moon', 'label' => 'Tema escuro'],
            ['name' => 'device-desktop', 'label' => 'Tema do sistema'],
            ['name' => 'arrow-left', 'label' => 'Voltar'],
            ['name' => 'chevron-down', 'label' => 'Expandir'],
            ['name' => 'circle-check', 'label' => 'Sucesso'],
            ['name' => 'info-circle', 'label' => 'Informação'],
            ['name' => 'circle-x', 'label' => 'Erro'],
            ['name' => 'x', 'label' => 'Fechar'],
            ['name' => 'layout-dashboard', 'label' => 'Dashboard'],
            ['name' => 'ruler-measure', 'label' => 'Engenharia'],
            ['name' => 'calendar', 'label' => 'Planejamento'],
            ['name' => 'building-factory', 'label' => 'Chão de fábrica'],
            ['name' => 'chart-bar', 'label' => 'Análises'],
            ['name' => 'shopping-cart', 'label' => 'Compras'],
            ['name' => 'receipt', 'label' => 'Vendas'],
            ['name' => 'users', 'label' => 'Administração'],
            ['name' => 'help-circle', 'label' => 'Tutorial'],
            ['name' => 'settings', 'label' => 'Preferências'],
            ['name' => 'chevron-left', 'label' => 'Recolher'],
            ['name' => 'menu-2', 'label' => 'Abrir menu'],
            ['name' => 'logout', 'label' => 'Sair'],
            ...$marineIcons,
        ];

        $navigation = [
            ['fundamentos', 'Fundamentos'],
            ['cores', 'Tokens'],
            ['acoes', 'Ações'],
            ['formularios', 'Formulários'],
            ['indicadores', 'Controles'],
            ['componentes', 'Componentes'],
            ['dados', 'Tabelas e gráficos'],
            ['feedback', 'Feedback'],
            ['navegacao', 'Navegação'],
            ['modais', 'Camadas'],
            ['icones', 'Ícones'],
        ];
    @endphp

    <div class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <nav class="sticky top-16 z-30 -mx-4 mb-10 flex gap-1 overflow-x-auto border-b border-[var(--ui-border)] bg-[color-mix(in_srgb,var(--ui-canvas)_92%,transparent)] px-4 py-3 backdrop-blur-xl sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8" aria-label="Seções do Layout System">
            @foreach ($navigation as [$anchor, $label])
                <a href="#{{ $anchor }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-medium text-[var(--ui-text-muted)] no-underline transition hover:bg-[var(--ui-surface-muted)] hover:text-[var(--ui-text)]">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="min-w-0 space-y-16">
            <section id="fundamentos" class="scroll-mt-24">
                <div class="relative overflow-hidden rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-6 py-10 shadow-[var(--ui-shadow-lg)] sm:px-10 lg:px-14 lg:py-14">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[color-mix(in_srgb,var(--ui-primary)_18%,transparent)] blur-3xl"></div>
                    <div class="relative max-w-4xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[var(--ui-primary-soft)] px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-primary-text)]">Primeira versão</span>
                            <span class="rounded-full border border-[var(--ui-border)] px-3 py-1 text-xs font-semibold text-[var(--ui-text-muted)]">Blade + Tailwind CSS 4</span>
                        </div>
                        <p class="mt-8 text-sm font-semibold text-[var(--ui-primary)]">Beyond MRP Layout System</p>
                        <h1 class="mt-3 max-w-3xl font-display text-4xl font-bold tracking-[-0.04em] text-[var(--ui-text)] sm:text-5xl lg:text-7xl">Interface para decisões industriais.</h1>
                        <p class="mt-6 max-w-2xl text-base leading-8 text-[var(--ui-text-muted)] sm:text-lg">
                            Uma base clara, densa quando necessário e previsível em qualquer tema. Componentes compartilhados transformam decisões visuais em contratos reutilizáveis.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <x-ui.button :href="route('global-admin.docs.show', ['file' => '11-design-system.md'])" variant="primary" size="lg">Ver documentação</x-ui.button>
                            <x-ui.button href="#formularios" variant="outline" size="lg">Explorar componentes</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach ([['Clareza operacional', 'A prioridade visual acompanha a prioridade da tarefa.'], ['Consistência', 'O mesmo componente preserva comportamento e acessibilidade.'], ['Evolução gradual', 'Novas telas adotam a base sem exigir uma migração total imediata.']] as [$title, $description])
                        <x-ui.panel padding="p-5">
                            <strong class="text-sm text-[var(--ui-text)]">{{ $title }}</strong>
                            <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">{{ $description }}</p>
                        </x-ui.panel>
                    @endforeach
                </div>

                <x-ui.code-example class="mt-5" title="Como usar o layout">
@verbatim
@extends('layouts.design-system')

@section('title', 'Planejamento da produção')

@section('content')
    <x-ui.page-heading title="Planejamento da produção" />
    <x-ui.panel>Conteúdo da página</x-ui.panel>
@endsection
@endverbatim
                </x-ui.code-example>
            </section>

            <section id="cores" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">01 · Tokens semânticos</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Claro, escuro e sistema</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">Os componentes consomem significado, não valores fixos. Alterar o tema troca o valor do token sem alterar o markup.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($colors as $color)
                        <article class="overflow-hidden rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] shadow-[var(--ui-shadow-sm)]">
                            <div class="h-24 border-b border-[var(--ui-border)]" style="background: var({{ $color['token'] }})"></div>
                            <div class="p-4">
                                <strong class="block text-sm text-[var(--ui-text)]">{{ $color['name'] }}</strong>
                                <code class="mt-1 block text-xs text-[var(--ui-primary)]">{{ $color['token'] }}</code>
                                <p class="mt-3 text-xs text-[var(--ui-text-muted)]">{{ $color['light'] }} <span aria-hidden="true">↔</span> {{ $color['dark'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-ui.code-example class="mt-5" title="Como usar os tokens semânticos" language="Blade + Tailwind">
@verbatim
<div class="border border-[var(--ui-border)] bg-[var(--ui-surface)] text-[var(--ui-text)]">
    O tema claro ou escuro altera os tokens automaticamente.
</div>
@endverbatim
                </x-ui.code-example>
            </section>

            <section id="acoes" class="scroll-mt-24">
                <x-ui.panel padding="p-6 sm:p-8">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">02 · Ações</p>
                    <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h2 class="font-display text-3xl font-bold text-[var(--ui-text)]">Cores e estados</h2>
                            <p class="mt-2 text-sm text-[var(--ui-text-muted)]">A cor comunica intenção; texto, ícone e estado mantêm a ação compreensível.</p>
                        </div>
                        <span class="rounded-full bg-[var(--ui-surface-muted)] px-3 py-1 text-xs font-semibold text-[var(--ui-text-muted)]">9 variantes · loading · disabled · readonly</span>
                    </div>

                    <div class="mt-8 grid gap-6 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] p-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.8fr)]" data-ui-button-playground>
                        <div>
                            <div class="mb-5">
                                <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Monte seu botão</h3>
                                <p class="mt-1 text-sm text-[var(--ui-text-muted)]">Altere as opções e copie o Blade gerado.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.field label="Texto" for="ds-button-text">
                                    <x-ui.input id="ds-button-text" value="Criar ordem" data-ui-button-control="text" />
                                </x-ui.field>
                                <x-ui.field label="Ícone" for="ds-button-icon">
                                    <x-ui.select id="ds-button-icon" :select2="false" data-ui-button-control="icon">
                                        <option value="">Sem ícone</option>
                                        <option value="plus" selected>Adicionar</option>
                                        <option value="ship">Embarcação</option>
                                        <option value="anchor">Âncora</option>
                                        <option value="propeller">Propulsão</option>
                                        <option value="circle-check">Confirmar</option>
                                        <option value="trash">Excluir</option>
                                        <option value="search">Pesquisar</option>
                                    </x-ui.select>
                                </x-ui.field>
                                <x-ui.field label="Cor" for="ds-button-variant">
                                    <x-ui.select id="ds-button-variant" :select2="false" data-ui-button-control="variant">
                                        @foreach (['primary' => 'Primária', 'neutral' => 'Neutra', 'info' => 'Informação', 'success' => 'Sucesso', 'warning' => 'Aviso', 'danger' => 'Perigo', 'secondary' => 'Secundária', 'outline' => 'Contorno', 'ghost' => 'Fantasma'] as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </x-ui.field>
                                <x-ui.field label="Tamanho" for="ds-button-size">
                                    <x-ui.select id="ds-button-size" :select2="false" data-ui-button-control="size">
                                        <option value="sm">Pequeno</option>
                                        <option value="md" selected>Médio</option>
                                        <option value="lg">Grande</option>
                                    </x-ui.select>
                                </x-ui.field>
                            </div>
                            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                                <x-ui.switch id="ds-button-disabled" data-ui-button-control="disabled">Disabled</x-ui.switch>
                                <x-ui.switch id="ds-button-readonly" data-ui-button-control="readonly" description="Usa aria-disabled.">Somente leitura</x-ui.switch>
                                <x-ui.switch id="ds-button-loading" data-ui-button-control="loading">Loading</x-ui.switch>
                            </div>
                        </div>

                        <div class="flex min-w-0 flex-col justify-between gap-5 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-text-subtle)]">Prévia funcional</p>
                                <div class="mt-6 flex min-h-20 items-center justify-center rounded-xl border border-dashed border-[var(--ui-border-strong)] p-4">
                                    <x-ui.button data-ui-button-playground-preview><x-ui.icon name="plus" size="sm" />Criar ordem</x-ui.button>
                                </div>
                                <p class="mt-3 min-h-10 text-xs leading-5 text-[var(--ui-text-muted)]" role="status" data-ui-button-playground-status>Pronto para testar. Clique no botão da prévia.</p>
                            </div>

                            @foreach (['plus', 'ship', 'anchor', 'propeller', 'circle-check', 'trash', 'search'] as $playgroundIcon)
                                <template data-ui-button-icon-template="{{ $playgroundIcon }}"><x-ui.icon :name="$playgroundIcon" size="sm" /></template>
                            @endforeach

                            <x-ui.code-example title="Blade gerado" :open="true">
@verbatim
<x-ui.button variant="primary" size="md">
    <x-ui.icon name="plus" size="sm" /> Criar ordem
</x-ui.button>
@endverbatim
                            </x-ui.code-example>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <x-ui.button variant="primary" data-ui-demo-message="Ordem criada para demonstração."><x-ui.icon name="plus" size="sm" />Criar ordem</x-ui.button>
                        <x-ui.button variant="neutral" data-ui-demo-message="Ação neutra executada.">Neutro</x-ui.button>
                        <x-ui.button variant="info" data-ui-demo-message="Informação atualizada.">Informação</x-ui.button>
                        <x-ui.button variant="success" data-ui-demo-message="Ação confirmada com sucesso.">Confirmar</x-ui.button>
                        <x-ui.button variant="warning" data-ui-demo-message="Item enviado para revisão.">Revisar</x-ui.button>
                        <x-ui.button variant="danger" data-ui-demo-message="Exclusão simulada; nenhum dado foi removido."><x-ui.icon name="trash" size="sm" />Excluir</x-ui.button>
                    </div>
                    <div class="mt-8 grid gap-6 border-t border-[var(--ui-border)] pt-8 lg:grid-cols-2">
                        <div>
                            <p class="mb-3 text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-text-subtle)]">Hierarquia</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <x-ui.button variant="secondary" data-ui-demo-message="Botão secundário acionado.">Secundário</x-ui.button>
                                <x-ui.button variant="outline" data-ui-demo-message="Botão de contorno acionado.">Contorno</x-ui.button>
                                <x-ui.button variant="ghost" data-ui-demo-message="Botão fantasma acionado.">Fantasma</x-ui.button>
                            </div>
                        </div>
                        <div>
                            <p class="mb-3 text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-text-subtle)]">Estados</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <x-ui.button variant="primary" :loading="true" loading-label="Salvando">Salvar</x-ui.button>
                                <x-ui.button variant="neutral" :disabled="true">Desabilitado</x-ui.button>
                                <x-ui.button variant="outline" :readonly="true">Somente leitura</x-ui.button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap items-end gap-3 border-t border-[var(--ui-border)] pt-8">
                        <x-ui.button variant="primary" size="sm">Pequeno</x-ui.button>
                        <x-ui.button variant="primary" size="md">Médio</x-ui.button>
                        <x-ui.button variant="primary" size="lg">Grande</x-ui.button>
                    </div>

                    <x-ui.code-example class="mt-8" title="Como usar o botão">
@verbatim
<x-ui.button variant="primary" size="md">
    <x-ui.icon name="plus" size="sm" /> Criar ordem
</x-ui.button>

<x-ui.button variant="warning">Revisar</x-ui.button>
<x-ui.button variant="danger" :disabled="true">Excluir</x-ui.button>
<x-ui.button variant="outline" :readonly="true">Somente leitura</x-ui.button>
<x-ui.button variant="primary" :loading="true" loading-label="Salvando">Salvar</x-ui.button>
@endverbatim
                    </x-ui.code-example>
                </x-ui.panel>
            </section>

            <section id="formularios" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">03 · Formulários</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Campos e controles de seleção</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">Labels permanecem visíveis, ajuda fica próxima do campo e estados inválidos são comunicados além da cor.</p>
                </div>

                <x-ui.panel padding="p-6 sm:p-8">
                    <form class="grid gap-6 lg:grid-cols-2" onsubmit="return false">
                        <x-ui.field label="Produto" for="ds-product" hint="Pesquise pelo código ou descrição." :required="true">
                            <x-ui.input id="ds-product" name="ds_product" icon="search" value="MRP-2048 · Motorredutor 10 cv" aria-describedby="ds-product-hint" required />
                        </x-ui.field>

                        <x-ui.field label="Tipo de produção" for="ds-production-type">
                            <x-ui.select id="ds-production-type" name="ds_production_type" :select2="false">
                                <option>Produção interna</option>
                                <option>Terceirizada</option>
                                <option>Montagem sob demanda</option>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Quantidade planejada" for="ds-quantity" hint="Utilize a unidade cadastrada no produto.">
                            <x-ui.input id="ds-quantity" name="ds_quantity" type="number" value="120" aria-describedby="ds-quantity-hint" />
                        </x-ui.field>

                        <x-ui.field label="Centro de trabalho" for="ds-work-center" error="Selecione um centro de trabalho disponível.">
                            <x-ui.select id="ds-work-center" name="ds_work_center" :select2="false" aria-invalid="true" aria-describedby="ds-work-center-error">
                                <option value="">Selecione</option>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field class="lg:col-span-2" label="Observações" for="ds-notes" hint="Informação operacional relevante para quem executará a ordem.">
                            <x-ui.textarea id="ds-notes" name="ds_notes" rows="4" placeholder="Adicione instruções de fabricação..." aria-describedby="ds-notes-hint" />
                        </x-ui.field>

                        <div class="space-y-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] p-5">
                            <p class="text-sm font-bold text-[var(--ui-text)]">Checkbox e switch</p>
                            <x-ui.checkbox name="ds_quality" :checked="true" description="Exige inspeção antes da conclusão.">Controle de qualidade</x-ui.checkbox>
                            <x-ui.switch name="ds_notifications" :checked="true" description="Notificar o responsável por mudanças de status.">Notificações da ordem</x-ui.switch>
                            <x-ui.switch name="ds_disabled" :disabled="true">Controle indisponível</x-ui.switch>
                        </div>

                        <fieldset class="space-y-4 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface-muted)] p-5">
                            <legend class="px-1 text-sm font-bold text-[var(--ui-text)]">Prioridade</legend>
                            <x-ui.radio name="ds_priority" value="normal" :checked="true" description="Segue a programação vigente.">Normal</x-ui.radio>
                            <x-ui.radio name="ds_priority" value="urgent" description="Recebe destaque na fila de execução.">Urgente</x-ui.radio>
                        </fieldset>
                    </form>

                    <div class="mt-8 border-t border-[var(--ui-border)] pt-8">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Modelos compostos</h3>
                        <p class="mt-2 text-sm text-[var(--ui-text-muted)]">Prefixos, sufixos e ícones fazem parte do componente de input. O select pesquisável reutiliza o Select2 já instalado no projeto.</p>
                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <x-ui.field label="Custo unitário" for="ds-unit-cost" hint="Prefixo monetário fixo.">
                                <x-ui.input id="ds-unit-cost" name="ds_unit_cost" type="number" prefix="R$" value="845.90" step="0.01" />
                            </x-ui.field>
                            <x-ui.field label="Peso estimado" for="ds-weight" hint="Sufixo de unidade fixo.">
                                <x-ui.input id="ds-weight" name="ds_weight" type="number" suffix="kg" value="1280" />
                            </x-ui.field>
                            <x-ui.field label="Código da embarcação" for="ds-vessel-code">
                                <x-ui.input id="ds-vessel-code" name="ds_vessel_code" icon="ship" value="BMRP-2408" />
                            </x-ui.field>
                            <x-ui.field label="Prazo de entrega" for="ds-due-date">
                                <x-ui.input id="ds-due-date" name="ds_due_date" icon="calendar" icon-position="end" value="18/08/2026" />
                            </x-ui.field>
                            <x-ui.field class="lg:col-span-2" label="Componente naval" for="ds-searchable-component" hint="Abra o select e pesquise por código ou descrição.">
                                <x-ui.select id="ds-searchable-component" name="ds_searchable_component" data-search="on" data-placeholder="Selecione um componente">
                                    <option value=""></option>
                                    <option value="CAS-001">CAS-001 · Casco principal</option>
                                    <option value="MOT-210">MOT-210 · Motor marítimo</option>
                                    <option value="HEL-040">HEL-040 · Conjunto de hélice</option>
                                    <option value="ANC-080">ANC-080 · Âncora galvanizada</option>
                                    <option value="LEM-120">LEM-120 · Sistema de leme</option>
                                    <option value="CON-300">CON-300 · Console de comando</option>
                                    <option value="SAL-050">SAL-050 · Equipamento salva-vidas</option>
                                    <option value="TAN-600">TAN-600 · Tanque de combustível</option>
                                </x-ui.select>
                            </x-ui.field>
                            <x-ui.field class="lg:col-span-2" label="Componentes da configuração" for="ds-multiple-components" hint="Pesquise e selecione mais de um componente.">
                                <x-ui.select id="ds-multiple-components" name="ds_multiple_components[]" multiple data-search="on" data-placeholder="Selecione os componentes">
                                    <option value="CAS-001">CAS-001 · Casco principal</option>
                                    <option value="MOT-210" selected>MOT-210 · Motor marítimo</option>
                                    <option value="HEL-040" selected>HEL-040 · Conjunto de hélice</option>
                                    <option value="ANC-080">ANC-080 · Âncora galvanizada</option>
                                    <option value="LEM-120">LEM-120 · Sistema de leme</option>
                                    <option value="CON-300">CON-300 · Console de comando</option>
                                    <option value="SAL-050">SAL-050 · Equipamento salva-vidas</option>
                                    <option value="TAN-600">TAN-600 · Tanque de combustível</option>
                                </x-ui.select>
                            </x-ui.field>
                        </div>
                    </div>
                </x-ui.panel>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <x-ui.code-example title="Como usar campo e input">
@verbatim
<x-ui.field label="Produto" for="product" hint="Código ou descrição." :required="true">
    <x-ui.input id="product" name="product" required />
</x-ui.field>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar o select">
@verbatim
<x-ui.field label="Tipo de produção" for="production_type">
    <x-ui.select id="production_type" name="production_type" :select2="false">
        <option value="internal">Produção interna</option>
        <option value="outsourced">Terceirizada</option>
    </x-ui.select>
</x-ui.field>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar prefixo e sufixo">
@verbatim
<x-ui.input name="unit_cost" type="number" prefix="R$" step="0.01" />
<x-ui.input name="weight" type="number" suffix="kg" />
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar input com ícone">
@verbatim
<x-ui.input name="vessel" icon="ship" placeholder="Código da embarcação" />
<x-ui.input name="due_date" icon="calendar" icon-position="end" />
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar select com busca">
@verbatim
<x-ui.select name="component_id" data-search="on" data-placeholder="Pesquise um componente">
    <option value=""></option>
    <option value="CAS-001">CAS-001 · Casco principal</option>
    <option value="MOT-210">MOT-210 · Motor marítimo</option>
</x-ui.select>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar select múltiplo">
@verbatim
<x-ui.select name="component_ids[]" multiple data-search="on" data-placeholder="Selecione os componentes">
    <option value="MOT-210">MOT-210 · Motor marítimo</option>
    <option value="HEL-040">HEL-040 · Conjunto de hélice</option>
    <option value="ANC-080">ANC-080 · Âncora galvanizada</option>
</x-ui.select>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar o textarea">
@verbatim
<x-ui.field label="Observações" for="notes" hint="Instruções para a produção.">
    <x-ui.textarea id="notes" name="notes" rows="4" />
</x-ui.field>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar o checkbox">
@verbatim
<x-ui.checkbox name="quality_control" :checked="true" description="Exige inspeção final.">
    Controle de qualidade
</x-ui.checkbox>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar o switch">
@verbatim
<x-ui.switch name="notify_owner" :checked="true" description="Avisa quando o status mudar.">
    Notificar responsável
</x-ui.switch>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar o radio">
@verbatim
<fieldset>
    <legend>Prioridade</legend>
    <x-ui.radio name="priority" value="normal" :checked="true">Normal</x-ui.radio>
    <x-ui.radio name="priority" value="urgent">Urgente</x-ui.radio>
</fieldset>
@endverbatim
                    </x-ui.code-example>
                </div>
            </section>

            <section id="indicadores" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">04 · Controles e indicadores</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Slider e progresso</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">O slider captura um valor dentro de uma faixa; o progresso comunica a evolução de uma operação sem exigir interação.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.panel padding="p-6">
                        <x-ui.field label="Conclusão da fabricação" for="ds-completion" hint="Mova o controle para atualizar o progresso ao lado.">
                            <x-ui.slider id="ds-completion" name="completion" :value="64" suffix="%" data-ui-progress-target="#ds-production-progress" />
                        </x-ui.field>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6">
                        <x-ui.progress id="ds-production-progress" label="Lancha Ocean 240" :value="64" variant="primary" />
                        <div class="mt-7 space-y-5">
                            <x-ui.progress label="Estrutura do casco" :value="100" variant="success" size="sm" />
                            <x-ui.progress label="Instalação elétrica" :value="72" variant="warning" />
                            <x-ui.progress label="Pendência de inspeção" :value="28" variant="danger" size="lg" />
                        </div>
                    </x-ui.panel>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <x-ui.code-example title="Como usar o slider">
@verbatim
<x-ui.field label="Conclusão" for="completion">
    <x-ui.slider id="completion" name="completion" :value="64" suffix="%" />
</x-ui.field>
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como usar o progresso">
@verbatim
<x-ui.progress label="Fabricação da embarcação" :value="64" variant="primary" />
<x-ui.progress label="Estrutura do casco" :value="100" variant="success" size="sm" />
@endverbatim
                    </x-ui.code-example>
                </div>
            </section>

            <section id="componentes" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">05 · Componentes</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Componentes compostos</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">Blocos reutilizáveis para conteúdo expansível, status, arquivos, ações agrupadas e entradas estruturadas.</p>
                </div>

                <div class="grid items-start gap-5 xl:grid-cols-2">
                    <x-ui.panel padding="p-6">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Accordion e Collapsible</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Organize informações extensas sem sobrecarregar a tela.</p>
                        <div class="mt-5 space-y-4">
                            <x-ui.accordion
                                :items="[
                                    ['value' => 'materials', 'title' => 'Materiais do casco', 'content' => 'Alumínio naval, perfis estruturais e consumíveis de solda estão reservados.'],
                                    ['value' => 'engine', 'title' => 'Conjunto de propulsão', 'content' => 'Motor, eixo e hélice aguardam inspeção dimensional.'],
                                    ['value' => 'premium', 'title' => 'Configuração indisponível', 'content' => 'Conteúdo indisponível.', 'disabled' => true],
                                ]"
                                :default-open="['materials']"
                            />
                            <x-ui.collapsible title="Detalhes técnicos" description="Informações complementares da embarcação">
                                Comprimento total: 7,3 m · Boca: 2,6 m · Capacidade: 10 passageiros.
                            </x-ui.collapsible>
                        </div>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Badge e Tooltip</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Comunique estados compactos e explique ações sem poluir a interface.</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-ui.badge variant="primary">Em planejamento</x-ui.badge>
                            <x-ui.badge variant="success">Concluído</x-ui.badge>
                            <x-ui.badge variant="warning">Aguardando</x-ui.badge>
                            <x-ui.badge variant="danger">Bloqueado</x-ui.badge>
                            <x-ui.badge variant="outline">OP-1052</x-ui.badge>
                        </div>
                        <div class="mt-5">
                            <x-ui.tooltip content="Atualiza as necessidades de materiais">
                                <button type="button" class="ui-icon-button" aria-label="Recalcular MRP"><x-ui.icon name="ripple" /></button>
                            </x-ui.tooltip>
                        </div>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Attachment e Item</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Represente arquivos e registros com contexto, estado e ações.</p>
                        <div class="mt-5 space-y-3">
                            <x-ui.attachment title="projeto-casco-v4.pdf" description="PDF · 4,8 MB" state="done">
                                <x-slot:actions><button type="button" class="ui-icon-button" aria-label="Remover projeto-casco-v4.pdf" data-ui-attachment-remove><x-ui.icon name="x" size="sm" /></button></x-slot:actions>
                            </x-ui.attachment>
                            <x-ui.attachment title="memorial-descritivo.pdf" description="Enviando · 64%" state="uploading" :progress="64">
                                <x-slot:actions><button type="button" class="ui-icon-button" aria-label="Cancelar envio" data-ui-attachment-remove><x-ui.icon name="x" size="sm" /></button></x-slot:actions>
                            </x-ui.attachment>
                            <x-ui.item title="Inspeção de estanqueidade" description="Responsável: Controle de qualidade" icon="circle-check" href="#feedback">
                                <x-slot:actions><x-ui.icon name="chevron-left" size="sm" class="rotate-180" /></x-slot:actions>
                            </x-ui.item>
                        </div>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Input Group</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Combine campos, unidades, atalhos e ações em um único controle.</p>
                        <div class="mt-5 space-y-5">
                            <x-ui.field label="Pesquisar ordem" for="ds-group-search">
                                <x-ui.input-group label="Pesquisar ordem de produção">
                                    <x-ui.input-group.addon><x-ui.icon name="search" size="sm" /></x-ui.input-group.addon>
                                    <x-ui.input id="ds-group-search" :unstyled="true" class="ui-input-group-control" placeholder="Código ou embarcação" />
                                    <x-ui.input-group.addon position="end"><span class="text-xs">⌘ K</span></x-ui.input-group.addon>
                                </x-ui.input-group>
                            </x-ui.field>
                            <x-ui.field label="Quantidade planejada" for="ds-group-quantity">
                                <x-ui.input-group label="Quantidade planejada">
                                    <x-ui.input id="ds-group-quantity" type="number" :unstyled="true" class="ui-input-group-control" value="12" />
                                    <x-ui.input-group.addon position="end">un.</x-ui.input-group.addon>
                                    <x-ui.input-group.addon position="end"><x-ui.button variant="ghost" size="sm">Aplicar</x-ui.button></x-ui.input-group.addon>
                                </x-ui.input-group>
                            </x-ui.field>
                        </div>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6" class="xl:col-span-2">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Button Group</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Agrupe ações relacionadas sem espaçamento: divisórias internas retas e arredondamento apenas no contorno.</p>
                        <div class="mt-5 space-y-3 overflow-x-auto pb-2">
                            <x-ui.button-group label="Navegação principal" class="min-w-max">
                                <x-ui.button variant="ghost"><x-ui.icon name="settings" />Configuração</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="ship" />Produção</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="chart-bar" />Análises</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="receipt" />Documentos<x-ui.icon name="chevron-down" size="sm" /></x-ui.button>
                            </x-ui.button-group>
                            <x-ui.button-group label="Navegação principal em contorno" tone="outline" class="min-w-max">
                                <x-ui.button variant="ghost"><x-ui.icon name="settings" />Configuração</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="ship" />Produção</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="chart-bar" />Análises</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="receipt" />Documentos<x-ui.icon name="chevron-down" size="sm" /></x-ui.button>
                            </x-ui.button-group>
                            <x-ui.button-group label="Navegação principal em superfície" tone="surface" class="min-w-max">
                                <x-ui.button variant="ghost"><x-ui.icon name="settings" />Configuração</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="ship" />Produção</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="chart-bar" />Análises</x-ui.button>
                                <x-ui.button variant="ghost"><x-ui.icon name="receipt" />Documentos<x-ui.icon name="chevron-down" size="sm" /></x-ui.button>
                            </x-ui.button-group>
                        </div>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6" class="xl:col-span-2">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Calendar e Date Picker</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Use o calendário para visão ampla e o Date Picker para seleção dentro de formulários.</p>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <x-ui.calendar month="2026-08" selected="2026-08-18" min="2026-08-04" />
                            <div>
                                <x-ui.field label="Entrega planejada" for="ds-delivery-date">
                                    <x-ui.date-picker id="ds-delivery-date" name="delivery_date" value="2026-08-18" min="2026-08-04" />
                                </x-ui.field>
                                <p class="mt-4 text-xs leading-5 text-[var(--ui-text-muted)]">O Date Picker compõe um trigger, um popover e o mesmo Calendar reutilizável.</p>
                            </div>
                        </div>
                    </x-ui.panel>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <x-ui.code-example title="Como usar Accordion e Collapsible">
@verbatim
<x-ui.accordion :items="$sections" :default-open="['materials']" />
<x-ui.collapsible title="Detalhes técnicos">Conteúdo adicional.</x-ui.collapsible>
@endverbatim
                    </x-ui.code-example>
                    <x-ui.code-example title="Como usar Attachment">
@verbatim
<x-ui.attachment title="projeto.pdf" description="PDF · 4,8 MB" state="done">
    <x-slot:actions><button data-ui-attachment-remove>Remover</button></x-slot:actions>
</x-ui.attachment>
@endverbatim
                    </x-ui.code-example>
                    <x-ui.code-example title="Como usar Badge e Tooltip">
@verbatim
<x-ui.badge variant="success">Concluído</x-ui.badge>
<x-ui.tooltip content="Recalcular MRP"><button aria-label="Recalcular">...</button></x-ui.tooltip>
@endverbatim
                    </x-ui.code-example>
                    <x-ui.code-example title="Como usar Button Group">
@verbatim
<x-ui.button-group label="Navegação principal" tone="primary">
    <x-ui.button variant="ghost"><x-ui.icon name="settings" /> Configuração</x-ui.button>
    <x-ui.button variant="ghost"><x-ui.icon name="ship" /> Produção</x-ui.button>
    <x-ui.button variant="ghost"><x-ui.icon name="chart-bar" /> Análises</x-ui.button>
</x-ui.button-group>
{{-- Use tone="outline" ou tone="surface" para as demais aparências. --}}
@endverbatim
                    </x-ui.code-example>
                    <x-ui.code-example title="Como usar Calendar e Date Picker">
@verbatim
<x-ui.calendar name="inspection_date" selected="2026-08-18" />
<x-ui.date-picker name="delivery_date" value="2026-08-18" />
@endverbatim
                    </x-ui.code-example>
                    <x-ui.code-example title="Como usar Input Group">
@verbatim
<x-ui.input-group label="Busca">
    <x-ui.input-group.addon><x-ui.icon name="search" /></x-ui.input-group.addon>
    <x-ui.input :unstyled="true" class="ui-input-group-control" />
</x-ui.input-group>
@endverbatim
                    </x-ui.code-example>
                    <x-ui.code-example title="Como usar Item">
@verbatim
<x-ui.item title="Inspeção" description="Controle de qualidade" icon="circle-check" />
@endverbatim
                    </x-ui.code-example>
                </div>
            </section>

            <section id="dados" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">06 · Dados</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Tabelas e gráficos</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">Escolha a visualização pela tarefa: gráficos para comparação e tendência, tabelas para consulta e operação detalhada.</p>
                </div>

                <x-ui.panel padding="p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Gráficos</h3>
                            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">Produção, eficiência, consumo, capacidade, status e custos.</p>
                        </div>
                        <x-ui.badge variant="info">6 exemplos</x-ui.badge>
                    </div>
                    <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <x-ui.chart label="Produção semanal" summary="93 un." :labels="['Seg', 'Ter', 'Qua', 'Qui', 'Sex']" :data="[12, 18, 15, 26, 22]" suffix=" un." />
                        <x-ui.chart type="line" label="Eficiência semanal" summary="91% atual" :labels="['Seg', 'Ter', 'Qua', 'Qui', 'Sex']" :data="[72, 78, 76, 84, 91]" suffix="%" />
                        <x-ui.chart type="area" label="Consumo de alumínio" summary="+62% no período" :labels="['S1', 'S2', 'S3', 'S4', 'S5', 'S6']" :data="[420, 510, 480, 620, 590, 680]" suffix=" kg" />
                        <x-ui.chart type="horizontal" label="Capacidade por setor" summary="75% média" :labels="['Corte', 'Solda', 'Montagem', 'Acabamento']" :data="[86, 68, 54, 91]" suffix="%" />
                        <x-ui.chart type="donut" label="Status das ordens" summary="18 ordens" :labels="['Produção', 'Planejadas', 'Inspeção', 'Bloqueadas']" :data="[8, 5, 3, 2]" suffix=" ordens" />
                        <x-ui.chart label="Custo por etapa" summary="R$ 125 mil" :labels="['Casco', 'Propulsão', 'Elétrica', 'Acabamento']" :data="[28, 42, 37, 18]" suffix=" mil" />
                    </div>
                </x-ui.panel>

                <div class="mt-5 grid gap-5 xl:grid-cols-2">
                    <x-ui.code-example title="Como usar gráficos">
@verbatim
<x-ui.chart label="Produção" :labels="$labels" :data="$values" />
<x-ui.chart type="line" label="Eficiência" :labels="$labels" :data="$efficiency" suffix="%" />
<x-ui.chart type="area" label="Consumo" :labels="$weeks" :data="$consumption" suffix=" kg" />
<x-ui.chart type="horizontal" label="Capacidade" :labels="$sectors" :data="$capacity" suffix="%" />
<x-ui.chart type="donut" label="Status" :labels="$statuses" :data="$orders" />
@endverbatim
                    </x-ui.code-example>

                    <x-ui.code-example title="Como escolher a visualização">
@verbatim
{{-- bar/horizontal: comparar categorias --}}
{{-- line/area: acompanhar evolução no tempo --}}
{{-- donut: distribuir um total entre poucas categorias --}}
{{-- table/data-table: consultar e operar registros detalhados --}}
@endverbatim
                    </x-ui.code-example>
                </div>

                <x-ui.panel padding="p-6" class="mt-5">
                    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Data Table</h3>
                            <p class="mt-1 text-sm text-[var(--ui-text-muted)]">Filtro, ordenação, edição inline, cópia de células e paginação local.</p>
                        </div>
                        <x-ui.badge variant="info">8 ordens</x-ui.badge>
                    </div>
                    <x-ui.data-table filter-placeholder="Filtrar ordem, embarcação ou status..." :page-size="4">
                        <x-slot:actions><x-ui.button variant="outline" size="sm"><x-ui.icon name="download" size="sm" />Exportar</x-ui.button></x-slot:actions>
                        <x-ui.table caption="Ordens de fabricação de embarcações">
                            <thead><tr><x-ui.table.head>Ordem</x-ui.table.head><x-ui.table.head>Embarcação</x-ui.table.head><x-ui.table.head>Status</x-ui.table.head><x-ui.table.head align="right">Progresso</x-ui.table.head><x-ui.table.head align="center"><span class="sr-only">Ações</span></x-ui.table.head></tr></thead>
                            <tbody>
                                @foreach ([
                                    ['OP-1052', 'Ocean 240', 'Em produção', '64%'], ['OP-1051', 'Fisher 190', 'Inspeção', '92%'],
                                    ['OP-1050', 'Wave 300', 'Planejada', '18%'], ['OP-1049', 'Coast 220', 'Bloqueada', '37%'],
                                    ['OP-1048', 'River 180', 'Concluída', '100%'], ['OP-1047', 'Ocean 260', 'Em produção', '71%'],
                                    ['OP-1046', 'Fisher 210', 'Montagem', '53%'], ['OP-1045', 'Wave 280', 'Concluída', '100%'],
                                ] as [$order, $boat, $status, $progress])
                                    <x-ui.table.row>
                                        <x-ui.table.cell :copyable="true" :value="$order"><strong>{{ $order }}</strong></x-ui.table.cell>
                                        <x-ui.table.cell :editable="true" :copyable="true" :value="$boat" input-name="boat[{{ $order }}]">{{ $boat }}</x-ui.table.cell>
                                        <x-ui.table.cell :editable="true" :copyable="true" :value="$status" input-name="status[{{ $order }}]">{{ $status }}</x-ui.table.cell>
                                        <x-ui.table.cell align="right" :editable="true" :copyable="true" :value="$progress" input-name="progress[{{ $order }}]">{{ $progress }}</x-ui.table.cell>
                                        <x-ui.table.cell align="center">
                                            <span class="inline-flex items-center gap-1">
                                                <button type="button" class="ui-icon-button h-8 w-8" title="Copiar linha" aria-label="Copiar linha {{ $order }}" data-ui-copy-text="{{ $order }} | {{ $boat }} | {{ $status }} | {{ $progress }}"><x-ui.icon name="copy" size="sm" /></button>
                                                <button type="button" class="ui-icon-button h-8 w-8" title="Editar linha" aria-label="Editar linha {{ $order }}" data-ui-table-row-edit><x-ui.icon name="pencil" size="sm" /></button>
                                            </span>
                                        </x-ui.table.cell>
                                    </x-ui.table.row>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </x-ui.data-table>
                    <x-ui.code-example class="mt-5" title="Como usar Data Table">
@verbatim
<x-ui.data-table filter-placeholder="Filtrar ordens..." :page-size="10">
    <x-ui.table>
        <x-ui.table.row>
            <x-ui.table.cell :copyable="true" :value="$order">{{ $order }}</x-ui.table.cell>
            <x-ui.table.cell :editable="true" :copyable="true" :value="$boat">{{ $boat }}</x-ui.table.cell>
        </x-ui.table.row>
    </x-ui.table>
</x-ui.data-table>
@endverbatim
                    </x-ui.code-example>
                </x-ui.panel>

                <div class="mb-4 mt-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Tabela base</h3>
                        <p class="mt-1 text-sm text-[var(--ui-text-muted)]">Leitura direta, sem filtro ou paginação.</p>
                    </div>
                    <x-ui.button variant="primary" data-ui-demo-message="Nova ordem iniciada para demonstração."><x-ui.icon name="plus" size="sm" />Nova ordem</x-ui.button>
                </div>

                <x-ui.table caption="Ordens de produção recentes">
                    <thead>
                        <tr>
                            <x-ui.table.head>Ordem</x-ui.table.head>
                            <x-ui.table.head>Produto</x-ui.table.head>
                            <x-ui.table.head>Prazo</x-ui.table.head>
                            <x-ui.table.head align="right">Quantidade</x-ui.table.head>
                            <x-ui.table.head>Status</x-ui.table.head>
                            <x-ui.table.head align="center"><span class="sr-only">Ações</span></x-ui.table.head>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['OP-1048', 'Motorredutor 10 cv', 'Hoje, 16:00', '120 un.', 'Em produção', 'warning'],
                            ['OP-1047', 'Eixo principal 40 mm', 'Amanhã', '80 un.', 'Liberada', 'info'],
                            ['OP-1046', 'Base usinada MX-2', '08 ago.', '45 un.', 'Concluída', 'success'],
                        ] as [$order, $product, $due, $quantity, $status, $tone])
                            <x-ui.table.row>
                                <x-ui.table.cell><strong class="text-[var(--ui-text)]">{{ $order }}</strong></x-ui.table.cell>
                                <x-ui.table.cell>
                                    <span class="flex items-center gap-3">
                                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-[var(--ui-primary-soft)] text-[var(--ui-primary-text)]"><x-ui.icon name="package" size="sm" /></span>
                                        <span>{{ $product }}</span>
                                    </span>
                                </x-ui.table.cell>
                                <x-ui.table.cell class="text-[var(--ui-text-muted)]">{{ $due }}</x-ui.table.cell>
                                <x-ui.table.cell align="right">{{ $quantity }}</x-ui.table.cell>
                                <x-ui.table.cell><span class="ui-status ui-status-{{ $tone }}">{{ $status }}</span></x-ui.table.cell>
                                <x-ui.table.cell align="center"><button type="button" class="ui-icon-button" aria-label="Ações de {{ $order }}"><x-ui.icon name="dots-vertical" size="sm" /></button></x-ui.table.cell>
                            </x-ui.table.row>
                        @endforeach
                    </tbody>
                </x-ui.table>

                <x-ui.code-example class="mt-5" title="Como usar a tabela">
@verbatim
<x-ui.table caption="Ordens de produção">
    <thead>
        <tr>
            <x-ui.table.head>Ordem</x-ui.table.head>
            <x-ui.table.head align="right">Quantidade</x-ui.table.head>
        </tr>
    </thead>
    <tbody>
        <x-ui.table.row>
            <x-ui.table.cell>OP-1048</x-ui.table.cell>
            <x-ui.table.cell align="right">120 un.</x-ui.table.cell>
        </x-ui.table.row>
    </tbody>
</x-ui.table>
@endverbatim
                </x-ui.code-example>
            </section>

            <section id="feedback" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">07 · Feedback</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Estados sem ambiguidade</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">Dispare notificações no canto superior direito. Cada tipo mantém ícone, título, mensagem e fechamento próprios.</p>
                </div>
                <x-ui.panel padding="p-6">
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="primary" data-ui-alert-trigger="all">Exibir todos</x-ui.button>
                        <x-ui.button variant="success" data-ui-alert-trigger="success">Sucesso</x-ui.button>
                        <x-ui.button variant="info" data-ui-alert-trigger="info">Informação</x-ui.button>
                        <x-ui.button variant="warning" data-ui-alert-trigger="warning">Aviso</x-ui.button>
                        <x-ui.button variant="danger" data-ui-alert-trigger="error">Erro</x-ui.button>
                    </div>
                </x-ui.panel>

                <x-ui.code-example class="mt-5" title="Como usar o alert">
@verbatim
<x-ui.alert variant="success" title="Ordem liberada" :dismissible="true">
    Os materiais foram reservados com sucesso.
</x-ui.alert>

{{-- Variantes disponíveis: success, info, warning e error. --}}
@endverbatim
                </x-ui.code-example>

                <div class="ds-alert-stack" data-ui-alert-stack aria-label="Notificações de demonstração">
                    <x-ui.alert class="pointer-events-auto hidden shadow-lg" variant="success" title="Ordem liberada" :dismissible="true" data-ui-demo-alert="success">Os materiais foram reservados com sucesso.</x-ui.alert>
                    <x-ui.alert class="pointer-events-auto hidden shadow-lg" variant="info" title="Sincronização disponível" :dismissible="true" data-ui-demo-alert="info">Existem dados mais recentes para consultar.</x-ui.alert>
                    <x-ui.alert class="pointer-events-auto hidden shadow-lg" variant="warning" title="Estoque abaixo do mínimo" :dismissible="true" data-ui-demo-alert="warning">Revise a necessidade planejada.</x-ui.alert>
                    <x-ui.alert class="pointer-events-auto hidden shadow-lg" variant="error" title="Apontamento não concluído" :dismissible="true" data-ui-demo-alert="error">Corrija os campos destacados e tente novamente.</x-ui.alert>
                </div>
            </section>

            <section id="navegacao" class="scroll-mt-24">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">08 · Navegação</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Ações e navegação contextual</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">O dropdown agrupa ações relacionadas; as tabs alternam visões do mesmo contexto sem trocar de página.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
                    <x-ui.panel padding="p-6">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Menu de ações</h3>
                        <p class="mt-2 mb-6 text-sm leading-6 text-[var(--ui-text-muted)]">Fecha ao escolher uma ação, clicar fora ou pressionar Escape.</p>
                        <x-ui.dropdown label="Ações da ordem">
                            <button type="button" role="menuitem" class="ui-dropdown-item" data-ui-demo-message="Produção liberada para demonstração."><x-ui.icon name="circle-check" size="sm" />Liberar produção</button>
                            <button type="button" role="menuitem" class="ui-dropdown-item" data-ui-demo-message="Materiais carregados para demonstração."><x-ui.icon name="package" size="sm" />Ver materiais</button>
                            <button type="button" role="menuitem" class="ui-dropdown-item ui-dropdown-item-danger" data-ui-demo-message="Exclusão simulada; nenhum dado foi removido."><x-ui.icon name="trash" size="sm" />Excluir ordem</button>
                        </x-ui.dropdown>

                        <x-ui.code-example class="mt-6" title="Como usar o dropdown">
@verbatim
<x-ui.dropdown label="Ações da ordem">
    <a href="/orders/1048" role="menuitem" class="ui-dropdown-item">
        <x-ui.icon name="package" size="sm" /> Ver materiais
    </a>
    <button type="button" role="menuitem" class="ui-dropdown-item ui-dropdown-item-danger">
        <x-ui.icon name="trash" size="sm" /> Excluir ordem
    </button>
</x-ui.dropdown>
@endverbatim
                        </x-ui.code-example>
                    </x-ui.panel>

                    <x-ui.panel padding="p-6">
                        <h3 class="font-display text-xl font-bold text-[var(--ui-text)]">Navegação por abas</h3>
                        <p class="mb-6 mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Alterne visões relacionadas sem perder o contexto da página.</p>
                        <x-ui.tabs label="Detalhes da ordem de produção">
                            <x-ui.tabs.list>
                                <x-ui.tabs.tab id="tab-resumo" target="panel-resumo" :active="true">Resumo</x-ui.tabs.tab>
                                <x-ui.tabs.tab id="tab-materiais" target="panel-materiais">Materiais</x-ui.tabs.tab>
                                <x-ui.tabs.tab id="tab-historico" target="panel-historico">Histórico</x-ui.tabs.tab>
                                <x-ui.tabs.tab id="tab-indisponivel" target="panel-indisponivel" :disabled="true">Custos</x-ui.tabs.tab>
                            </x-ui.tabs.list>
                            <x-ui.tabs.panel id="panel-resumo" labelledby="tab-resumo" :active="true">
                                A ordem OP-1048 está em produção, com 120 unidades planejadas e conclusão prevista para hoje às 16:00.
                            </x-ui.tabs.panel>
                            <x-ui.tabs.panel id="panel-materiais" labelledby="tab-materiais">
                                Onze componentes estão reservados e dois aguardam reposição do estoque central.
                            </x-ui.tabs.panel>
                            <x-ui.tabs.panel id="panel-historico" labelledby="tab-historico">
                                Última atualização: início da operação de usinagem registrado às 09:42.
                            </x-ui.tabs.panel>
                            <x-ui.tabs.panel id="panel-indisponivel" labelledby="tab-indisponivel">Custos indisponíveis.</x-ui.tabs.panel>
                        </x-ui.tabs>

                        <x-ui.code-example class="mt-6" title="Como usar as tabs">
@verbatim
<x-ui.tabs label="Detalhes da ordem">
    <x-ui.tabs.list>
        <x-ui.tabs.tab id="summary-tab" target="summary-panel" :active="true">Resumo</x-ui.tabs.tab>
        <x-ui.tabs.tab id="materials-tab" target="materials-panel">Materiais</x-ui.tabs.tab>
    </x-ui.tabs.list>
    <x-ui.tabs.panel id="summary-panel" labelledby="summary-tab" :active="true">Resumo da ordem.</x-ui.tabs.panel>
    <x-ui.tabs.panel id="materials-panel" labelledby="materials-tab">Lista de materiais.</x-ui.tabs.panel>
</x-ui.tabs>
@endverbatim
                        </x-ui.code-example>
                    </x-ui.panel>
                </div>
            </section>

            <section id="modais" class="scroll-mt-24">
                <x-ui.panel padding="p-6 sm:p-8">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">09 · Camadas</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Modais e sheets</h2>
                    <p class="mt-3 max-w-3xl leading-7 text-[var(--ui-text-muted)]">Escolha o tamanho pela complexidade da decisão. Sheet mantém o contexto visível em fluxos laterais; full atende tarefas extensas.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @foreach ([
                            ['modal-sm', 'sm'],
                            ['modal-md', 'md'],
                            ['modal-lg', 'lg'],
                            ['modal-xl', 'xl'],
                            ['modal-full', 'full'],
                            ['modal-sheet', 'sheet'],
                        ] as [$modalId, $modalSize])
                            <x-ui.button variant="outline" data-ui-modal-open="{{ $modalId }}">{{ strtoupper($modalSize) }}</x-ui.button>
                        @endforeach
                    </div>

                    <x-ui.code-example class="mt-8" title="Como usar o modal ou sheet">
@verbatim
<x-ui.button data-ui-modal-open="filters-sheet">Abrir filtros</x-ui.button>

<x-ui.modal id="filters-sheet" size="sheet" title="Filtros da programação">
    Conteúdo do painel lateral.

    <x-slot:footer>
        <x-ui.button variant="ghost" data-ui-modal-close>Cancelar</x-ui.button>
        <x-ui.button variant="primary">Aplicar filtros</x-ui.button>
    </x-slot:footer>
</x-ui.modal>

{{-- Tamanhos: sm, md, lg, xl, full e sheet. --}}
@endverbatim
                    </x-ui.code-example>
                </x-ui.panel>

                @foreach ([
                    ['modal-sm', 'sm', 'Confirmação rápida', 'Use para uma decisão curta e objetiva.'],
                    ['modal-md', 'md', 'Editar planejamento', 'Tamanho padrão para formulários de baixa complexidade.'],
                    ['modal-lg', 'lg', 'Detalhes da produção', 'Oferece espaço para informações relacionadas.'],
                    ['modal-xl', 'xl', 'Análise de necessidades', 'Indicado para conteúdo denso, tabelas ou comparações.'],
                    ['modal-full', 'full', 'Planejamento completo', 'Ocupa a área útil para uma tarefa extensa e focada.'],
                    ['modal-sheet', 'sheet', 'Filtros da programação', 'Painel lateral para ajustar o conteúdo ao fundo.'],
                ] as [$modalId, $modalSize, $modalTitle, $modalDescription])
                    <x-ui.modal :id="$modalId" :size="$modalSize" :title="$modalTitle" :description="$modalDescription">
                        <p>Este exemplo demonstra dimensões, cabeçalho, rolagem interna, fechamento por Escape e restauração do foco no botão que abriu a camada.</p>
                        <x-slot:footer>
                            <x-ui.button variant="ghost" data-ui-modal-close>Cancelar</x-ui.button>
                            <x-ui.button variant="primary" data-ui-modal-close data-ui-demo-message="Ação do modal confirmada.">Confirmar</x-ui.button>
                        </x-slot:footer>
                    </x-ui.modal>
                @endforeach
            </section>

            <section id="icones" class="scroll-mt-24 pb-10">
                <div class="mb-6 max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--ui-primary)]">10 · Iconografia</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-[var(--ui-text)] sm:text-4xl">Tabler Icons</h2>
                    <p class="mt-3 leading-7 text-[var(--ui-text-muted)]">Somente SVGs usados são mantidos localmente. Nenhum pacote, CDN ou catálogo completo faz parte da aplicação.</p>
                </div>

                <x-ui.panel padding="p-6 sm:p-8" class="mb-6">
                    <div class="max-w-3xl">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-primary)]">Contexto inicial</p>
                        <h3 class="mt-2 font-display text-2xl font-bold text-[var(--ui-text)]">Fabricação de barcos</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">Vocabulário visual para acompanhar o barco da fabricação à inspeção final. Escolha o ícone pelo processo representado, não apenas pela aparência.</p>
                    </div>
                    <div class="mt-8 space-y-8">
                        @foreach ($boatIconGroups as $group)
                            <div>
                                <h4 class="text-sm font-bold text-[var(--ui-text)]">{{ $group['title'] }}</h4>
                                <p class="mt-1 text-sm text-[var(--ui-text-muted)]">{{ $group['description'] }}</p>
                                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                    @foreach ($group['icons'] as $icon)
                                        <div class="flex min-h-28 flex-col items-center justify-center rounded-xl border border-[color-mix(in_srgb,var(--ui-primary)_18%,var(--ui-border))] bg-[var(--ui-primary-soft)] p-4 text-center">
                                            <x-ui.icon :name="$icon['name']" size="lg" class="text-[var(--ui-primary-text)]" />
                                            <span class="mt-3 text-xs font-semibold text-[var(--ui-primary-text)]">{{ $icon['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.panel>

                <x-ui.panel padding="p-6 sm:p-8" class="mb-8">
                    <h3 class="font-display text-2xl font-bold text-[var(--ui-text)]">Como usar</h3>
                    <p class="mt-2 text-sm leading-6 text-[var(--ui-text-muted)]">O componente busca o SVG em <code class="text-[var(--ui-primary)]">resources/icons/tabler</code> e rejeita nomes que não estejam disponíveis localmente.</p>
                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <x-ui.code-example title="Ícone decorativo" :open="true">
@verbatim
<x-ui.icon name="ship" />
@endverbatim
                        </x-ui.code-example>

                        <x-ui.code-example title="Tamanho e cor">
@verbatim
<x-ui.icon name="anchor" size="lg" class="text-[var(--ui-primary)]" />
@endverbatim
                        </x-ui.code-example>

                        <x-ui.code-example title="Ícone dentro de botão">
@verbatim
<x-ui.button variant="primary">
    <x-ui.icon name="propeller" size="sm" /> Produção
</x-ui.button>
@endverbatim
                        </x-ui.code-example>

                        <x-ui.code-example title="Ícone informativo">
@verbatim
<x-ui.icon name="lifebuoy" label="Equipamento de segurança" />
@endverbatim
                        </x-ui.code-example>
                    </div>
                    <x-ui.alert variant="info" title="Regra de inclusão" class="mt-5">Baixe do Tabler somente o SVG necessário, salve com o nome original e use o componente. Não instale o pacote completo.</x-ui.alert>
                </x-ui.panel>

                <h3 class="mb-4 font-display text-xl font-bold text-[var(--ui-text)]">Lista disponível</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                    @foreach ($icons as $icon)
                        <div class="flex min-h-28 flex-col items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-4 text-center shadow-[var(--ui-shadow-sm)]">
                            <x-ui.icon :name="$icon['name']" size="lg" class="text-[var(--ui-primary)]" />
                            <span class="mt-3 text-xs font-semibold text-[var(--ui-text-muted)]">{{ $icon['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
