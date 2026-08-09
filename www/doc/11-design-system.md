# Design System

> Status: primeira versão aprovada para evolução incremental.

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
