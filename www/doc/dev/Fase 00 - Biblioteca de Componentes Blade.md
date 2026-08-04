# Biblioteca de Componentes Blade Compartilhados

Este documento descreve a biblioteca base de componentes Blade compartilhados da plataforma.

## Objetivo

Padronizar os blocos visuais reutilizados entre modulos e reduzir repeticao de markup em views.

## Componentes disponiveis

### `x-ui.alert`

Arquivo: `resources/views/components/ui/alert.blade.php`

Uso:

```blade
<x-ui.alert variant="success">Salvo com sucesso.</x-ui.alert>
<x-ui.alert variant="error">Falha ao salvar.</x-ui.alert>
<x-ui.alert variant="warning">Atencao ao prazo.</x-ui.alert>
<x-ui.alert>Informacao geral.</x-ui.alert>
```

Props:

- `variant`: `success`, `error`, `warning`, `info`.

### `x-ui.panel`

Arquivo: `resources/views/components/ui/panel.blade.php`

Uso:

```blade
<x-ui.panel>
  Conteudo do painel
</x-ui.panel>

<x-ui.panel padding="p-6 md:p-10">
  Conteudo com padding customizado
</x-ui.panel>
```

Props:

- `padding`: classes de espacamento interno.

### `x-ui.page-heading`

Arquivo: `resources/views/components/ui/page-heading.blade.php`

Uso:

```blade
<x-ui.page-heading
  title="Painel Administrativo"
  subtitle="Gestao central da plataforma"
/>
```

Props:

- `title`: titulo principal.
- `subtitle`: subtitulo opcional.
- `titleClass`: classes para o tamanho/estilo do titulo.

### `x-ui.button`

Arquivo: `resources/views/components/ui/button.blade.php`

Uso:

```blade
<x-ui.button variant="primary" type="submit">Salvar</x-ui.button>
<x-ui.button variant="secondary">Cancelar</x-ui.button>
<x-ui.button variant="danger">Remover</x-ui.button>
```

Props:

- `variant`: `primary`, `secondary`, `danger`, `ghost`.
- `size`: `sm`, `md`, `lg`.
- `type`: tipo do botao (`button`, `submit`, etc).

### `x-ui.input`

Arquivo: `resources/views/components/ui/input.blade.php`

Uso:

```blade
<x-ui.input name="name" :value="old('name')" required />
<x-ui.input name="email" type="email" required />
<x-ui.input name="remember" type="checkbox" value="1" unstyled />
<x-ui.input type="hidden" name="plan_code" :value="$planCode" unstyled />
```

Props:

- `id`: opcional. Se ausente, o componente deriva automaticamente de `name`.
- `name`: opcional, recomendado para formularios.
- `type`: padrao `text`.
- `unstyled`: quando `true`, nao aplica classe `ui-input`.

Observacoes:

- Use `unstyled` quando o campo precisa manter estilo customizado ja existente (ex.: `auth-input`, checkboxes compactos).
- A classe padrao `ui-input` foi adicionada em `resources/css/app.css` para padronizar borda, raio, padding e foco.

### `x-ui.textarea`

Arquivo: `resources/views/components/ui/textarea.blade.php`

Uso:

```blade
<x-ui.textarea name="description" rows="4">{{ old('description') }}</x-ui.textarea>
<x-ui.textarea name="payload_json" rows="12" class="font-mono text-sm">{{ $payload }}</x-ui.textarea>
```

Props:

- `id`: opcional. Se ausente, o componente deriva automaticamente de `name`.
- `name`: opcional, recomendado para formularios.
- `rows`: padrao `3`.
- `unstyled`: quando `true`, nao aplica classe `ui-textarea`.

Observacoes:

- A classe padrao `ui-textarea` foi adicionada em `resources/css/app.css` com comportamento visual alinhado ao `x-ui.input`.

### `x-ui.select`

Arquivo: `resources/views/components/ui/select.blade.php`

Uso:

```blade
<x-ui.select name="company_id" data-search="on" required>
  <option value="">Selecione</option>
</x-ui.select>

<x-ui.select name="status" data-search="off">
  <option value="ACTIVE">Ativo</option>
</x-ui.select>

<x-ui.select name="legacy_select" :select2="false">
  <option value="A">Opcao A</option>
</x-ui.select>
```

Props:

- `id`: opcional. Se ausente, o componente deriva automaticamente de `name`.
- `name`: opcional, recomendado para formularios.
- `select2`: padrao `true`; quando ativo aplica `data-ui-select2="true"`.

Atributos suportados no markup (via `$attributes`):

- `data-search="on|off"`: controla busca do Select2 (`on` sempre mostra, `off` nunca mostra).
- `data-placeholder`: placeholder do Select2.
- `data-allow-clear="true"`: habilita limpeza do valor.
- `data-dropdown-parent="#seletor"`: ancora o dropdown em container especifico.

