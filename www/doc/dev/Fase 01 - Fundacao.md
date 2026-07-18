# Fase 01 - Fundacao

## Objetivo
Definir a fundacao tecnica e visual que sera reutilizada por todo o sistema.

## Nivel de implementacao atual

| Item | Status | Situacao no projeto |
| --- | --- | --- |
| Layout | Parcial | Existem telas com estrutura propria em Blade para dashboard, admin, onboarding e docs, mas ainda nao ha um layout base unico reutilizavel para toda a plataforma. |
| Componentes Blade | Parcial | Ha views bem estruturadas e padronizadas, porem o projeto ainda nao expõe uma biblioteca clara de componentes Blade compartilhados. |
| Tema | Parcial | O visual ja tem direcao consistente nas telas principais, com Tailwind e estilos locais, mas ainda nao existe um tema centralizado e totalmente unificado. |
| Sidebar | Parcial | Ha sidebars funcionais no dashboard, admin e docs, mas cada uma e implementada de forma especifica e nao como componente global. |
| Menu | Parcial | Existem menus de navegação nas areas principais, mas ainda nao ha um menu global consolidado com estado, slots ou composição reutilizavel. |
| Breadcrumb | Nao implementado | Nao foi encontrado breadcrumb funcional no fluxo atual. |
| Dashboard | Implementado | O projeto ja possui dashboard industrial e dashboard de trial ligados as rotas web. |

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
| Multitenancy | Parcial | Existem companies, organizations, tenants, trial, subscriptions, middleware de resolucao de tenant e escopo por company; o fluxo ainda depende de refinamentos de integracao e padronizacao entre web e API. |
| Autenticacao e seguranca | Parcial | Login, logout, recuperacao de senha, verificacao de email e gerenciamento de sessoes ja existem; two factor ainda nao foi identificado no projeto. |
| Permissoes RBAC | Parcial | Roles, permissions, role_user, permission_role, hasPermission e middleware de checagem ja existem; ainda falta consolidar a camada de administracao e cobertura por modulo. |

### Observacoes por subfase

- Multitenancy: companies, planos, assinaturas, dominios, trial, middleware e resolver de tenant ja estao presentes em boa parte da base.
- Autenticacao e seguranca: login, logout, recuperacao de senha, verificacao de email e sessoes estao implementados; two factor ainda nao aparece no codigo.
- Permissoes RBAC: roles, permissions, role permission, user role e permissoes por modulo ja tem base de dados e regras de checagem, mas ainda sem camada completa de gestao visual unificada.
