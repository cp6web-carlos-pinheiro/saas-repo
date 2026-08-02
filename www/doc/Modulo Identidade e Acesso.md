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
