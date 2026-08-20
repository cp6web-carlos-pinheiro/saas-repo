# Design System

> Status: contrato consolidado e aplicado às interfaces ativas do produto.

## Objetivo

Definir uma linguagem visual e de interação consistente para o produto, com fundamentos, componentes e padrões reutilizáveis que atendam aos fluxos administrativos e industriais.

## Catálogo visual

[Abrir a página visual do Design System](/global-admin/design-system)

O catálogo apresenta tokens de tema, botões, campos, controles de seleção, tabelas, feedback, navegação contextual, modais e os SVGs Tabler disponíveis na aplicação.

Cada item do catálogo inclui um exemplo Blade expansível e pronto para copiar, próximo da demonstração funcional correspondente.

O próprio catálogo também é o modelo de página do produto: header global, menu lateral de módulos e área principal responsiva.

## Decisões aprovadas

- Blade e Tailwind CSS 4 são a base dos componentes compartilhados.
- Os temas `light`, `dark` e `system` usam tokens semânticos e preferência persistida no navegador.
- Novas interfaces reutilizam ou evoluem componentes em `resources/views/components/ui`.
- Tabler Icons é a biblioteca oficial; somente cada SVG utilizado deve ser baixado e versionado.
- O conjunto inicial inclui ícones de fabricação naval para embarcação, veleiro, ancoragem, comando, propulsão, motorização, segurança e hidrodinâmica.
- Componentes fundamentais cobrem botões, campos, selects, textarea, checkbox, radio, switch, slider, progresso, feedback, tabelas, dropdown, tabs e modal.
- Botões oferecem variantes semânticas, três tamanhos, ícones e estados de loading, desabilitado e somente leitura por `aria-disabled`.
- Inputs aceitam prefixo, sufixo e ícone no início ou final. Selects pesquisáveis usam o Select2 já instalado, por meio de `data-search="on"`.
- Componentes compostos incluem accordion, attachment, badge, button group, calendar, chart, collapsible, data table, date picker, input group, item e tooltip. O button group padrão mantém os botões unidos, com cantos retos nas divisórias internas e o mesmo raio dos botões no contorno externo; oferece tons `primary`, `outline` e `surface`.
- Date Picker reutiliza Calendar em um popover; Data Table evolui a Table existente com filtro, ordenação e paginação local.
- Células da Data Table podem habilitar cópia e edição inline. Salvar dispara `ui:table-cell-updated`; a integração da tela deve persistir a alteração no backend.
- Gráficos são SVG/CSS acessíveis com uma tabela equivalente para leitores de tela, sem adicionar uma biblioteca gráfica à primeira versão. O componente oferece barras verticais, linha, área, barras horizontais e donut.
- Modais suportam `sm`, `md`, `lg`, `xl`, `full` e `sheet`, com fechamento por Escape, foco contido e retorno ao elemento acionador.
- O header mantém Tutorial e Preferências como ações globais em painéis laterais.
- O menu lateral usa os mesmos domínios da área do cliente, suporta subitens expansíveis e preserva somente os ícones ao ser recolhido; no mobile, abre sobre o conteúdo.
- Estados de foco, erro, desabilitado e responsividade fazem parte do contrato de cada componente.

## Uso de ícones

```blade
<x-ui.icon name="ship" />
<x-ui.icon name="anchor" size="lg" class="text-[var(--ui-primary)]" />
<x-ui.button variant="primary"><x-ui.icon name="propeller" /> Produção</x-ui.button>
<x-ui.icon name="lifebuoy" label="Equipamento de segurança" />
```

Ícones junto de texto são decorativos por padrão. Use `label` quando o ícone transmitir informação sem texto equivalente. O arquivo correspondente deve existir em `resources/icons/tabler/<nome>.svg`.

Para a construção naval, prefira o vocabulário já agrupado no catálogo: embarcação e navegação; fabricação e montagem; movimentação e logística; sistemas de bordo; qualidade e segurança. Por exemplo, use `hammer-drill` para furação, `crane` para içamento, `gauge` para instrumentação e `clipboard-check` para inspeção. Não use um ícone de barco genérico para representar uma etapa específica da produção.

## Próximas evoluções

- Validar os componentes em fluxos reais do produto.
- Consolidar padrões de filtros, paginação e estados vazios.
- Definir processo de revisão visual e acessibilidade para novas variantes.

## Fundação (onda 1 — shell reutilizável e componentes ausentes)

