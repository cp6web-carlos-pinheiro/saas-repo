# Modulo Identidade e Acesso

Este modulo gerencia autenticacao, autorizacao e controle de permissoes de usuarios na plataforma.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Status atual: Avancado.
- Cobertura atual: login/logout, recuperacao de senha, verificacao de email, RBAC com roles/permissoes e middleware de checagem por modulo.
- Seguranca adicional: MFA por codigo via e-mail no login web (habilitavel por configuracao), politica de senha centralizada e monitoramento de autenticacao com logs dedicados.
- Pendencia principal: evoluir camada administrativa unificada e ampliar opcoes de MFA (ex.: app autenticador/TOTP).

## Tabelas relacionadas

### Mestres

- `users`
- `roles`
- `permissions`

### Transacionais

- `personal_access_tokens`

### Relacionamento

- `permission_role`
- `role_user`

## Escopo atual de Permissoes RBAC

### Modelo ativo

- RBAC tenant por empresa com `roles` + `permissions`.
- Permissoes efetivas por usuario sao resolvidas por role vinculada em `role_user` (escopo por `company_id`).
- Permissoes de role sao resolvidas por `permission_role`.
- `User::hasPermission(permissionSlug, companyId)` considera role administrativa (`master`) e permissoes vinculadas na role.

### Estruturas removidas da solucao

- Fluxo de templates RBAC removido.
- Fluxo de aprovacao/historico de alteracoes RBAC removido.
- Excecoes por usuario (override por permissao) removidas.
- Estruturas de banco removidas por migracoes:
	- `role_templates`
	- `role_template_versions`
	- `company_role_template_versions`
	- `rbac_change_requests`
	- `permission_user_overrides`

### Permissoes company-access ativas

- `company-access.users.read`
- `company-access.users.create`
- `company-access.users.update`
- `company-access.users.delete`
- `company-access.rbac.read`
- `company-access.roles.create`
- `company-access.roles.update`
- `company-access.roles.delete`
- `company-access.dashboard.read`
- `company-access.billing.read`
- `company-access.billing.update`

### Fluxo funcional atual

- Console RBAC (`/company-access/rbac`): lista, cria, visualiza, edita e exclui perfis de acesso.
- Edicao de perfil altera somente dados do perfil e matriz de permissoes.
- Tela de detalhe do perfil exibe usuarios vinculados e atalho para editar usuario.
- Cadastro/edicao de usuario (`/company-access/users`) seleciona um perfil RBAC existente (`role_id`).
- Troca de perfil permitida inclusive para usuario atualmente `master`, com regras de continuidade administrativa.

### Regras de governanca ativas

- Separacao de funcoes (SoD): bloqueia combinacoes criticas de permissoes na mesma role.
- Validacao de naming de slugs de permissao.
- Continuidade administrativa:
	- nao permite remover a ultima role administrativa ativa da empresa;
	- nao permite deixar a empresa sem nenhum usuario administrador ativo.

### Observacoes de UI

- O primeiro usuario da empresa permanece obrigatoriamente com perfil administrativo.
- Para os demais usuarios, o perfil RBAC e sempre selecionavel na tela de edicao.

## Baseline de seguranca implementado

- Politica de senha unificada em `App\Support\Security\PasswordPolicy` aplicada em fluxos de cadastro, convite, reset e criacao de usuarios web/admin/api.
- MFA web via desafio de codigo com expiracao e reenvio, controlado por `AUTH_MFA_ENABLED`.
- Eventos de autenticacao monitorados em canal dedicado (`storage/logs/auth.log`) com contexto operacional.
