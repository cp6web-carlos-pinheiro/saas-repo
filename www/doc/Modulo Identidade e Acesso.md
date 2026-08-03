# Modulo Identidade e Acesso

Este modulo gerencia autenticacao, autorizacao e controle de permissoes de usuarios na plataforma.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: login/logout, recuperacao de senha, verificacao de email, RBAC com roles/permissoes e middleware de checagem por modulo.
- Pendencia principal: evoluir camada administrativa unificada e avaliar inclusao de two factor no fluxo padrao.

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