- O shell do catálogo foi extraído para `<x-ui.app-shell>` (`resources/views/components/ui/app-shell.blade.php`), que recebe navegação, marca e ações via props/slots. O catálogo consome o mesmo componente que Global Admin e área cliente usarão nas próximas ondas — nenhuma navegação real está acoplada a ele.
- Os tokens `--ui-*` e o fundo do shell deixaram de depender da classe `.ds-shell`: a classe correta agora é `.ui-shell`, aplicada ao `<body>` por qualquer layout que use `<x-ui.app-shell>`, não apenas pelo catálogo.
- Container e cabeçalho de página padronizados: `.ui-page` (largura e espaçamento de conteúdo) e `x-ui.page-heading`, que agora aceita `:breadcrumbs="[...]"` (delegando para `x-ui.breadcrumb`, já migrado para tokens semânticos), um `eyebrow` opcional e um slot `actions` para ações no topo da página.
- `x-ui.field` agora emite `data-ui-field` e `data-for`; um script em `resources/js/app.js` liga `aria-describedby` automaticamente ao dica/erro do campo, sem exigir que cada view repita o id manualmente. Erros de validação continuam sendo linkados automaticamente por `x-ui.input`/`select`/`textarea` a partir do `name`.
- Novos componentes compartilhados:
  - `x-ui.icon-button` — formaliza `.ui-icon-button` como componente com `label` obrigatório (`aria-label`/`title`).
  - `x-ui.filter-bar` — busca (`search`, GET) + slots `fields` e `actions` para listagens.
  - `x-ui.empty-state` — ícone, título, descrição e ações para estados vazios de listagens.
  - `x-ui.row-actions` — agrupa ícones de ação no fim de uma linha de tabela.
  - `x-ui.editor-toolbar` — toolbar para editores de conteúdo (ex.: tutoriais).
  - `x-ui.confirm-button` — ação destrutiva com formulário + confirmação acessível (`data-ui-confirm`, tratado com SweetAlert2 e classes `.ui-swal-*` em tokens semânticos; substitui o padrão anterior restrito a `data-admin-delete-confirm`/`.g-swal-*`, que continua funcionando).
  - Paginação: `resources/views/vendor/pagination/ui.blade.php`, registrada em `AppServiceProvider` via `Paginator::defaultView()`. Toda chamada existente a `$paginator->links()` passa a usar o componente `.ui-pagination` automaticamente, sem alterar controllers, filtros ou query string.
- Mapeamento usado durante a migração das variantes antigas para as variantes semânticas:

  | Variante legada | Variante semântica |
  | --- | --- |
  | `brand-primary` | `primary` |
  | `material-edit` | `primary` |
  | `material-remove` | `danger` |
  | `material-versions` | `info` |
  | `material-back` | `outline` |
  | `surface-muted` | `secondary` |
  | `danger-outline` | `danger` (estilo outline) |
  | `inverse-outline` | `outline` (sobre fundos escuros) |

- As variantes antigas foram removidas de `x-ui.button` depois da última onda. Código novo deve usar somente `primary`, `secondary`, `outline`, `ghost`, `danger`, `success`, `warning`, `info`, `neutral` ou `inverse-outline` quando estiver sobre fundo escuro.

## Consolidação das interfaces

- Global Admin, área cliente, catálogo e páginas públicas compartilham os tokens `--ui-*`, tema persistido e shell responsivo. A área cliente preserva a filtragem por empresa, contrato, módulo, papel e permissão ao montar a navegação entregue ao `x-ui.app-shell`.
- Listagens ativas usam `x-ui.table`, com caption acessível, paginação do servidor preservada e scroll responsivo. Formulários dinâmicos de BOM, compras e vendas mantêm seus nomes de campos e hooks `data-*`, usando `x-ui.button` e `x-ui.icon-button` para adicionar e remover linhas.
- Não há mais variantes Material, classes `ind-*`, Heroicons nem SVGs inline nas views funcionais. Cores específicas aparecem somente na definição dos tokens e na amostra visual do catálogo.
- Os templates legados `admin/administrator/edit.blade.php`, `admin/module-placeholder.blade.php` e `dashboard/trial.blade.php` foram removidos após validação de rotas, controllers, testes e referências. O caminho `/trial/dashboard` continua como redirect para `/dashboard`.
- O visualizador de documentação e este documento refletem o contrato usado pelas telas reais, incluindo light, dark e system, navegação móvel, foco visível, estados semânticos e ações destrutivas acessíveis.
