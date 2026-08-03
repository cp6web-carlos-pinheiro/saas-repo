# Fase 01 - Fundacao

## Objetivo
Definir a fundacao tecnica e visual que sera reutilizada por todo o sistema.

## Ultima atualizacao
- 2026-08-03

## Nivel de implementacao atual

| Item | Status | Situacao no projeto |
| --- | --- | --- |
| Layout | Implementado | Foi criado um layout base reutilizavel no padrao visual Google e aplicado nas telas de dashboard, admin, onboarding e docs, padronizando estrutura e cabecalho HTML com seções Blade compartilhadas. |
| Componentes Blade | Implementado | Biblioteca compartilhada consolidada em `resources/views/components/ui`, incluindo `alert`, `panel`, `page-heading`, `button`, `sidebar`, `menu`, `menu-item`, `breadcrumb` e componentes de formulario (`input`, `textarea`, `select`). Os formularios e buscas foram padronizados para uso desses componentes, com `x-ui.select` integrado ao Select2 (incluindo suporte a elementos dinamicos). |
| Tema | Implementado | O tema foi centralizado em `resources/css/app.css` com tokens globais (cores, tipografia e estados) e aplicado via `layouts/google.blade.php`, removendo duplicacoes de estilos inline e unificando dashboard, docs, auth, onboarding, admin e landing. |
| Sidebar | Implementado | Foi criado o componente global `x-ui.sidebar` em `resources/views/components/ui/sidebar.blade.php` e aplicado nas areas de dashboard industrial, administracao e visualizador de documentacao, mantendo variacoes de layout por contexto. |
| Menu | Implementado | Foi criada uma composicao global de menu com `x-ui.menu` e `x-ui.menu-item`, incluindo estado ativo (`active`/`is-active`), submenus colapsaveis, ordenacao por prioridade operacional, mapeamento por modulos e icones coerentes por contexto na area do cliente/dashboard. |
| Breadcrumb | Implementado | Foi criado o componente global `x-ui.breadcrumb` em `resources/views/components/ui/breadcrumb.blade.php` e aplicado nas paginas internas de dashboard industrial, dashboard de acesso gratuito (14 dias), onboarding, administracao e documentacao. |
| Dashboard | Implementado | O projeto possui dashboard industrial e dashboard de acesso gratuito (14 dias) ligados as rotas web, com shell padronizado (cabecalho, sidebar, breadcrumbs e conteudo full width para CRUDs internos). |

## Arquitetura Base
- Laravel
- Blade
- Tailwind
- Alpine.js
- Vite
- MySQL
- Queues
- Jobs
- Events
- Notifications
- Cache
- Logs
- Docker
- Testes

## Definicoes obrigatorias
- Layout
- Componentes Blade
- Tema
- Sidebar
- Menu
- Breadcrumb
- Dashboard

## Subfases da Fundacao
| Subfase | Status | Situacao no projeto |
| --- | --- | --- |
| Multitenancy | Parcial avancado | Existem companies, organizations, tenants, periodo gratuito inicial de 14 dias, subscriptions, middleware de resolucao de tenant e escopo por company. A navegacao web tenant foi ampliada e padronizada, mas ainda ha refinamentos pendentes para consolidar contratos entre web e API. |
| Autenticacao e seguranca | Parcial | Login, logout, recuperacao de senha, verificacao de email e gerenciamento de sessoes ja existem; two factor ainda nao foi identificado no projeto. |
| Permissoes RBAC | Parcial avancado | Roles, permissions, role_user, permission_role, hasPermission e middleware de checagem ja existem e estao ativos no fluxo tenant. O escopo foi simplificado para role pura por empresa (sem templates, sem aprovacao/historico RBAC e sem overrides por usuario), com cadastro/edicao de usuario baseado em selecao de perfil RBAC existente. |

### Observacoes por subfase

- Multitenancy: companies, planos, assinaturas, dominios e periodo gratuito inicial de 14 dias ja estao presentes em boa parte da base, com resolucao tenant aplicada no fluxo web.
- Autenticacao e seguranca: login, logout, recuperacao de senha, verificacao de email e sessoes estao implementados; two factor ainda nao aparece no codigo.
- Permissoes RBAC: roles, permissions, role permission, user role e permissoes por modulo ja tem base de dados e regras de checagem; o fluxo tenant atual trabalha com atribuicao direta de perfil RBAC por usuario e governanca de continuidade administrativa.
