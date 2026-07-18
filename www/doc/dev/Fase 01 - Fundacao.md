# Fase 01 - Fundacao

## Objetivo
Definir a fundacao tecnica e visual que sera reutilizada por todo o sistema.

## Nivel de implementacao atual

| Item | Status | Situacao no projeto |
| --- | --- | --- |
| Layout | Implementado | Foi criado um layout base reutilizavel no padrao visual Google e aplicado nas telas de dashboard, admin, onboarding e docs, padronizando estrutura e cabecalho HTML com seções Blade compartilhadas. |
| Componentes Blade | Implementado | Foi criada e aplicada uma biblioteca compartilhada em `resources/views/components/ui` (alert, panel, page-heading, button), com uso real em autenticacao, onboarding e admin, e documentacao dedicada no arquivo `doc/dev/Biblioteca de Componentes Blade.md`. |
| Tema | Implementado | O tema foi centralizado em `resources/css/app.css` com tokens globais (cores, tipografia e estados) e aplicado via `layouts/google.blade.php`, removendo duplicacoes de estilos inline e unificando dashboard, docs, auth, onboarding, admin e landing. |
| Sidebar | Implementado | Foi criado o componente global `x-ui.sidebar` em `resources/views/components/ui/sidebar.blade.php` e aplicado nas areas de dashboard industrial, administracao e visualizador de documentacao, mantendo variacoes de layout por contexto. |
| Menu | Implementado | Foi criada uma composicao global de menu com `x-ui.menu` e `x-ui.menu-item`, incluindo estado ativo (`active`/`is-active`) e aplicacao consolidada em dashboard industrial, administracao e visualizador de documentacao. |
| Breadcrumb | Implementado | Foi criado o componente global `x-ui.breadcrumb` em `resources/views/components/ui/breadcrumb.blade.php` e aplicado nas paginas internas de dashboard industrial, dashboard de acesso gratuito (14 dias), onboarding, administracao e documentacao. |
| Dashboard | Implementado | O projeto ja possui dashboard industrial e dashboard de acesso gratuito (14 dias) ligados as rotas web. |

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
| Multitenancy | Parcial | Existem companies, organizations, tenants, periodo gratuito inicial de 14 dias, subscriptions, middleware de resolucao de tenant e escopo por company; o fluxo ainda depende de refinamentos de integracao e padronizacao entre web e API. |
| Autenticacao e seguranca | Parcial | Login, logout, recuperacao de senha, verificacao de email e gerenciamento de sessoes ja existem; two factor ainda nao foi identificado no projeto. |
| Permissoes RBAC | Parcial | Roles, permissions, role_user, permission_role, hasPermission e middleware de checagem ja existem; ainda falta consolidar a camada de administracao e cobertura por modulo. |

### Observacoes por subfase

- Multitenancy: companies, planos, assinaturas, dominios e periodo gratuito inicial de 14 dias ja estao presentes em boa parte da base.
- Autenticacao e seguranca: login, logout, recuperacao de senha, verificacao de email e sessoes estao implementados; two factor ainda nao aparece no codigo.
- Permissoes RBAC: roles, permissions, role permission, user role e permissoes por modulo ja tem base de dados e regras de checagem, mas ainda sem camada completa de gestao visual unificada.