Observacoes:

- Inicializacao do Select2 ocorre em `resources/js/app.js` via `initializeUiSelects()`.
- Selects adicionados dinamicamente (ex.: linhas de BOM) sao inicializados automaticamente por `MutationObserver`.

### `x-ui.sidebar`

Arquivo: `resources/views/components/ui/sidebar.blade.php`

Uso:

```blade
<x-ui.sidebar variant="industrial" id="sidebar" aria-label="{{ __('ui.modules') }}">
  <x-slot:header>
    <strong>{{ __('ui.app_name') }}</strong>
    <span>{{ __('ui.modules') }}</span>
  </x-slot:header>

  <nav class="ind-module-list">
    <!-- links -->
  </nav>

  <x-slot:footer>
    <!-- logout -->
  </x-slot:footer>
</x-ui.sidebar>
```

Props:

- `variant`: `base`, `industrial`, `docs`, `admin`.
- `title`, `subtitle`: opcoes para cabecalho simples.
- `headerClass`, `contentClass`, `footerClass`: override das classes internas.

### `x-ui.menu`

Arquivo: `resources/views/components/ui/menu.blade.php`

Uso:

```blade
<x-ui.menu variant="docs" :aria-label="__('ui.documentation')">
  <x-ui.menu-item variant="docs" :href="route('docs.show', ['file' => '01 - README.md'])" :active="true">
    README
  </x-ui.menu-item>
</x-ui.menu>
```

Props:

- `variant`: `base`, `industrial`, `docs`, `admin`.
- `ariaLabel`: acessibilidade do bloco de navegacao.

### `x-ui.menu-item`

Arquivo: `resources/views/components/ui/menu-item.blade.php`

Uso:

```blade
<x-ui.menu-item variant="admin" href="#sec-usuarios" data-section-link="sec-usuarios">
  Usuarios
</x-ui.menu-item>

<x-ui.menu-item variant="docs" :href="$url" :active="$isCurrent">
  Documento atual
</x-ui.menu-item>
```

Props:

- `variant`: `base`, `industrial`, `docs`, `admin`.
- `href`: destino do item.
- `active`: marca item ativo com `is-active` e `aria-current`.

### `x-ui.breadcrumb`

Arquivo: `resources/views/components/ui/breadcrumb.blade.php`

Uso:

```blade
<x-ui.breadcrumb :items="[
  ['label' => __('ui.app_name'), 'href' => route('dashboard.industrial')],
  ['label' => __('ui.documentation'), 'href' => route('docs.index')],
  ['label' => $currentTitle],
]" />
```

Props:

- `items`: lista ordenada de itens, com `label` e `href` opcional.
- `ariaLabel`: rotulo de acessibilidade para a navegacao.

## Aplicacao atual

Componentes aplicados nas areas:

- Autenticacao (`auth/*`): feedback com `x-ui.alert` e campos com `x-ui.input`.
- Onboarding (`onboarding/*`): container com `x-ui.panel`, feedback com `x-ui.alert` e formularios com `x-ui.input`, `x-ui.textarea` e `x-ui.select`.
- Admin (`admin/*`): secoes com `x-ui.panel`, feedback com `x-ui.alert` e formularios/buscas padronizados com `x-ui.input`, `x-ui.textarea` e `x-ui.select`.
- Cliente (`client/*`): formularios/buscas padronizados com `x-ui.input`, `x-ui.textarea` e `x-ui.select`.
- Billing (`billing/*`): campos hidden e selecao de plano usando componentes de formulario.
- Sidebars globais: dashboard industrial, admin e docs via `x-ui.sidebar`.
- Menus globais: dashboard industrial, admin e docs via `x-ui.menu` + `x-ui.menu-item`.
- Breadcrumb global: dashboard industrial, dashboard de acesso gratuito (14 dias), onboarding wizard, admin e docs via `x-ui.breadcrumb`.

## Mudancas recentes

- Criados os componentes `x-ui.input` e `x-ui.textarea` para padronizacao total de campos de formulario.
- Componente `x-ui.select` consolidado como padrao de selects com Select2 habilitado por default.
- Inicializacao de Select2 evoluida para suportar elementos dinamicos com `MutationObserver` em `resources/js/app.js`.
- Estilos base de formulario centralizados nas classes `ui-input`, `ui-textarea` e `ui-select` em `resources/css/app.css`.

## Convencao de evolucao

- Novos componentes compartilhados devem ser adicionados em `resources/views/components/ui/`.
- Componentes devem ser pequenos, focados e sem acoplamento com regras de negocio.
- Toda duplicacao visual recorrente entre modulos deve ser candidata a componente.
