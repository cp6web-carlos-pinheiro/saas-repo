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
  <x-ui.menu-item variant="docs" :href="route('docs.show', ['file' => 'README.md'])" :active="true">
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

- Autenticacao (`auth/*`): feedback com `x-ui.alert`.
- Onboarding (`onboarding/wizard`): container com `x-ui.panel` e feedback com `x-ui.alert`.
- Admin (`admin/management`): secoes com `x-ui.panel` e feedback com `x-ui.alert`.
- Sidebars globais: dashboard industrial, admin e docs via `x-ui.sidebar`.
- Menus globais: dashboard industrial, admin e docs via `x-ui.menu` + `x-ui.menu-item`.
- Breadcrumb global: dashboard industrial, dashboard trial, onboarding wizard, admin e docs via `x-ui.breadcrumb`.

## Convencao de evolucao

- Novos componentes compartilhados devem ser adicionados em `resources/views/components/ui/`.
- Componentes devem ser pequenos, focados e sem acoplamento com regras de negocio.
- Toda duplicacao visual recorrente entre modulos deve ser candidata a componente.
